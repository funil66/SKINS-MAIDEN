#!/bin/bash

# Backup script for Iron Code Skins platform

# Define backup directory
BACKUP_DIR="/path/to/backup/directory"

# Create backup directory if it doesn't exist
mkdir -p $BACKUP_DIR

# Define the date format for the backup filename
DATE=$(date +"%Y%m%d_%H%M%S")

# Backup the backend database
echo "Backing up the backend database..."
mysqldump -u username -p database_name > $BACKUP_DIR/backend_backup_$DATE.sql

# Backup the frontend build files
echo "Backing up the frontend build files..."
tar -czf $BACKUP_DIR/frontend_backup_$DATE.tar.gz -C /path/to/frontend/build .

# Backup the webapp audit files
echo "Backing up the webapp audit files..."
tar -czf $BACKUP_DIR/webapp_audit_backup_$DATE.tar.gz -C /path/to/webapp-audit/src .

# Log the backup completion
echo "Backup completed successfully on $DATE" >> $BACKUP_DIR/backup_log.txt

# Optional: Remove backups older than 7 days
find $BACKUP_DIR -type f -name "*.sql" -mtime +7 -exec rm {} \;
find $BACKUP_DIR -type f -name "*.tar.gz" -mtime +7 -exec rm {} \;

echo "Old backups removed."