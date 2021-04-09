#!/bin/bash

DB_HOST=""
DB_USERNAME=""
DB_PASSWORD=""
DB_NAME=""

BACKUP_PATH="$1"

# Remove old backups
find "$BACKUP_PATH" -name "db-backup-*.sql" -mtime +14 -type f -delete

# Backup database
PGPASSWORD="$DB_PASSWORD"  pg_dump --host="$DB_HOST" --username="$DB_USERNAME" "$DB_NAME" > "$BACKUP_PATH/db-backup-$(date +"%Y-%m-%d").sql"
