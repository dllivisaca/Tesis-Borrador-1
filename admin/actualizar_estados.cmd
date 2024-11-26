@echo off
set "timestamp=%date% %time%"
echo Script ejecutándose en: %timestamp% >> "C:\xampp_nuevo\htdocs\Login\admin\actualizar_estados_log.txt"

rem Ejecutar el script PHP y redirigir cualquier error al log
"C:\xampp_nuevo\php\php.exe" "C:\xampp_nuevo\htdocs\Login\admin\actualizar_estados.php" 2>> "C:\xampp_nuevo\htdocs\Login\admin\actualizar_estados_log.txt"

rem Registrar el código de salida del script PHP
echo Código de salida: %ERRORLEVEL% >> "C:\xampp_nuevo\htdocs\Login\admin\actualizar_estados_log.txt"

set "timestamp=%date% %time%"
echo Script finalizado en: %timestamp% >> "C:\xampp_nuevo\htdocs\Login\admin\actualizar_estados_log.txt"

timeout /t 5 /nobreak >nul
