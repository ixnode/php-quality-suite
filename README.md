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

### Paths

The **PHP Quality Suite** needs to know which paths to analyze and which ones to ignore.

A template configuration file is included in this package:

```bash
cp vendor/ixnode/php-quality-suite/config/pqs.yml.dist pqs.yml
```

Now adjust the file `pqs.yml` to match your project structure.

#### Example `pqs.yml`

```yaml
paths-included:
    src: src
    tests: tests
    vendor_gui: lib/VendorGuiBundle

paths-excluded:
    - src/Legacy
    - src/Experimental
    
rules-excluded:
    - Rector\Php52\Rector\Property\VarToPublicPropertyRector:<=8.0 # only for PHP version <= 8.0
    - Rector\Php74\Rector\Assign\NullCoalescingOperatorRector:>8.1 # only for php version > 8.1
    - Rector\Php74\Rector\Closure\ClosureToArrowFunctionRector # always
    - Rector\Renaming\Rector\MethodCall\RenameMethodRector:symfony<=6.4 # only for Symfony version <= 6.4
    - Rector\Arguments\Rector\ClassMethod\ArgumentAdderRector:symfony>6.4 # only for Symfony version > 6.4
    - Rector\Renaming\Rector\Name\RenameClassRector:symfony # for all symfony versions
```

* `paths-included`: Directories or files to be analyzed. You can assign keys (e.g. `vendor_gui`) to reference them in CLI commands.
* `paths-excluded`: Directories or files that are always excluded from analysis. These paths are passed to Rector automatically.
* `rules-excluded`: Rector rules that should **not be applied**.  
  * You can exclude rules either unconditionally or based on environment conditions:
    * **Without condition** → the rule is always excluded.
    * **With PHP version condition** (`<=8.0`, `>8.1`, …) → the rule is excluded only if the condition matches the current PHP version.
    * **With Symfony version condition** (`symfony<=6.4`, `symfony>6.4`, …) → the rule is excluded only if the condition matches the current Symfony version.
    * **With a framework tag only** (`:symfony`) → the rule is excluded for **all versions** of that framework.


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
