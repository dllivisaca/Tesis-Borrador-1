@echo off
set "timestamp=%date% %time%"
echo Script ejecutándose en: %timestamp% >> "C:\xampp_nuevo\htdocs\Login\admin\ejecucion_cmd_log.txt"

rem Ejecutar el script PHP y redirigir cualquier error al log
"C:\xampp_nuevo\php\php.exe" "C:\xampp_nuevo\htdocs\Login\admin\enviar_recordatorios.php" 2>> "C:\xampp_nuevo\htdocs\Login\admin\ejecucion_cmd_log.txt"

rem Registrar el código de salida del script PHP
echo Código de salida: %ERRORLEVEL% >> "C:\xampp_nuevo\htdocs\Login\admin\ejecucion_cmd_log.txt"

set "timestamp=%date% %time%"
echo Script finalizado en: %timestamp% >> "C:\xampp_nuevo\htdocs\Login\admin\ejecucion_cmd_log.txt"

timeout /t 5 /nobreak >nul
