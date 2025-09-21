## 4. Best Practices with Rector

### Best Practices with PHP

To run your first analysis, start with a safe, minimal check:

```Bash
vendor/bin/php-quality-suite analyze --include=src,tests --level=0 --dry-run
```

| Argument              | Description                                                        |
|-----------------------|--------------------------------------------------------------------|
| `--include=src,tests` | Limits the analysis to the given directories (here src and tests). |
| `--level=0`           | Runs only the most critical checks (safe starting point).          | 
| `--dry-run`           | Shows suggested changes without modifying any files.               |

This will give you an overview of potential issues in your codebase without applying any changes yet.

<details>

<summary>Example output:</summary>

### You can add a header

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

</details>

#### Incremental approach

Increase the `--level` step by step (from 0 up to 20).

Each level enables more rules — starting with critical fixes, then gradually adding type declarations, code quality
improvements, and style fixes.

```bash
vendor/bin/php-quality-suite analyze --include=src,tests --level=1 --dry-run
vendor/bin/php-quality-suite analyze --include=src,tests --level=2 --dry-run
...
vendor/bin/php-quality-suite analyze --include=src,tests --level=20 --dry-run
```

This incremental approach ensures that you can review and commit changes in small, controlled batches.

#### Target PHP version

The PHP Quality Suite analyzes your code against the PHP version specified in your project’s `composer.json`.

For example:

```json
{
    "require": {
        "php": "^8.0"
    }
}
```

If you plan to migrate to a newer PHP release (e.g. from `8.0` to `8.1` or `8.2`), first update the version constraint
in `composer.json`:

```json
{
    "require": {
        "php": "^8.1"
    }
}
```

Then rerun the analysis with `--level=0` for the new target version, and gradually increase the level again.

This ensures your code is cleaned up, compatible, and ready for the upgrade.

### Best Practices with Symfony

The PHP Quality Suite integrates Rector’s Symfony sets to automatically apply framework-specific best practices and
upgrade rules. This allows you to modernize and improve your Symfony codebase with minimal effort.

#### Run checks against a specific Symfony version

The following command checks your codebase against the Symfony rules for the version you specify.

This helps ensure compatibility when upgrading from your current Symfony release to a newer one:

```bash
vendor/bin/php-quality-suite analyze --with-symfony=<target-version> --dry-run
```

| Argument                          | Description                                                                     |
|-----------------------------------|---------------------------------------------------------------------------------|
| `--with-symfony=<target-version>` | Applies Rector’s rules for a specific Symfony version (e.g. `6.0`, `6.1`, etc.) |

### Add Symfony-specific improvements

In addition to version-specific upgrades, you can enable extra checks that focus on code quality and best practices:

```bash
vendor/bin/php-quality-suite analyze \
    --with-symfony=<symfony-version> \
    --with-symfony-code-quality \
    --with-symfony-constructor-injection \
    --dry-run
```

| Argument                               | Description                                                              |
|----------------------------------------|--------------------------------------------------------------------------|
| `--with-symfony-code-quality`          | Specify whether to use the **Code Quality** rules from Symfony.          |
| `--with-symfony-constructor-injection` | Specify whether to use the **Constructor Injection** rules from Symfony. |

* **Code Quality**: Improves readability and maintainability by applying common Symfony coding standards and simplifications.
* **Constructor Injection**: Ensures services receive their dependencies through the constructor instead of service locators
  or property injection. This makes classes easier to test, reduces hidden dependencies, and aligns with modern Symfony
  best practices.

### Upgrading Symfony via Composer

When upgrading Symfony, you need to adjust all Symfony-related packages in your project’s `composer.json` to match the
target version you want to migrate to:

```json
{
    "require": {
        "php": "^8.0",
        "symfony/asset": "6.0.*",
        "symfony/browser-kit": "6.0.*",
        "symfony/console": "6.0.*",
        "symfony/dependency-injection": "6.0.*",
        "symfony/dotenv": "6.0.*",
        "symfony/framework-bundle": "6.0.*",
        "symfony/http-foundation": "6.0.*",
        "symfony/http-kernel": "6.0.*",
        "symfony/routing": "6.0.*",
        "symfony/yaml": "6.0.*",
        ...
    }
}
```

Then run:

```bash
composer update symfony/*
```

This upgrades all installed Symfony components to the specified version.

It is highly recommended to run PHP Quality Suite before and after this step to automatically handle deprecations and
ensure your codebase is compatible with the new Symfony release.
