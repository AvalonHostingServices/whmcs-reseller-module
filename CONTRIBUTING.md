# Contributing to WHMCS Reseller Module

Thank you for your interest in contributing to the WHMCS Reseller Module! We welcome contributions from the community.

## Code of Conduct

This project and everyone participating in it is governed by our [Code of Conduct](CODE_OF_CONDUCT.md). By participating, you are expected to uphold this code.

## How Can I Contribute?

### Reporting Bugs

Before creating a bug report, check the [issue list](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues) as you might find out that you don't need to create one. When you create a bug report, please include as many details as possible:

- **Use a clear and descriptive title**
- **Describe the exact steps which reproduce the problem**
- **Provide specific examples to demonstrate the steps**
- **Describe the behavior you observed after following the steps**
- **Explain which behavior you expected to see instead and why**
- **Include screenshots and animated GIFs if possible**
- **Include your environment details** (WHMCS version, PHP version, OS, etc.)

### Suggesting Enhancements

Enhancement suggestions are tracked as [GitHub issues](https://github.com/AvalonHostingServices/whmcs-reseller-module/issues). When creating an enhancement suggestion, please include:

- **Use a clear and descriptive title**
- **Provide a step-by-step description of the suggested enhancement**
- **Provide specific examples to demonstrate the steps**
- **Describe the current behavior and the expected behavior**
- **Explain why this enhancement would be useful**

### Pull Requests

- Follow the [PHP coding standards](#coding-standards)
- Include appropriate test cases
- Update documentation as needed
- Keep commits atomic and meaningful
- Reference any related issues in your PR description

## Development Setup

1. Fork the repository
2. Clone your fork locally
3. Create a feature branch (`git checkout -b feature/your-feature-name`)
4. Make your changes
5. Commit your changes with sign-off (`git commit -S -m "Your message"`)
6. Push to your fork
7. Submit a pull request

## Coding Standards

### PHP

- Follow [PSR-12](https://www.php-fig.org/psr/psr-12/) coding standards
- Use 4 spaces for indentation
- Use meaningful variable and function names
- Add comments for complex logic
- Maximum line length: 120 characters

### JavaScript

- Use 2 spaces for indentation
- Use `const` by default, `let` for variables that change
- Add meaningful comments
- Follow ES6+ standards

### General

- Keep code DRY (Don't Repeat Yourself)
- Write self-documenting code
- Add inline comments for complex logic
- Use meaningful commit messages

## Commit Guidelines

- Use the past tense ("Add feature" not "Adds feature")
- Use the imperative mood ("Move cursor to..." not "Moves cursor to...")
- Keep commits small and focused
- Reference issues when relevant: "Closes #123" or "Fixes #456"
- **REQUIRED: Always sign your commits** using `git commit -S -m "message"`

All commits must be cryptographically signed with GPG. Unsigned commits will not be accepted.

### Setting Up Commit Signing

If you haven't set up commit signing yet:

```bash
# Generate a GPG key (if you don't have one)
gpg --gen-key

# List your keys
gpg --list-secret-keys --keyid-format LONG

# Configure Git to use your key
git config --global user.signingkey YOUR_KEY_ID
git config --global commit.gpgsign true

# Optional: Configure per-repository
git config commit.gpgsign true
git config user.signingkey YOUR_KEY_ID
```

### Signing Commits

```bash
# Sign a single commit
git commit -S -m "Your commit message"

# Sign all future commits (requires configuration above)
git commit -m "Your commit message"
```

### Verifying Your Signature

```bash
# View commit signatures in your branch
git log --show-signature

# View a single commit's signature
git show --show-signature <commit-hash>
```

## Testing

- Write tests for any new functionality
- Ensure all tests pass before submitting a PR
- Update existing tests if your changes affect them

## Documentation

- Update README.md if you change functionality
- Update CHANGELOG.md with your changes
- Add comments to complex code
- Update documentation in docs/ folder if applicable

## Release Management

For information about creating and managing releases, see [RELEASE_PROCESS.md](RELEASE_PROCESS.md).

Key points:
- Releases are created using semantic versioning (v1.0.0, v1.1.0, etc.)
- Push a version tag to automatically build and release the `modules` folder
- All commits in a release must be signed with GPG
- Release packages include only the `modules/` directory and all subdirectories
- SHA256 checksums are automatically generated for integrity verification

To create a release:
```bash
git tag -s v1.0.1 -m "Release version 1.0.1"
git push origin v1.0.1
```

## Questions?

Feel free to:
- Open an issue with your question
- Check the [discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)
- Email support@avalon.hosting

## License

By submitting a contribution to this project, you agree that your contribution will be governed by the terms of the [Proprietary License](LICENSE.md). In particular, you grant Avalon Hosting Services a perpetual, irrevocable, royalty-free license to use, modify, and incorporate your contribution into the Software. Your contribution does not grant you any ownership rights or additional use rights beyond those already set out in the license.

---

Thank you for helping make this project better! 🎉
