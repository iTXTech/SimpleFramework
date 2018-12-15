@echo off
title SimpleFramework
cd /d %~dp0

if exist bin\php\php.exe (
    set PHP_BINARY=bin\php\php.exe
) else (
    set PHP_BINARY=php
)

if exist SimpleFramework.phar (
    set SF_FILE=SimpleFramework.phar
) else (
    set SF_FILE=src\iTXTech\SimpleFramework\SimpleFramework.php
)

REM php 7.2 requires VC++ 2015, so XP is not supported
if exist bin\mintty.exe (
    start "" bin\mintty.exe -o Columns=88 -o Rows=32 -o AllowBlinking=0 -o FontQuality=3 -o Font="Consolas" -o FontHeight=10 -o CursorType=0 -o CursorBlinks=1 -h error -t "SimpleFramework" -w max %PHP_BINARY% %SF_FILE% %*
) else (
    powershell %PHP_BINARY% %SF_FILE% %*
)
