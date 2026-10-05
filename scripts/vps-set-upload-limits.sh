#!/usr/bin/env bash
# Naikkan batas upload PHP-FPM + cek Nginx untuk foto taman (max 10 MB di app, server disarankan 20M).
# Jalankan di VPS: sudo bash scripts/vps-set-upload-limits.sh
#
# Opsional env:
#   PHP_VER=8.3 UPLOAD_LIMIT=20M POST_LIMIT=20M NGINX_BODY=20M

set -euo pipefail

UPLOAD_LIMIT="${UPLOAD_LIMIT:-20M}"
POST_LIMIT="${POST_LIMIT:-20M}"
NGINX_BODY="${NGINX_BODY:-20M}"

if [[ "${EUID:-$(id -u)}" -ne 0 ]]; then
    echo "Jalankan dengan sudo: sudo bash scripts/vps-set-upload-limits.sh"
    exit 1
fi

APP_DIR="${APP_DIR:-/var/www/samasta}"
if [[ -d "$APP_DIR" ]]; then
    cd "$APP_DIR"
fi

detect_php_ver() {
    if [[ -n "${PHP_VER:-}" ]]; then
        echo "$PHP_VER"
        return
    fi
    if command -v php >/dev/null 2>&1; then
        php -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;'
        return
    fi
    echo "8.3"
}

PHP_VER="$(detect_php_ver)"
FPM_INI="/etc/php/${PHP_VER}/fpm/php.ini"
FPM_CONF_D="/etc/php/${PHP_VER}/fpm/conf.d"
DROP_IN="${FPM_CONF_D}/99-simtaman-upload-limits.ini"

echo "=== SIMTAMAN — set upload limits ==="
echo "PHP FPM: ${PHP_VER}"
echo "Target: upload_max_filesize=${UPLOAD_LIMIT}, post_max_size=${POST_LIMIT}"

if [[ ! -d "$(dirname "$FPM_INI")" ]]; then
    echo "[ERROR] PHP-FPM tidak ditemukan di ${FPM_INI}"
    echo "Set PHP_VER manual, contoh: PHP_VER=8.2 sudo bash scripts/vps-set-upload-limits.sh"
    exit 1
fi

mkdir -p "$FPM_CONF_D"
cat > "$DROP_IN" <<EOF
; SIMTAMAN — foto taman hingga 10 MB per file (buffer server 20M)
upload_max_filesize = ${UPLOAD_LIMIT}
post_max_size = ${POST_LIMIT}
max_file_uploads = 25
EOF
echo "[OK] Ditulis: ${DROP_IN}"

if systemctl is-active --quiet "php${PHP_VER}-fpm" 2>/dev/null; then
    systemctl reload "php${PHP_VER}-fpm"
    echo "[OK] Reload php${PHP_VER}-fpm"
elif systemctl is-active --quiet php-fpm 2>/dev/null; then
    systemctl reload php-fpm
    echo "[OK] Reload php-fpm"
else
    echo "[WARN] Service php-fpm tidak ditemukan — reload manual."
fi

echo ""
echo "[Verifikasi FPM via PHP CLI — nilai FPM mengikuti setelah reload]"
php -r 'echo "upload_max_filesize=".ini_get("upload_max_filesize")."\npost_max_size=".ini_get("post_max_size")."\n";' || true

echo ""
echo "=== Nginx client_max_body_size ==="
NGINX_UPDATED=0
if command -v nginx >/dev/null 2>&1; then
    mapfile -t CONF_FILES < <(grep -Rl "root.*samasta\|sitaman\|samasta/public" /etc/nginx/sites-enabled /etc/nginx/conf.d 2>/dev/null | head -5 || true)
    if [[ ${#CONF_FILES[@]} -eq 0 ]]; then
        echo "[WARN] File vhost Nginx SIMTAMAN tidak terdeteksi otomatis."
        echo "       Tambahkan manual di server block: client_max_body_size ${NGINX_BODY};"
    else
        for f in "${CONF_FILES[@]}"; do
            if grep -q "client_max_body_size" "$f"; then
                sed -i.bak-simtaman "s/client_max_body_size[^;]*;/client_max_body_size ${NGINX_BODY};/" "$f"
            else
                sed -i.bak-simtaman "/server_name/a\\    client_max_body_size ${NGINX_BODY};" "$f" 2>/dev/null || \
                    sed -i.bak-simtaman "/root /a\\    client_max_body_size ${NGINX_BODY};" "$f"
            fi
            echo "[OK] Nginx: ${f} → client_max_body_size ${NGINX_BODY}"
            NGINX_UPDATED=1
        done
        if [[ "$NGINX_UPDATED" -eq 1 ]]; then
            nginx -t
            systemctl reload nginx
            echo "[OK] Nginx reloaded"
        fi
    fi
else
    echo "[WARN] nginx tidak terpasang"
fi

echo ""
echo "Selesai. Uji upload foto 3–5 MB di admin Kelola Taman."
echo "Panduan: docs/deploy/PHP-UPLOAD-LIMITS.md"
