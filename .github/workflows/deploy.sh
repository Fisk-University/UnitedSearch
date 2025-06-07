#!/bin/bash
set -e

ENV=$1
TAG=$2

DATE=$(date +"%Y%m%d_%H%M%S")
LOG_DIR="$HOME/deploy-logs"
mkdir -p "$LOG_DIR"
LOG_FILE="${LOG_DIR}/deploy_${TAG}.log"
LOCAL_BACKUP_DIR="$HOME/backups/omeka-${ENV}-${DATE}"
S3_BUCKET="rw.rosenwald-ci-cd-logs-backups"

echo "[INFO] Starting deployment for $TAG → $ENV" | tee "$LOG_FILE"
echo "[INFO] Backing up module $name to $LOCAL_BACKUP_DIR..." | tee -a "$LOG_FILE"

mkdir -p "$LOCAL_BACKUP_DIR"

FAILED=0

if [ -d /tmp/deployed/modules ]; then
  for dir in /tmp/deployed/modules/*; do
    name=$(basename "$dir")
    cp -r "/var/www/html/omeka-s/modules/$name" "$LOCAL_BACKUP_DIR/${name}_preupdate" 2>/dev/null || true

    mkdir -p "/var/www/html/omeka-s/modules/$name"
    rsync -av "$dir/" "/var/www/html/omeka-s/modules/$name/" >> "$LOG_FILE" || FAILED=1

    if [ -f "$dir/composer.json" ] && [ ! -d "$dir/vendor" ]; then
      echo "[STEP] Running composer install for $name" | tee -a "$LOG_FILE"
      cd "/var/www/html/omeka-s/modules/$name"
      timeout 300 composer install --no-dev --prefer-dist >> "$LOG_FILE" 2>&1 || {
        echo "[ERROR] Composer failed for $name" | tee -a "$LOG_FILE"
        FAILED=1
      }
    fi

    if [ "$FAILED" -ne 0 ]; then
      echo "[ERROR] Rolling back $name" | tee -a "$LOG_FILE"
      rm -rf "/var/www/html/omeka-s/modules/$name"
      cp -r "$LOCAL_BACKUP_DIR/${name}_preupdate" "/var/www/html/omeka-s/modules/$name"
    fi
  done
else
  echo "[ERROR] No modules to deploy." | tee -a "$LOG_FILE"
  exit 1
fi

echo "[STEP] Cleaning up..." | tee -a "$LOG_FILE"
rm -rf /tmp/artifact.zip /tmp/deployed

aws s3 cp --recursive "$LOCAL_BACKUP_DIR/" "s3://${S3_BUCKET}/${ENV}/backups/${TAG}/" || echo "[WARN] S3 backup failed" | tee -a "$LOG_FILE"
aws s3 cp "$LOG_FILE" "s3://${S3_BUCKET}/${ENV}/logs/deploy_${TAG}.log" || echo "[WARN] Log upload failed" | tee -a "$LOG_FILE"

echo "[SUCCESS] Deployment completed for $TAG on $ENV" | tee -a "$LOG_FILE"
