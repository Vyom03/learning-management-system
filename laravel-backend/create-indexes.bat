@echo off
echo Creating database indexes...
mysql -u root -ptemp@12345 loginauth < create-indexes-safe.sql
echo Done!
pause

