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
- **Always sign your commits** using `git commit -S -m "message"`

To set up signing commits:

```bash
# Generate a GPG key if you don't have one
gpg --gen-key

# List your keys
gpg --list-secret-keys --keyid-format LONG

# Configure Git
git config --global user.signingkey YOUR_KEY_ID
git config --global commit.gpgsign true
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

## Questions?

Feel free to:
- Open an issue with your question
- Check the [discussions](https://github.com/AvalonHostingServices/whmcs-reseller-module/discussions)
- Email support@avalon.hosting

## License

By contributing to this project, you agree that your contributions will be licensed under its GPL-3.0 License.

---

Thank you for helping make this project better! 🎉
