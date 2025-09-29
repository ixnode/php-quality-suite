## Best Practices with PHPStan

## First analysis

To run your first analysis, start with a safe, minimal check:

```Bash
vendor/bin/php-quality-suite phpstan --include=src,tests --level=0
```

| Argument              | Description                                                            |
|-----------------------|------------------------------------------------------------------------|
| `--include=src,tests` | Limits the analysis to the given directories (here `src` and `tests`). |
| `--level=0`           | Runs only the most critical checks (safe starting point).              |

This will give you an overview of potential issues in your codebase without applying any changes yet.

## Incremental approach

Increase the `--level` step by step (from 0 up to 10, see [PHPStan Rule Levels](https://phpstan.org/user-guide/rule-levels)).

Each level enables stricter checks — starting with catching critical errors, then gradually adding type safety,
dead-code detection, and advanced best-practice rules.

```bash
vendor/bin/php-quality-suite phpstan --include=src,tests --level=1
vendor/bin/php-quality-suite phpstan --include=src,tests --level=2
...
vendor/bin/php-quality-suite phpstan --include=src,tests --level=10
```

This incremental approach ensures that you can review and commit changes in small, controlled batches.
