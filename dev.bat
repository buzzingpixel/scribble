@echo off

set cmd=%1
set allArgs=%*
for /f "tokens=1,* delims= " %%a in ("%*") do set allArgsExceptFirst=%%b
set secondArg=%2
set valid=false

:: If no command provided, list commands
if "%cmd%" == "" (
    set valid=true
    echo The following commands are available:
    echo   .\dev up
    echo   .\dev down
    echo   .\dev phpunit [args]
    echo   .\dev cli [args]
    echo   .\dev composer [args]
    echo   .\dev login [args]
)

:: If command is up or run, we need to run the docker containers and install composer and yarn dependencies
if "%cmd%" == "up" (
    set valid=true
    docker-compose -f docker-compose.yml -p scribble up -d
    docker exec -it --user root --workdir /app php-scribble bash -c "cd /app && composer install"
)

:: If the command is down, then we want to stop docker
if "%cmd%" == "down" (
    set valid=true
    docker-compose -f docker-compose.yml -p scribble down
)

:: Run phpunit if requested
if "%cmd%" == "phpunit" (
    set valid=true
    docker exec -it --user root --workdir /app php-scribble bash -c "chmod +x /app/vendor/bin/phpunit && /app/vendor/bin/phpunit --configuration /app/phpunit.xml %allArgsExceptFirst%"
)

:: Run cli if requested
if "%cmd%" == "cli" (
    set valid=true
    docker exec -it --user root --workdir /app php-scribble bash -c "php %allArgs%"
)

:: Run composer if requested
if "%cmd%" == "composer" (
    set valid=true
    docker exec -it --user root --workdir /app php-scribble bash -c "%allArgs%"
)

:: Login to a container if requested
if "%cmd%" == "login" (
    set valid=true
    docker exec -it --user root %secondArg%-scribble bash
)

:: If there was no valid command found, warn user
if not "%valid%" == "true" (
    echo Specified command not found
    exit /b 1
)

:: Exit with no error
exit /b 0
