@echo off
echo Setting up all LMS tables (quizzes and forums)...
mysql -u root -ptemp@12345 loginauth < setup-all-tables.sql
echo Done!
pause

