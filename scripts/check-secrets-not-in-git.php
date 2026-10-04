<?php

declare(strict_types=1);

/**
 * Fail if environment files or obvious secrets are tracked or staged in Git.
 *
 * Usage:
 *   php scripts/check-secrets-not-in-git.php           # tracked + staged paths
 *   php scripts/check-secrets-not-in-git.php --history  # audit object database (pernah .env?)
 *
 * Exit code: 0 = OK, 1 = violation
 */
$root = dirname(__DIR__);
$auditHistory = in_array('--history', $argv ?? [], true);

if (! is_dir($root.'/.git')) {
    fwrite(STDOUT, "check-secrets: not a git repo — skipped.\n");

    exit(0);
}

function gitLines(string $command): array
{
    $output = [];
    exec($command, $output, $code);

    if ($code !== 0 && $output === []) {
        return [];
    }

    return array_values(array_filter(array_map('trim', $output), static fn (string $line): bool => $line !== ''));
}

function isAllowedEnvPath(string $path): bool
{
    $normalized = str_replace('\\', '/', $path);

    if ($normalized === '.env.example') {
        return true;
    }

    if (preg_match('#/\.env\.[^/]+\.example$#', $normalized) === 1) {
        return true;
    }

    if (preg_match('#^docs/deploy/\.env\.[^/]+\.example$#', $normalized) === 1) {
        return true;
    }

    return false;
}

function isForbiddenEnvPath(string $path): bool
{
    if (isAllowedEnvPath($path)) {
        return false;
    }

    $basename = basename(str_replace('\\', '/', $path));

    if ($basename === '.env') {
        return true;
    }

    if (preg_match('/^\.env(\.|$)/', $basename) === 1) {
        return true;
    }

    return false;
}

/** @return list<string> */
function findEnvPathsInGitHistory(string $root): array
{
    $lines = gitLines('git rev-list --objects --all');
    $found = [];

    foreach ($lines as $line) {
        $parts = preg_split('/\s+/', $line, 2);
        if ($parts === false || count($parts) < 2) {
            continue;
        }

        $path = $parts[1];
        if (isForbiddenEnvPath($path)) {
            $found[] = $path;
        }
    }

    return array_values(array_unique($found));
}

/** @return list<string> */
function scanStagedForEmbeddedSecrets(string $root): array
{
    $staged = gitLines('git diff --cached --name-only --diff-filter=ACMR');
    $issues = [];

    foreach ($staged as $path) {
        if (isAllowedEnvPath($path)) {
            continue;
        }

        $full = $root.DIRECTORY_SEPARATOR.str_replace('/', DIRECTORY_SEPARATOR, $path);
        if (! is_file($full)) {
            continue;
        }

        $content = file_get_contents($full);
        if ($content === false) {
            continue;
        }

        if (preg_match('/^APP_KEY=base64:[A-Za-z0-9+\/=]{20,}/m', $content) === 1) {
            $issues[] = "APP_KEY production terdeteksi di file staged: {$path}";
        }

        if (preg_match('/^DB_PASSWORD=(?!null$|""$|\s*$).+/m', $content) === 1
            && ! str_contains($path, '.example')) {
            $issues[] = "DB_PASSWORD terisi di file staged (bukan template): {$path}";
        }

        if (preg_match('/^SIMTAMAN_LAPANGAN_PIN=(?!null$|""$|\s*$).+/m', $content) === 1
            && ! str_contains($path, '.example')) {
            $issues[] = "SIMTAMAN_LAPANGAN_PIN terisi di file staged: {$path}";
        }
    }

    return $issues;
}

if ($auditHistory) {
    $historical = findEnvPathsInGitHistory($root);
    if ($historical !== []) {
        fwrite(STDERR, "AUDIT RIWAYAT: path environment pernah ada di Git:\n");
        foreach ($historical as $path) {
            fwrite(STDERR, "  - {$path}\n");
        }
        fwrite(STDERR, "\nAsumsikan rahasia bocor. Rotasi kredensial + pertimbangkan git filter-repo.\n");
        fwrite(STDERR, "Lihat docs/deploy/KEAMANAN-ENV-DAN-GIT.md\n");

        exit(1);
    }

    fwrite(STDOUT, "check-secrets --history: OK — tidak ada .env di riwayat Git.\n");

    exit(0);
}

$tracked = gitLines('git ls-files');
$staged = gitLines('git diff --cached --name-only --diff-filter=ACMR');
$candidates = array_unique(array_merge($tracked, $staged));

$violations = [];
foreach ($candidates as $path) {
    if (isForbiddenEnvPath($path)) {
        $violations[] = "File environment tidak boleh di Git: {$path}";
    }
}

$violations = array_merge($violations, scanStagedForEmbeddedSecrets($root));

if ($violations !== []) {
    fwrite(STDERR, "KEAMANAN: pelanggaran ditemukan:\n");
    foreach (array_unique($violations) as $message) {
        fwrite(STDERR, "  - {$message}\n");
    }
    fwrite(STDERR, "\nLangkah perbaikan:\n");
    fwrite(STDERR, "  git rm --cached .env\n");
    fwrite(STDERR, "  git commit -m \"Stop tracking .env\"\n");
    fwrite(STDERR, "  Rotasi APP_KEY, password admin, PIN lapangan, dan password DB di server.\n");
    fwrite(STDERR, "  Lihat docs/deploy/KEAMANAN-ENV-DAN-GIT.md\n");

    exit(1);
}

fwrite(STDOUT, "check-secrets: OK — tidak ada file environment (.env) yang ter-track atau ter-staging di Git.\n");

exit(0);
