# WHMCS Reseller Module - Release Quick Reference

This is a quick reference for managing releases of the WHMCS Reseller Module.

## Repository Setup Summary

✅ **All files properly configured for v1.0.0 release**

### What's Been Set Up

1. **Professional GitHub Repository Files**
   - Comprehensive README with features and installation
   - CHANGELOG tracking version history
   - CODE_OF_CONDUCT establishing community standards
   - CONTRIBUTING with strict commit signing requirements
   - SECURITY policy for vulnerability reporting
   - LICENSE (GPLv3)

2. **Issue & Discussion Templates**
   - Bug Report template
   - Feature Request template
   - Q&A Discussion template
   - Show and Tell template
   - Ideas Discussion template
   - Announcements Discussion template

3. **Automated Release Workflow**
   - Release GitHub Action triggered by version tags
   - Auto-packages only the `modules/` folder
   - Creates ZIP and TAR.GZ archives
   - Generates SHA256 checksums
   - Publishes releases to GitHub

4. **Installation Tooling**
   - Linux/macOS installation script (install.sh)
   - Windows installation script (install.bat)
   - Comprehensive INSTALL.md guide

5. **Code Quality**
   - PHP linting workflow
   - Security scanning workflow

## Releasing v1.0.0 or Later

### Process Overview

1. **Make your changes** → Commit with signature → Test
2. **Update version files** → CHANGELOG, README, etc.
3. **Create release tag** → Push tag to trigger workflow
4. **GitHub Actions packages** → Creates release files
5. **Release goes live** → Available for resellers to download

### Step-by-Step Release

#### 1. Prepare Changes (on feature branch)

```bash
# Create and switch to feature branch
git checkout -b feature/your-feature

# Make your changes
# ... edit files ...

# Commit with signature (REQUIRED)
git commit -S -m "Your commit message"

# Push to your fork
git push origin feature/your-feature
```

#### 2. Create Pull Request & Get Approval

- Open PR on GitHub
- Go through review process
- All commits must be signed ✓ (or PR won't be mergeable)

#### 3. Merge to Master

```bash
# Switch to master
git checkout master

# Merge the pull request (via GitHub or command line)
git merge --no-ff feature/your-feature
git push origin master
```

#### 4. Update Version Files

Before creating the release tag, update:

**CHANGELOG.md** - Add new section:
```markdown
## [1.0.1] - 2026-03-25

### Fixed
- Bug fix description

### Added
- New feature description
```

**README.md** - Update version badge if needed

**modules/PROJECT.md** - Update version if applicable

Commit these updates:
```bash
git commit -S -m "Bump version to 1.0.1

- Update CHANGELOG with fixes and features
- Update version in documentation"
```

#### 5. Create & Push Release Tag

```bash
# Create signed, annotated tag (recommended)
git tag -s v1.0.1 -m "Release version 1.0.1

Improvements:
- Bug fixes
- Performance enhancements"

# Push the tag (this triggers the release workflow!)
git push origin v1.0.1
```

#### 6. Monitor & Verify

1. Go to GitHub Actions tab
2. Watch the "Release" workflow complete (1-2 minutes)
3. Check **Releases** tab for your new release
4. Verify package files and checksums are present

### Verify Release Workflow Success

```bash
# Check if tag was created
git tag -l v1.0.1

# View tag details
git show v1.0.1

# Check if tag is on GitHub
git ls-remote --tags origin v1.0.1
```

## Commit Signing Requirements

All contributions MUST use signed commits:

```bash
# Sign a single commit
git commit -S -m "Your message"

# Or enable automatic signing
git config --global commit.gpgsign true
git config --global user.signingkey YOUR_KEY_ID

# Then just use normal commit
git commit -m "Your message"
```

**Note:** Unsigned commits will NOT be accepted in pull requests.

## Release Artifacts

When you push a tag, the release workflow automatically creates:

- `whmcs-reseller-module-v1.0.1.zip` - ZIP package
- `whmcs-reseller-module-v1.0.1.zip.sha256` - Checksum file
- `whmcs-reseller-module-v1.0.1.tar.gz` - TAR.GZ package
- `whmcs-reseller-module-v1.0.1.tar.gz.sha256` - Checksum file

**What's included:**
- Only `modules/servers/products_reseller_server/` directory
- All subdirectories and files within
- NOT included: README, docs, CI config, etc.

This keeps reseller packages clean and focused.

## Version Numbering

Use semantic versioning (https://semver.org/):

```
v1.0.0 - Initial release
v1.0.1 - Bug fix (patch)
v1.1.0 - New feature (minor)
v2.0.0 - Breaking change (major)
```

Pre-releases:
```
v1.1.0-alpha.1
v1.1.0-beta.1
v1.1.0-rc.1
```

## Quick Commands Reference

```bash
# View all tags
git tag -l

# View tag details
git show v1.0.0

# List signed tags
git tag -l -n 3

# Delete tag locally
git tag -d v1.0.0

# Delete tag on GitHub
git push origin --delete v1.0.0

# Create and sign tag in one go
git tag -s v1.0.1 -m "Release v1.0.1" && git push origin v1.0.1

# View commits since last tag
git log v1.0.0..HEAD --oneline
```

## Troubleshooting

### Workflow didn't trigger?

- Verify tag name matches pattern `v*` (e.g., `v1.0.0`)
- Check Actions tab for any error messages
- Ensure all commits in the tag are signed

### Release files missing?

- Check the Release workflow in Actions tab for errors
- Verify the tag was pushed with `git push origin v1.0.1`
- Ensure `modules/` directory exists in repo

### Need to fix a release?

```bash
# Delete the tag (locally and remotely)
git tag -d v1.0.0
git push origin --delete v1.0.0

# After fixing, create new tag
git tag -s v1.0.0 -m "Release v1.0.0" && git push origin v1.0.0
```

## Documentation References

- **RELEASE_PROCESS.md** - Detailed release procedures
- **CONTRIBUTING.md** - Contribution guidelines with signing details
- **INSTALL.md** - Installation scripts and manual installation
- **README.md** - Project overview and features

## Support Links

- GitHub Issues: https://github.com/AvalonHostingServices/whmcs-reseller-module/issues
- Discussions: https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions
- Email: support@avalon.hosting
- Docs: https://docs.avalon.hosting/

---

**Current Version:** 1.0.0  
**Repository:** AvalonHostingServices/whmcs-reseller-module  
**Last Updated:** March 24, 2026
