# Installation Scripts

This directory contains helper scripts to make installing the WHMCS Reseller Module easier for resellers.

## Available Scripts

### Linux/macOS: `install.sh`

A comprehensive bash script that:
- Auto-detects your WHMCS installation
- Verifies package integrity with SHA256
- Creates automatic backups
- Sets correct file permissions
- Supports both ZIP and TAR.GZ packages

**Requirements:**
- bash
- unzip or tar
- Standard Unix utilities

**Usage:**

```bash
# Basic installation (auto-detect WHMCS path)
./install.sh -f whmcs-reseller-module-v1.0.0.zip

# Specify WHMCS path
./install.sh -p /var/www/whmcs -f whmcs-reseller-module-v1.0.0.zip

# Verify checksum before installation
./install.sh -f whmcs-reseller-module-v1.0.0.zip -v -s abc123def456...

# View help
./install.sh -h
```

**Options:**
- `-p, --path PATH` - Path to WHMCS installation (auto-detected if omitted)
- `-f, --file FILE` - Path to release package file (required)
- `-v, --verify` - Enable SHA256 checksum verification
- `-s, --sha256 HASH` - Expected SHA256 hash (required with `-v`)
- `-h, --help` - Show help message

**Examples:**

```bash
# Install from home directory public_html
chmod +x install.sh
./install.sh -f whmcs-reseller-module-v1.0.0.zip

# Install with automatic checksum verification
./install.sh -f whmcs-reseller-module-v1.0.0.zip -v
# Script will look for whmcs-reseller-module-v1.0.0.zip.sha256

# Install to custom path with manual hash
./install.sh \
  -p /home/hostingco/public_html \
  -f whmcs-reseller-module-v1.0.0.zip \
  -v \
  -s "abcd1234efgh5678..."
```

### Windows: `install.bat`

A batch script for Windows systems that:
- Auto-detects common WHMCS installation paths
- Creates backups before installation
- Uses PowerShell for reliable extraction
- Provides step-by-step installation feedback

**Requirements:**
- Windows 7 or later
- PowerShell 3.0+ (usually pre-installed)
- ZIP file package

**Usage:**

```batch
REM Basic installation (auto-detect WHMCS path)
install.bat -f whmcs-reseller-module-v1.0.0.zip

REM Specify WHMCS path
install.bat -p "C:\whmcs" -f whmcs-reseller-module-v1.0.0.zip

REM View help
install.bat -h
```

**Options:**
- `-p, --path PATH` - Path to WHMCS installation (auto-detected if omitted)
- `-f, --file FILE` - Path to release package file (required)
- `-h, --help` - Show help message

**Examples:**

```batch
REM Install to auto-detected path
install.bat -f whmcs-reseller-module-v1.0.0.zip

REM Install to custom path
install.bat -p "D:\hosting\whmcs" -f whmcs-reseller-module-v1.0.0.zip

REM Run with elevated privileges (if needed for permission issues)
REM Right-click Command Prompt -> Run as Administrator, then:
install.bat -p "C:\whmcs" -f whmcs-reseller-module-v1.0.0.zip
```

## Manual Installation

If you prefer not to use the scripts, you can install manually:

### Linux/macOS

```bash
# Extract to WHMCS root
unzip whmcs-reseller-module-v1.0.0.zip -d /path/to/whmcs/

# Or with tar.gz
tar -xzf whmcs-reseller-module-v1.0.0.tar.gz -C /path/to/whmcs/

# Set permissions
chmod -R 755 /path/to/whmcs/modules/servers/products_reseller_server
```

### Windows

1. Right-click the ZIP file
2. Select "Extract All..."
3. Browse to your WHMCS root directory
4. Click "Extract"

Or using PowerShell:

```powershell
Add-Type -AssemblyName 'System.IO.Compression.FileSystem'
[System.IO.Compression.ZipFile]::ExtractToDirectory('C:\path\to\whmcs-reseller-module-v1.0.0.zip', 'C:\path\to\whmcs')
```

## Verifying Installation

After installation, verify the module was installed correctly:

1. Log in to WHMCS Admin
2. Go to **System Settings** > **Servers**
3. Create a new server
4. In the server type dropdown, you should see "Products Reseller Server"

If you don't see the module type, check:
- Files are extracted to `modules/servers/products_reseller_server/`
- File permissions are correct (755 or equivalent)
- Your WHMCS installation path is correct

## Troubleshooting

### Script won't run (Linux/macOS)

```bash
# Make script executable
chmod +x install.sh

# Then run
./install.sh -f whmcs-reseller-module-v1.0.0.zip
```

### Permission denied error

- **Linux/macOS:** Ensure the script is executable and you have write permissions to WHMCS directory
- **Windows:** Run Command Prompt as Administrator (right-click "Run as administrator")

### Module not appearing in WHMCS

1. Verify files extracted to correct location:
   ```
   WHMCS_ROOT/modules/servers/products_reseller_server/
   ```

2. Check file ownership and permissions:
   ```bash
   ls -la /path/to/whmcs/modules/servers/products_reseller_server/
   ```

3. Restart WHMCS and clear cache:
   - In WHMCS Admin: System Settings > Tools > Maintenance > Clear Cache

### Checksum verification fails

The package may be corrupted. Try:
1. Re-download the package from the GitHub release page
2. Verify the file size matches what's listed in the release
3. Try a different download method (wget, curl, browser)

## Getting Help

If you encounter issues:

1. Check the [Documentation](https://docs.avalon.hosting/)
2. Visit [GitHub Discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)
3. Open a [GitHub Issue](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues)
4. Email: support@avalon.hosting

## Security Notes

- Always verify SHA256 checksums when available
- Use the official GitHub releases page to download packages
- Don't run scripts from untrusted sources
- Keep your WHMCS installation updated
- Regularly backup your WHMCS directory before installing updates
