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

### PHP Version

The static analysis tools determine the target PHP version from your project's `composer.json`.

Make sure your `composer.json` contains the desired PHP version in the `require.php` field,
for example:

```json
{
    "require": {
        "php": "^8.0"
    }
}
```

This setting defines the PHP version Rector and PHPStan will use for parsing and refactoring.
Adjust this value according to the PHP version you want to migrate to or validate against.

### Paths

The **PHP Quality Suite** needs to know which paths to analyze and which ones to ignore.

A template configuration file is included in this package:

```bash
cp vendor/ixnode/php-quality-suite/paths.yaml.dist paths.yaml
```

Now adjust the file `paths.yaml` to match your project structure.

#### Example `paths.yaml`

```yaml
paths:
  src: src
  tests: tests
  vendor_gui: lib/VendorGuiBundle

excluded:
  - src/Legacy
  - src/Experimental
```

* `paths`: Directories or files to be analyzed. You can assign keys (e.g. `vendor_gui`) to reference them in CLI commands.
* `excluded`: Directories or files that are always excluded from analysis. These paths are passed to Rector automatically.

#### Notes

* All paths are relative to the project root.
* You can include both directories and single files.

#### Usage with `--include`

By default, all paths listed under paths are analyzed. You can restrict the analysis to specific entries using the --include option: `--include=src,vendor_gui`. This will analyze only the src and vendor_gui directories, while the excluded paths from paths.yaml are always respected.

### First check

To run your first analysis, use:

```Bash
vendor/bin/php-quality-suite analyze --include=src,tests --level=0 --dry-run
```

| Argument              | Description                                              |
|-----------------------|----------------------------------------------------------|
| `--include=src,tests` | Limits the analysis to the src directory.                |
| `--level=0`           | Runs only the most critical checks (safe to start with). | 
| `--dry-run`           | Shows the suggested changes without modifying any files. |

This will give you an overview of potential issues in your codebase without applying any changes yet.

Example output:

```bash
Rector Overview
---------------
Rector target PHP version: 8.0.0
Level:                     0
Include paths:             src
Rules:                     N/A


 1/1 [▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓▓] 100%
1 file with changes
===================

1) src/test.php:2

    ---------- begin diff ----------
@@ @@

 final class Test
 {
-    var string $test = 'test';
+    public string $test = 'test';
 }
    ----------- end diff -----------

Applied rules:
 * VarToPublicPropertyRector



 [OK] 1 file would have been changed (dry-run) by Rector
```