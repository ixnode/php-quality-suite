# PHP Quality Suite

[![Release](https://img.shields.io/github/v/release/ixnode/php-quality-suite)](https://github.com/ixnode/php-quality-suite/releases)
[![](https://img.shields.io/github/release-date/ixnode/php-quality-suite)](https://github.com/twelvepics-com/php-calendar-builder/releases)
[![PHP](https://img.shields.io/badge/PHP-^8.0-777bb3.svg?logo=php&logoColor=white&labelColor=555555&style=flat)](https://www.php.net/supported-versions.php)
[![Rector - Instant Upgrades and Automated Refactoring](https://img.shields.io/badge/Rector-^2.1-73a165.svg?style=flat)](https://github.com/rectorphp/rector)
[![LICENSE](https://img.shields.io/github/license/ixnode/php-quality-suite)](https://github.com/ixnode/php-quality-suite/blob/master/LICENSE)

> PHP Quality Suite - A zero-config PHP quality toolbox combining Rector, PHPStan, and PHPMD. Run static analysis and automated refactorings with simple commands.

## 1. Installation

```bash
composer require --dev ixnode/php-quality-suite
```

```bash
vendor/bin/php-quality-suite -V
```

```bash
php-quality-suite 0.1.0 (2025-09-20 19:59:14) - Björn Hempel <bjoern@hempel.li>
```

## 2. Quick start

```bash
cp paths.yaml.dist paths.yaml
```

Adjust the included and excluded paths to your needs.

Run command:

```Bash
vendor/bin/php-quality-suite analyze --include=src --level=0 --dry-run
```
