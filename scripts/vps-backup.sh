#!/usr/bin/env bash
# Backup harian / manual di VPS — pasang di cron:
# 0 2 * * * cd /var/www/samasta && bash scripts/vps-backup.sh >> /var/log/samasta-backup.log 2>&1
# Saat kegiatan pemutakhiran (opsional, tiap jam):
# 0 * * * * cd /var/www/samasta && BACKUP_LABEL=pemutakhiran bash scripts/vps-backup.sh >> /var/log/samasta-backup.log 2>&1

set -euo pipefail

APP_DIR="${APP_DIR:-/var/www/samasta}"
LABEL="${BACKUP_LABEL:-harian}"
FULL="${BACKUP_FULL:-1}"

cd "$APP_DIR"

ARGS=(--label="$LABEL")
if [[ "$FULL" == "1" ]]; then
    ARGS=(--full "${ARGS[@]}")
fi

php artisan simtaman:backup "${ARGS[@]}"
