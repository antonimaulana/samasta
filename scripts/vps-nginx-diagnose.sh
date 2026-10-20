#!/usr/bin/env bash
# Diagnosa Nginx — duplicate default_server, dll.
# Usage: bash scripts/vps-nginx-diagnose.sh

set -euo pipefail

echo "=== nginx -t ==="
sudo nginx -t 2>&1 || true

echo ""
echo "=== sites-enabled ==="
ls -la /etc/nginx/sites-enabled/ 2>/dev/null || true

echo ""
echo "=== default_server (port 80/443) ==="
grep -rn "default_server" /etc/nginx/sites-enabled/ /etc/nginx/sites-available/ 2>/dev/null || echo "(tidak ditemukan)"

echo ""
echo "=== systemctl nginx ==="
systemctl is-active nginx 2>/dev/null || true
systemctl status nginx --no-pager -l 2>/dev/null | head -15 || true

echo ""
echo "=== curl lokal ==="
curl -sI -m 3 http://127.0.0.1/ 2>&1 | head -5 || true

echo ""
echo "Panduan: docs/deploy/NGINX-DUPLICATE-DEFAULT-SERVER.md"
