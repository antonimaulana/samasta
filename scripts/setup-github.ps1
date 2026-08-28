# Setup GitHub repo untuk proyek SIMTAMAN (Windows)
# Usage: .\scripts\setup-github.ps1 -GitHubUsername "antonimaulana"
# Repo aktif: https://github.com/antonimaulana/samasta
# Requires: Git for Windows, GitHub CLI (gh) recommended

param(
    [Parameter(Mandatory = $true)]
    [string]$GitHubUsername,

    [string]$RepoName = "samasta",
    [switch]$Public
)

$ErrorActionPreference = "Stop"
$ProjectRoot = Split-Path -Parent $PSScriptRoot
Set-Location $ProjectRoot

function Require-Command($Name) {
    if (-not (Get-Command $Name -ErrorAction SilentlyContinue)) {
        Write-Error "Perintah '$Name' tidak ditemukan. Install dulu, lalu restart PowerShell.`nGit: https://git-scm.com/download/win`ngh: https://cli.github.com/"
    }
}

Require-Command git

Write-Host "=== Setup GitHub: $GitHubUsername/$RepoName ===" -ForegroundColor Cyan
Write-Host "Project: $ProjectRoot"

if (Test-Path ".env") {
    $gitCheck = git check-ignore -v .env 2>$null
    if (-not $gitCheck) {
        Write-Error ".env tidak di-ignore! Perbaiki .gitignore sebelum commit."
    }
    Write-Host "[OK] .env di-ignore oleh git" -ForegroundColor Green
}

if (-not (Test-Path ".git")) {
    git init -b main
    Write-Host "[OK] git init -b main" -ForegroundColor Green
}

$status = git status --porcelain
if ($status) {
    git add -A
    Write-Host "`nStaged files:" -ForegroundColor Yellow
    git diff --cached --name-only | Select-Object -First 30
    $envStaged = git diff --cached --name-only | Select-String -Pattern "^\.env$"
    if ($envStaged) {
        Write-Error "ABORT: .env akan ter-commit! Hapus dari staging: git reset HEAD .env"
    }
    git commit -m "Initial commit: Portal SIMTAMAN Disperakimtan Batam"
    Write-Host "[OK] Initial commit created" -ForegroundColor Green
} else {
    Write-Host "[INFO] Working tree clean or already committed" -ForegroundColor Yellow
}

$remoteUrl = git remote get-url origin 2>$null
if ($remoteUrl) {
    Write-Host "[INFO] Remote origin sudah ada: $remoteUrl" -ForegroundColor Yellow
    Write-Host "Push: git push -u origin main"
    exit 0
}

if (Get-Command gh -ErrorAction SilentlyContinue) {
    $authStatus = gh auth status 2>&1
    if ($LASTEXITCODE -ne 0) {
        Write-Host "Login GitHub CLI dulu:" -ForegroundColor Yellow
        gh auth login
    }
    $visibility = if ($Public) { "--public" } else { "--private" }
    # Repo mungkin sudah ada di GitHub — coba push saja
    git remote add origin "https://github.com/$GitHubUsername/$RepoName.git" 2>$null
    git push -u origin main 2>$null
    if ($LASTEXITCODE -ne 0) {
        gh repo create $RepoName $visibility --source=. --remote=origin --push
    }
    Write-Host "`n[SUCCESS] Repo: https://github.com/$GitHubUsername/$RepoName" -ForegroundColor Green
} else {
    Write-Host "[WARN] gh CLI tidak ada. Buat repo manual di GitHub, lalu:" -ForegroundColor Yellow
    Write-Host "  git remote add origin https://github.com/$GitHubUsername/$RepoName.git"
    Write-Host "  git push -u origin main"
    Write-Host "`nPanduan lengkap: docs/deploy/GITHUB-SETUP.md"
}

Write-Host "`nClone di VPS (setelah deploy key):" -ForegroundColor Cyan
Write-Host "  git clone git@github.com:${GitHubUsername}/${RepoName}.git ."
