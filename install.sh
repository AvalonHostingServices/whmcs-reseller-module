#!/bin/bash
# WHMCS Reseller Module Installer
# This script helps install the WHMCS Reseller Module from a release package

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Functions
print_help() {
    cat << EOF
WHMCS Reseller Module Installer

Usage: $0 [OPTIONS]

OPTIONS:
    -p, --path PATH          Path to your WHMCS installation (default: /home/*/public_html)
    -f, --file FILE          Path to the release package (zip or tar.gz)
    -v, --verify             Verify SHA256 checksum before installation
    -s, --sha256 HASH        SHA256 hash to verify against
    -h, --help               Show this help message

EXAMPLES:
    # Install to specific WHMCS path
    $0 -p /var/www/whmcs -f whmcs-reseller-module-v1.0.0.zip

    # Verify checksum before installation
    $0 -f whmcs-reseller-module-v1.0.0.zip -v -s abc123...

EOF
}

error() {
    echo -e "${RED}ERROR: $1${NC}" >&2
    exit 1
}

success() {
    echo -e "${GREEN}✓ $1${NC}"
}

warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

info() {
    echo -e "${YELLOW}ℹ $1${NC}"
}

# Parse arguments
WHMCS_PATH=""
PACKAGE_FILE=""
VERIFY_CHECKSUM=false
EXPECTED_SHA256=""

while [[ $# -gt 0 ]]; do
    case $1 in
        -p|--path)
            WHMCS_PATH="$2"
            shift 2
            ;;
        -f|--file)
            PACKAGE_FILE="$2"
            shift 2
            ;;
        -v|--verify)
            VERIFY_CHECKSUM=true
            shift
            ;;
        -s|--sha256)
            EXPECTED_SHA256="$2"
            shift 2
            ;;
        -h|--help)
            print_help
            exit 0
            ;;
        *)
            error "Unknown option: $1"
            ;;
    esac
done

# Validate inputs
if [ -z "$PACKAGE_FILE" ]; then
    error "Package file is required. Use -f or --file option."
fi

if [ ! -f "$PACKAGE_FILE" ]; then
    error "Package file not found: $PACKAGE_FILE"
fi

# Detect package type
if [[ "$PACKAGE_FILE" == *.zip ]]; then
    PACKAGE_TYPE="zip"
elif [[ "$PACKAGE_FILE" == *.tar.gz ]] || [[ "$PACKAGE_FILE" == *.tgz ]]; then
    PACKAGE_TYPE="tar"
else
    error "Unsupported package format. Use .zip or .tar.gz"
fi

# Detect WHMCS path if not provided
if [ -z "$WHMCS_PATH" ]; then
    info "Detecting WHMCS installation path..."
    
    # Try common paths
    for path in /home/*/public_html /var/www/whmcs /home/whmcs /opt/whmcs; do
        if [ -f "$path/init.php" ] || [ -f "$path/configuration.php" ]; then
            WHMCS_PATH="$path"
            success "Found WHMCS at: $WHMCS_PATH"
            break
        fi
    done
    
    if [ -z "$WHMCS_PATH" ]; then
        error "Could not find WHMCS installation. Please specify path with -p option."
    fi
fi

# Verify WHMCS installation
if [ ! -d "$WHMCS_PATH" ]; then
    error "WHMCS path does not exist: $WHMCS_PATH"
fi

if [ ! -f "$WHMCS_PATH/init.php" ] && [ ! -f "$WHMCS_PATH/configuration.php" ]; then
    warning "WHMCS configuration files not found in $WHMCS_PATH"
    warning "Make sure this is a valid WHMCS installation directory"
    read -p "Continue anyway? (y/n) " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        error "Installation cancelled"
    fi
fi

# Verify checksum if requested
if [ "$VERIFY_CHECKSUM" = true ]; then
    if [ -z "$EXPECTED_SHA256" ]; then
        info "Looking for .sha256 file..."
        SHA256_FILE="${PACKAGE_FILE}.sha256"
        
        if [ -f "$SHA256_FILE" ]; then
            EXPECTED_SHA256=$(awk '{print $1}' "$SHA256_FILE")
            success "Found checksum file: $SHA256_FILE"
        else
            error "Checksum file not found: $SHA256_FILE"
        fi
    fi
    
    info "Verifying checksum..."
    if command -v sha256sum &> /dev/null; then
        ACTUAL_SHA256=$(sha256sum "$PACKAGE_FILE" | awk '{print $1}')
    elif command -v shasum &> /dev/null; then
        ACTUAL_SHA256=$(shasum -a 256 "$PACKAGE_FILE" | awk '{print $1}')
    else
        warning "sha256sum/shasum not found, skipping verification"
    fi
    
    if [ -n "$ACTUAL_SHA256" ]; then
        if [ "$ACTUAL_SHA256" != "$EXPECTED_SHA256" ]; then
            error "Checksum mismatch!
    Expected: $EXPECTED_SHA256
    Actual:   $ACTUAL_SHA256
    The package may be corrupted or tampered with."
        fi
        success "Checksum verification passed"
    fi
fi

# Create backup
BACKUP_DIR="$WHMCS_PATH/modules/servers/products_reseller_server.backup.$(date +%s)"
if [ -d "$WHMCS_PATH/modules/servers/products_reseller_server" ]; then
    info "Creating backup of existing module..."
    cp -r "$WHMCS_PATH/modules/servers/products_reseller_server" "$BACKUP_DIR"
    success "Backup created: $BACKUP_DIR"
fi

# Extract package
info "Extracting package..."
TEMP_DIR=$(mktemp -d)
trap "rm -rf $TEMP_DIR" EXIT

if [ "$PACKAGE_TYPE" = "zip" ]; then
    unzip -q "$PACKAGE_FILE" -d "$TEMP_DIR"
else
    tar -xzf "$PACKAGE_FILE" -C "$TEMP_DIR"
fi

success "Package extracted"

# Copy module files
if [ -d "$TEMP_DIR/modules" ]; then
    info "Installing module files..."
    cp -r "$TEMP_DIR/modules"/* "$WHMCS_PATH/modules/" 2>/dev/null || \
        error "Failed to copy module files. Check permissions on $WHMCS_PATH"
    success "Module files installed"
else
    error "Package structure invalid. Could not find modules/ directory."
fi

# Set permissions
if [ -d "$WHMCS_PATH/modules/servers/products_reseller_server" ]; then
    info "Setting file permissions..."
    chmod -R 755 "$WHMCS_PATH/modules/servers/products_reseller_server"
    success "Permissions set correctly"
fi

# Installation complete
echo ""
echo -e "${GREEN}╔════════════════════════════════════════╗${NC}"
echo -e "${GREEN}║  Installation Completed Successfully!  ║${NC}"
echo -e "${GREEN}╚════════════════════════════════════════╝${NC}"
echo ""
echo "Next steps:"
echo "1. Log in to your WHMCS Admin Panel"
echo "2. Go to System Settings > Servers"
echo "3. Create a new server with type 'Products Reseller Server'"
echo "4. Configure your reseller credentials"
echo ""
echo "Documentation: https://docs.avalon.hosting/"
echo "Support: support@avalon.hosting"
echo ""
