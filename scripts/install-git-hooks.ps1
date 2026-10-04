# Pasang pre-commit hook SIMTAMAN (Windows / Git Bash)
$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot
php scripts/install-git-hooks.php
