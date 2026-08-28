#!/usr/bin/env bash
# Generate SSH deploy key for GitHub on VPS BatamGarden
# Run on VPS: bash scripts/vps-setup-deploy-key.sh

set -euo pipefail

KEY_PATH="$HOME/.ssh/samasta_deploy"
CONFIG_BLOCK=$'Host github.com\n  HostName github.com\n  User git\n  IdentityFile ~/.ssh/samasta_deploy\n  IdentitiesOnly yes\n'

echo "=== SIMTAMAN — GitHub Deploy Key Setup ==="

if [[ -f "$KEY_PATH" ]]; then
    echo "[INFO] Key already exists: $KEY_PATH"
else
    ssh-keygen -t ed25519 -C "batam-garden-vps-deploy" -f "$KEY_PATH" -N ""
    chmod 600 "$KEY_PATH"
    chmod 644 "${KEY_PATH}.pub"
    echo "[OK] Deploy key created"
fi

if ! grep -q "IdentityFile ~/.ssh/samasta_deploy" "$HOME/.ssh/config" 2>/dev/null; then
    mkdir -p "$HOME/.ssh"
    chmod 700 "$HOME/.ssh"
    printf '%s\n' "$CONFIG_BLOCK" >> "$HOME/.ssh/config"
    chmod 600 "$HOME/.ssh/config"
    echo "[OK] SSH config updated"
fi

echo ""
echo "=== PUBLIC KEY — copy ke GitHub Deploy Keys ==="
echo ""
cat "${KEY_PATH}.pub"
echo ""
echo "GitHub → Repo → Settings → Deploy keys → Add deploy key"
echo "Title: BatamGarden VPS | Allow write: OFF"
echo ""
echo "Test: ssh -T git@github.com"
