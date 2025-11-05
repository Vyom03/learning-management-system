@echo off
echo Creating video content tables...
mysql -u root -ptemp@12345 loginauth < create-video-tables.sql
echo Done!
pause

