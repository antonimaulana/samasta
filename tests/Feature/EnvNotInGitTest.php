<?php

namespace Tests\Feature;

use Tests\TestCase;

class EnvNotInGitTest extends TestCase
{
    public function test_gitignore_blocks_env_file(): void
    {
        $gitignorePath = base_path('.gitignore');
        $this->assertFileExists($gitignorePath);

        $gitignore = file_get_contents($gitignorePath);
        $this->assertNotFalse($gitignore);
        $this->assertStringContainsString('.env', $gitignore);
    }

    public function test_env_is_not_tracked_by_git(): void
    {
        if (! is_dir(base_path('.git'))) {
            $this->markTestSkipped('Not a git repository.');
        }

        $output = [];
        exec('git ls-files', $output, $code);

        $forbidden = array_values(array_filter($output, static function (string $path): bool {
            if ($path === '.env.example') {
                return false;
            }
            if (str_ends_with($path, '.example') && str_contains($path, '.env.')) {
                return false;
            }

            return $path === '.env' || str_starts_with(basename($path), '.env');
        }));

        $this->assertSame([], $forbidden, 'Environment files must not be tracked: '.implode(', ', $forbidden));
    }

    public function test_env_not_in_git_history(): void
    {
        if (! is_dir(base_path('.git'))) {
            $this->markTestSkipped('Not a git repository.');
        }

        $output = [];
        $exitCode = 0;
        exec('php '.escapeshellarg(base_path('scripts/check-secrets-not-in-git.php')).' --history', $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }

    public function test_check_secrets_script_passes(): void
    {
        if (! is_dir(base_path('.git'))) {
            $this->markTestSkipped('Not a git repository.');
        }

        $output = [];
        $exitCode = 0;
        exec('php '.escapeshellarg(base_path('scripts/check-secrets-not-in-git.php')), $output, $exitCode);

        $this->assertSame(0, $exitCode, implode("\n", $output));
    }
}
