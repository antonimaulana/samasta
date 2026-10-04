<?php

declare(strict_types=1);

/**
 * Install pre-commit hook (blok commit jika .env / rahasia ter-staging).
 *
 * Usage: php scripts/install-git-hooks.php
 */
$root = dirname(__DIR__);
$source = $root.'/scripts/git-hooks/pre-commit';
$target = $root.'/.git/hooks/pre-commit';

if (! is_dir($root.'/.git')) {
    fwrite(STDERR, "Error: bukan repositori Git.\n");
    exit(1);
}

if (! is_file($source)) {
    fwrite(STDERR, "Error: hook sumber tidak ada: {$source}\n");
    exit(1);
}

$hook = file_get_contents($source);
if ($hook === false) {
    exit(1);
}

// Normalisasi line ending untuk hook shell
$hook = str_replace("\r\n", "\n", $hook);

if (file_put_contents($target, $hook) === false) {
    fwrite(STDERR, "Error: gagal menulis {$target}\n");
    exit(1);
}

@chmod($target, 0755);

fwrite(STDOUT, "Git hook terpasang: .git/hooks/pre-commit\n");
fwrite(STDOUT, "Jalankan: php scripts/check-secrets-not-in-git.php (manual kapan saja)\n");

exit(0);
