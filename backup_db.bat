@echo off
set TIMESTAMP=%date:~10,4%-%date:~4,2%-%date:~7,2%_%time:~0,2%-%time:~3,2%-%time:~6,2%
set TIMESTAMP=%TIMESTAMP: =0%
set BACKUP_FILE=c:\xampp\htdocs\cnpapp\db_backups\auto_backup_%TIMESTAMP%.sql

echo Backing up databases to %BACKUP_FILE%...
"C:\xampp\mysql\bin\mysqldump.exe" -u root --databases cnpapp phpmyadmin > "%BACKUP_FILE%"

if %ERRORLEVEL% equ 0 (
    echo Backup successful!
) else (
    echo Backup failed!
)
pause
