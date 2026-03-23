# Security Policy

## Reporting a Vulnerability

The WHMCS Reseller Module team takes security seriously. If you discover a security vulnerability, please do **not** open a public issue.

Instead, please email security@avalon.hosting with:

- **Description of the vulnerability**
- **Steps to reproduce the issue**
- **Potential impact of the vulnerability**
- **Suggested fix (if available)**

We will acknowledge your report within 48 hours and work to address the issue promptly. Please allow reasonable time for us to address the vulnerability before public disclosure.

## Responsible Disclosure

We appreciate your responsible disclosure and ask that you:

- Not publicly disclose the vulnerability until we've had time to address it
- Not access or modify other users' data
- Not perform testing against our infrastructure or production systems without permission
- Keep your findings private during the resolution process

## Security Practices

This module implements:

- Input validation and sanitization
- SQL injection prevention (prepared statements)
- CSRF token protection
- Secure authentication and session management
- Secure credential storage
- Error handling that doesn't leak sensitive information
- Regular security updates

## Supported Versions

We provide security updates for:

- The current stable release
- The previous minor version (for critical issues only)

Example:
- If 1.2.0 is the latest stable, we support 1.2.x and 1.1.x
- Version 1.0.x would be considered end-of-life

## Security Updates

Security fixes are treated with high priority and released as patch versions (e.g., 1.0.1) as soon as possible after being reported and fixed.

## Thank You

We appreciate the security research community and thank you for helping keep our project secure!

---

For inquiries about security practices, contact: security@avalon.hosting
