<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Console\Commands\RunCICommand;
use Illuminate\Support\Facades\Process;
use Illuminate\Testing\PendingCommand;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use Symfony\Component\Console\Command\Command as ProcessCommand;
use Tests\TestCase;

#[CoversClass(RunCICommand::class)]
final class RunCICommandTest extends TestCase
{
    protected function setUp(): void // @phpstan-ignore-line phpunit.callParent
    {
        self::markTestSkipped('This test is skipped.');
    }

    #[Test]
    public function ci_command_runs_expected_processes(): void
    {
        // Arrange: fake external processes the CI command orchestrates
        Process::fake([
            'aspell --version' => Process::result("aspell 0.60.8 (mock)\n"),
            'npm run build' => Process::result('built'),
            'composer ci' => Process::result(),
            'composer audit' => Process::result(),
            'npm audit' => Process::result(),
        ]);

        // Act
        /** @var PendingCommand $result */
        $result = $this->artisan('ci:run');

        // Assert
        $result->assertExitCode(ProcessCommand::SUCCESS);
        Process::assertRan('aspell --version');
        Process::assertRan('npm run build');
        Process::assertRan('composer ci');
        Process::assertRan('composer audit');
        Process::assertRan('npm audit');
    }
}
