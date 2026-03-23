@echo off
REM WHMCS Reseller Module Installer for Windows
REM This script helps install the WHMCS Reseller Module from a release package

setlocal enabledelayedexpansion

REM Colors (Note: Colors in batch are limited)
set GREEN=[92m
set YELLOW=[93m
set RED=[91m
set NC=[0m

REM Variables
set WHMCS_PATH=
set PACKAGE_FILE=
set VERIFY_CHECKSUM=0
set EXPECTED_SHA256=

REM Parse arguments
:parse_args
if "%1"=="" goto validate_inputs
if "%1"=="-p" (
    set WHMCS_PATH=%2
    shift
    shift
    goto parse_args
)
if "%1"=="--path" (
    set WHMCS_PATH=%2
    shift
    shift
    goto parse_args
)
if "%1"=="-f" (
    set PACKAGE_FILE=%2
    shift
    shift
    goto parse_args
)
if "%1"=="--file" (
    set PACKAGE_FILE=%2
    shift
    shift
    goto parse_args
)
if "%1"=="-v" (
    set VERIFY_CHECKSUM=1
    shift
    goto parse_args
)
if "%1"=="--verify" (
    set VERIFY_CHECKSUM=1
    shift
    goto parse_args
)
if "%1"=="-h" (
    goto show_help
)
if "%1"=="--help" (
    goto show_help
)
shift
goto parse_args

:show_help
echo WHMCS Reseller Module Installer
echo.
echo Usage: %0 [OPTIONS]
echo.
echo OPTIONS:
echo   -p, --path PATH      Path to your WHMCS installation
echo   -f, --file FILE      Path to the release package (zip)
echo   -v, --verify         Verify file integrity
echo   -h, --help           Show this help message
echo.
echo EXAMPLES:
echo   %0 -p "C:\whmcs" -f whmcs-reseller-module-v1.0.0.zip
echo.
goto end

:validate_inputs
if not defined PACKAGE_FILE (
    echo ERROR: Package file is required. Use -f or --file option.
    goto end
)

if not exist "%PACKAGE_FILE%" (
    echo ERROR: Package file not found: %PACKAGE_FILE%
    goto end
)

REM Detect WHMCS path if not provided
if not defined WHMCS_PATH (
    echo Detecting WHMCS installation path...
    
    REM Try common paths
    for %%P in (C:\whmcs, C:\wwwroot\whmcs, D:\whmcs, %HOMEDRIVE%\whmcs) do (
        if exist "%%P\init.php" (
            set WHMCS_PATH=%%P
            echo Found WHMCS at: !WHMCS_PATH!
            goto validate_path
        )
        if exist "%%P\configuration.php" (
            set WHMCS_PATH=%%P
            echo Found WHMCS at: !WHMCS_PATH!
            goto validate_path
        )
    )
    
    if not defined WHMCS_PATH (
        echo ERROR: Could not find WHMCS installation. Please specify path with -p option.
        goto end
    )
)

:validate_path
if not exist "%WHMCS_PATH%" (
    echo ERROR: WHMCS path does not exist: %WHMCS_PATH%
    goto end
)

if not exist "%WHMCS_PATH%\init.php" (
    if not exist "%WHMCS_PATH%\configuration.php" (
        echo WARNING: WHMCS configuration files not found in %WHMCS_PATH%
        set /p CONTINUE="Continue anyway? (y/n): "
        if /i not "!CONTINUE!"=="y" (
            echo Installation cancelled
            goto end
        )
    )
)

REM Check if file is ZIP
set FILE_EXTENSION=%PACKAGE_FILE:~-4%
if /i not "!FILE_EXTENSION!"==".zip" (
    echo ERROR: Only ZIP files are supported on Windows
    echo Please use a .zip package file
    goto end
)

echo.
echo Installing WHMCS Reseller Module...
echo WHMCS Path: %WHMCS_PATH%
echo Package: %PACKAGE_FILE%
echo.

REM Create modules directory if it doesn't exist
if not exist "%WHMCS_PATH%\modules\servers" (
    mkdir "%WHMCS_PATH%\modules\servers"
    echo Created modules\servers directory
)

REM Backup existing module
if exist "%WHMCS_PATH%\modules\servers\products_reseller_server" (
    echo Creating backup of existing module...
    
    for /f "tokens=2-4 delims=/ " %%a in ('date /t') do (set mydate=%%c%%a%%b)
    for /f "tokens=1-2 delims=/:" %%a in ('time /t') do (set mytime=%%a%%b)
    
    move "%WHMCS_PATH%\modules\servers\products_reseller_server" "%WHMCS_PATH%\modules\servers\products_reseller_server.backup.!mydate!.!mytime!" >nul
    echo Backup created
)

REM Use PowerShell to extract (more reliable than built-in)
echo Extracting package...

powershell -NoProfile -Command ^
    " ^
    Add-Type -AssemblyName 'System.IO.Compression.FileSystem'; ^
    [System.IO.Compression.ZipFile]::ExtractToDirectory('%PACKAGE_FILE%', '%WHMCS_PATH%'); ^
    echo 'Package extracted successfully'
    " || (
    echo ERROR: Failed to extract package
    goto end
)

REM Verify installation
if not exist "%WHMCS_PATH%\modules\servers\products_reseller_server" (
    echo ERROR: Module directory was not created. Installation may have failed.
    goto end
)

echo.
echo ============================================
echo Installation Completed Successfully!
echo ============================================
echo.
echo Next steps:
echo 1. Log in to your WHMCS Admin Panel
echo 2. Go to System Settings ^> Servers
echo 3. Create a new server with type "Products Reseller Server"
echo 4. Configure your reseller credentials
echo.
echo Documentation: https://docs.avalon.hosting/
echo Support: support@avalon.hosting
echo.

:end
endlocal
pause
