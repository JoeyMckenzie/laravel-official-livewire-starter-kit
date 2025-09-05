<?php

declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Process\PendingProcess;
use Illuminate\Support\Facades\Process;
use RuntimeException;

final class RunCICommand extends Command
{
    /**
     * @var string
     */
    protected $signature = 'ci:run';

    /**
     * @var string
     */
    protected $description = 'Runs CI for the project.';

    /**
     * Execute the console command.
     */
    public function handle(
        Filesystem $file,
        PendingProcess $process,
        Kernel $artisan
    ): int {
        $this->info('Running CI for the project...');

        $osRunner = $this->detectOsRunner();

        if ($osRunner === null) {
            $this->error('Unsupported OS detected, please use Linux or macOS');

            return self::FAILURE;
        }

        $this->info("Detected OS: $osRunner");

        // Ensure NPM dependencies are installed
        if (! is_dir(base_path('node_modules'))) {
            $this->info('Installing npm dependencies...');
            $this->runOrFail('npm ci', $process);
        }

        if (! $file->exists(base_path('.env'))) {
            $this->info('.env file not found, initializing environment');

            $example = base_path('.env.example');
            $env = base_path('.env');

            if ($file->isFile($example) && ! $file->copy($example, $env)) {
                $this->error('Failed to copy .env.example to .env');

                return self::FAILURE;
            }

            $artisan->call('key:generate');
        }

        // Ensure Aspell is available
        $aspell = Process::path(base_path())->run('aspell --version');

        if ($aspell->successful()) {
            $output = strtok($aspell->output(), "\n");
            if ($output === false) {
                $this->error('Error detecting Aspell version');

                return self::FAILURE;
            }
            $firstLine = mb_trim($output);
            $this->info("Detected $firstLine");
        } elseif ($osRunner === 'Linux') {
            $this->info('Installing Aspell on Linux...');
            $this->runOrFail('sudo apt-get update', $process);
            $this->runOrFail('sudo apt-get install -y aspell aspell-en', $process);
            $this->info('Aspell installed successfully');
        } elseif ($osRunner === 'macOS') {
            $this->info('Installing Aspell on macOS...');
            $this->runOrFail('brew install aspell', $process);
            $this->info('Aspell installed successfully');
        }

        // Build assets
        $this->info('Building assets...');
        $this->runOrFail('npm run build', $process);

        // Run backend CI checks
        $this->info('Running backend CI checks...');
        $this->runOrFail('composer ci', $process);

        // Audit dependencies
        $this->info('Auditing dependencies...');
        $this->runOrFail('composer audit', $process);
        $this->runOrFail('npm audit', $process);

        $this->info('CI successful!');

        return self::SUCCESS;
    }

    private function detectOsRunner(): ?string
    {
        return match (PHP_OS_FAMILY) {
            'Linux' => 'Linux',
            'Darwin' => 'macOS',
            default => null,
        };
    }

    private function runOrFail(string $command, PendingProcess $process): void
    {
        $result = $process->path(base_path())->run($command, function (string $type, string $buffer): void {
            $this->output->write($buffer);
        });

        if (! $result->successful()) {
            $message = sprintf("Command failed (%s) with exit code %d\n%s", $command, $result->exitCode(), $result->errorOutput());
            throw new RuntimeException($message);
        }
    }
}
