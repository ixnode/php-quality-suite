# PHP Quality Suite

[![Release](https://img.shields.io/github/v/release/ixnode/php-quality-suite)](https://github.com/ixnode/php-quality-suite/releases)
[![](https://img.shields.io/github/release-date/ixnode/php-quality-suite)](https://github.com/twelvepics-com/php-calendar-builder/releases)
[![PHP](https://img.shields.io/badge/PHP-^8.0-777bb3.svg?logo=php&logoColor=white&labelColor=555555&style=flat)](https://www.php.net/supported-versions.php)
[![Rector - Instant Upgrades and Automated Refactoring](https://img.shields.io/badge/Rector-^2.1-73a165.svg?style=flat)](https://github.com/rectorphp/rector)
[![LICENSE](https://img.shields.io/github/license/ixnode/php-quality-suite)](https://github.com/ixnode/php-quality-suite/blob/master/LICENSE)


🚀 **Zero-config. Out-of-the-box. Instant code quality.**  

The PHP Quality Suite combines [Rector](https://github.com/rectorphp/rector), [PHPStan](https://github.com/phpstan/phpstan), and [PHPMD](https://phpmd.org/) into a single developer-friendly toolbox.

Run static analysis and automated refactorings with simple CLI commands – no configuration required. Just:

```bash
vendor/bin/php-quality-suite rector --level=0 --dry-run
```

✔️ No setup needed – works immediately after installation<br>
✔️ Instant upgrades – migrate between PHP or Symfony versions<br>
✔️ Quality built-in – type safety, dead code removal, coding standards

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

Make sure your `composer.json` defines the target PHP version for analysis, e.g.:

```json
{
    "require": {
        "php": "^8.0"
    }
}
```

Analyze your codebase right away:

```bash
vendor/bin/php-quality-suite rector --include=src --level=0 --dry-run
```

✅ Runs instantly<br>
✅ No setup required<br>
✅ Safe first checks without modifying files

Remove `--dry-run` once you’re ready to apply the fixes automatically.

> **Hint**: By default, this example uses the src directory. For customizing paths or excluding directories, see
> the following chapter **Preparation**.

## 3. Preparation

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

### Configuration

The **PHP Quality Suite** needs to know which paths to analyze and which ones to ignore. The same applies to the rules that are used or ignored.

A template configuration file is included in this package:

```bash
cp vendor/ixnode/php-quality-suite/config/pqs.yml.dist pqs.yml
```

Now adapt the `pqs.yml` file to your project structure and the analyzation target.

#### Example `pqs.yml`

```yaml
paths-included:
    src: src
    tests: tests
    vendor_gui: lib/VendorGuiBundle

paths-excluded:
    - src/Legacy
    - src/Experimental

rules-included:
    ContinueToBreakInSwitchRector: Rector\Php52\Rector\Switch_\ContinueToBreakInSwitchRector
    TernaryToElvisRector: Rector\Php53\Rector\Ternary\TernaryToElvisRector

rules-excluded:
    - Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector # always
    - Rector\Php52\Rector\Property\VarToPublicPropertyRector:<=8.0 # only for PHP version <= 8.0
    - Rector\Php74\Rector\Assign\NullCoalescingOperatorRector:>8.1 # only for PHP version > 8.1
    - Rector\Renaming\Rector\MethodCall\RenameMethodRector:symfony<=6.4 # only for Symfony version <= 6.4
    - Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector:symfony>6.4 # only for Symfony version > 6.4
    - Rector\Renaming\Rector\Name\RenameClassRector:symfony # for all Symfony versions
```

* `paths-included`: Directories or files to be analyzed. You can assign keys (e.g. `vendor_gui`) to reference them in CLI commands.
* `paths-excluded`: Directories or files that are always excluded from analysis. These paths are passed to Rector automatically. If no rule is specified, the default rules of PHP or Symfony are applied.
* `rules-included`: Rector rules to be analyzed. You can assign keys (e.g. `TernaryToElvisRector`) to reference them in CLI commands.
* `rules-excluded`: Rector rules that should not be applied. The behavior depends on optional tagging (`php`, `symfony`) and version conditions:
  * **Rules without tagging and without version**
    * → Exclusion is always applied.
  * **Rules without tagging but with version**
    * → Exclusion is applied only if the current PHP version matches the condition; otherwise, the exclusion is ignored.
  * **Rules with php tagging and without version**
    * → Same as rules without tagging and without version.
  * **Rules with php tagging and with version**
    * → Same as rules without tagging but with version.
  * **Rules with symfony tagging and without version**
    * → Exclusion is applied only if a Symfony version is being analyzed; otherwise, the exclusion is ignored.
  * **Rules with symfony tagging and with version**
    * → Exclusion is applied only if a Symfony version is being analyzed and it matches the condition; otherwise, the exclusion is ignored.

#### Notes

* All paths are relative to the project root.
* You can include both directories and single files.
* If `pqs.yml` is missing, the default configuration `pqs.yml.dist` from the package will be used.

#### Usage with `--include`

By default, all paths listed under paths are analyzed. You can restrict the analysis to specific entries using the
`--include` option: `--include=src,vendor_gui`. This will analyze only the `src` and `vendor_gui` directories, while
the excluded paths from `pqs.yml` are always respected.

## 4. Best Practices

See:

* [Best Practices with Rector](docs/best-practices/rector.md)
* [Best Practices with PHPStan](docs/best-practices/rector.md)
* [Best Practices with PHP Mess detector](docs/best-practices/rector.md)

## 5. Development

```bash
git clone git@github.com:ixnode/php-quality-suite.git && cd php-quality-suite
```

```bash
composer install
```

```bash
composer test
```

## 6. License

This tool is licensed under the MIT License - see the [LICENSE](/LICENSE) file for details.
