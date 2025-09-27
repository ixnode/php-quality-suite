# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](http://keepachangelog.com/en/1.0.0/)
and this project adheres to [Semantic Versioning](http://semver.org/spec/v2.0.0.html).

## Releases

### [0.1.17] - 2025-09-27

* Add rector "withImportNames" rules

### [0.1.16] - 2025-09-27

* Implement excluded and included rules
* Class refactoring

### [0.1.15] - 2025-09-26

* Add excluded rules
* Add first PHPUnit tests
* Update README.md file
* Refactoring

### [0.1.14] - 2025-09-22

* Fix typos within README.md documentation

### [0.1.13] - 2025-09-22

* Rename environment names
* Parameters refactoring

### [0.1.12] - 2025-09-21

* Parameters refactoring
* General refactoring
* Rename the path.yaml file to pqs.yml

### [0.1.11] - 2025-09-21

* Switch php-quality-suite analyze command to php-quality-suite rector

### [0.1.10] - 2025-09-21

* Add paths.yaml usage and fallback to package default

### [0.1.9] - 2025-09-21

* Add symfony presets

### [0.1.8] - 2025-09-21

* Replace symfony/yaml with yaml_parse_file to avoid composer conflicts

### [0.1.7] - 2025-09-21

* Update README.md quick start section

### [0.1.6] - 2025-09-21

* Add analyze output example

### [0.1.5] - 2025-09-21

* Fixed relative paths to analyze

### [0.1.4] - 2025-09-21

* Fixed paths.yml access

### [0.1.3] - 2025-09-21

* Install rector also when package is used as dependency

### [0.1.2] - 2025-09-21

* Ensure Rector binary is resolved correctly in root and package context

### [0.1.1] - 2025-09-21

* Direct call of the rector command

### [0.1.0] - 2025-09-20

* Initial release
* Add src
* Add README.md
* Add LICENSE.md

## Add new version

```bash
# Checkout master branch
$ git checkout main && git pull

# Check current version
$ vendor/bin/version-manager --current

# Increase patch version
$ vendor/bin/version-manager --patch

# Change changelog
$ vi CHANGELOG.md

# Push new version
$ git add CHANGELOG.md VERSION && git commit -m "Add version $(cat VERSION)" && git push

# Tag and push new version
$ git tag -a "$(cat VERSION)" -m "Version $(cat VERSION)" && git push origin "$(cat VERSION)"
```
