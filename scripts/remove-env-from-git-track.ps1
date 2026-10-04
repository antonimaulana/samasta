# Hapus .env dari indeks Git (file lokal tetap ada). Jalankan dari root repo.
$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

$tracked = git ls-files -- ".env" ".env.*" 2>$null
$forbidden = @()
foreach ($line in $tracked) {
    if ($line -eq ".env.example") { continue }
    if ($line -match "\.example$") { continue }
    $forbidden += $line
}

if ($forbidden.Count -eq 0) {
    Write-Host "OK: tidak ada .env yang ter-track di Git."
    php scripts/check-secrets-not-in-git.php
    exit 0
}

Write-Host "Menghapus dari Git (cached): $($forbidden -join ', ')"
foreach ($path in $forbidden) {
    git rm --cached -- "$path"
}

Write-Host ""
Write-Host "Selanjutnya: commit perubahan, push, lalu ROTASI rahasia di VPS (lihat docs/deploy/KEAMANAN-ENV-DAN-GIT.md)."
php scripts/check-secrets-not-in-git.php
