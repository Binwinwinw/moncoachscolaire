@echo off
cd /d "C:\xampp\mysql\bin"
echo [%date% %time%] V?rification hebdomadaire MySQL >> C:\xampp\logs\mysql_weekly.log
.\mysqlcheck.exe -u root --all-databases --check >> C:\xampp\logs\mysql_weekly.log 2>&1
.\mysqlcheck.exe -u root --all-databases --repair >> C:\xampp\logs\mysql_weekly.log 2>&1
echo [%date% %time%] V?rification termin?e >> C:\xampp\logs\mysql_weekly.log
