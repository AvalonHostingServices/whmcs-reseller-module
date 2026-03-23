# Release Process

This document describes how to create and publish releases for the WHMCS Reseller Module.

## Overview

The release process is automated using GitHub Actions. When you push a version tag (e.g., `v1.0.0`), a GitHub Action automatically:

1. Packages only the `modules` folder
2. Creates ZIP and TAR.GZ archives
3. Generates SHA256 checksums
4. Creates a GitHub Release with installation instructions
5. Attaches the package files for download

## Creating a Release

### Step 1: Ensure All Changes Are Committed and Signed

All commits must be signed with GPG. To verify commits are signed:

```bash
git log --oneline --graph
# Look for the "Verified" badge next to commits
```

To enable automatic signing of commits:

```bash
git config --global commit.gpgsign true
```

### Step 2: Update Version Information

Update the version in the following files:

1. **CHANGELOG.md** - Add your release notes under a new version header
2. **modules/PROJECT.md** - Update the version number if needed
3. **whmcs.json** - If version is tracked there, update it

### Step 3: Create and Push the Release Tag

```bash
# Create a signed, annotated tag (recommended)
git tag -s v1.0.0 -m "Release version 1.0.0"

# Push the tag to trigger the release workflow
git push origin v1.0.0
```

Or create a lightweight tag:

```bash
# Create a lightweight tag
git tag v1.0.0

# Push the tag
git push origin v1.0.0
```

### Step 4: Monitor the Release Workflow

1. Go to your GitHub repository
2. Navigate to **Actions**
3. Look for the "Release" workflow
4. Wait for it to complete (typically 1-2 minutes)
5. Check the **Releases** tab for your new release

## Release Package Contents

The release package contains only the `modules` folder with the following structure:

```
modules/
└── servers/
    └── products_reseller_server/
        ├── ajax_functions.php
        ├── hooks.php
        ├── pr_server_classes.php
        ├── products_reseller_server.php
        ├── whmcs.json
        ├── hooks/
        │   └── prs_hooks.php
        ├── images/
        │   ├── cpanel/
        │   └── (other images)
        └── templates/
            └── cpanel.tpl
```

## Installation Instructions for Resellers

Resellers receiving the release package should:

1. Download the ZIP or TAR.GZ file
2. Verify the SHA256 checksum
3. Extract to their WHMCS root directory:
   ```bash
   unzip whmcs-reseller-module-v1.0.0.zip -d /path/to/whmcs/
   ```
4. Log in to WHMCS Admin
5. Configure the module in **System Settings > Servers**

## Verifying Package Integrity

SHA256 checksums are provided for each release. To verify:

```bash
# On Linux/Mac
sha256sum -c whmcs-reseller-module-v1.0.0.zip.sha256

# On macOS
shasum -a 256 -c whmcs-reseller-module-v1.0.0.zip.sha256

# On Windows (PowerShell)
(Get-FileHash -Path "whmcs-reseller-module-v1.0.0.zip" -Algorithm SHA256).Hash
```

## Version Numbering

This project follows [Semantic Versioning](https://semver.org/):

- **MAJOR** version for incompatible API changes
- **MINOR** version for backwards-compatible functionality additions
- **PATCH** version for backwards-compatible bug fixes

Examples:
- `v1.0.0` - Initial release
- `v1.0.1` - Bug fix (patch)
- `v1.1.0` - New feature (minor)
- `v2.0.0` - Breaking change (major)

## Managing Pre-releases

For pre-release versions (alpha, beta, RC), use tags like:

```bash
git tag -s v1.1.0-beta.1 -m "Beta release"
git push origin v1.1.0-beta.1
```

The GitHub Action will automatically mark these as pre-releases.

## Rollback Process

If you need to unpublish a release:

1. Go to your GitHub repository
2. Navigate to **Releases**
3. Find the release
4. Click **Edit**
5. Check "This is a pre-release" or delete using the **Delete** option

To delete a tag locally and remotely:

```bash
# Delete locally
git tag -d v1.0.0

# Delete remotely
git push origin --delete v1.0.0
```

## Troubleshooting

### Release workflow fails

1. Check the **Actions** tab for error logs
2. Ensure your GPG key is configured on GitHub
3. Verify the tag follows the correct format (`v*`)
4. Ensure all commits are signed

### Missing files in package

The workflow only packages the `modules/` folder. If files are missing:

1. Verify they exist in the `modules/` directory
2. Check the `.gitignore` file isn't excluding them
3. Verify files are committed to Git

### Checksum mismatch

If checksums don't match:

1. Re-download the file from the release
2. Ensure you're comparing the correct file
3. Try a different download location/mirror

## Questions?

For issues related to the release process, please:

1. Check [GitHub Discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)
2. Open an [Issue](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues)
3. Email support@avalon.hosting
