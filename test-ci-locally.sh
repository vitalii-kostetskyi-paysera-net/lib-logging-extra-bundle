#!/usr/bin/env bash
set -e

SYMFONY_VERSION="${1:-4.*}"
DEPENDENCY_VERSION="${2:-highest}"

# Set environment variables (like in CI)
export SYMFONY_REQUIRE="${SYMFONY_VERSION}"
export COMPOSER_PROCESS_TIMEOUT=0
export COMPOSER_NO_INTERACTION=1
export COMPOSER_NO_AUDIT=1

# Backup composer.lock and composer.json
[ -f "composer.lock" ] && cp composer.lock composer.lock.backup
cp composer.json composer.json.backup

# Remove composer.lock to simulate require-lock-file: false
rm -f composer.lock

# Install symfony/flex to enforce SYMFONY_REQUIRE
composer config --no-plugins allow-plugins.symfony/flex true
composer require --no-update symfony/flex:"^1.0|^2.0"

# Install dependencies
if [ "${DEPENDENCY_VERSION}" = "lowest" ]; then
    composer update --prefer-lowest --prefer-stable
else
    composer update --prefer-stable
fi

# Run PHPUnit
./bin/phpunit

# Restore backups
mv composer.json.backup composer.json
[ -f "composer.lock.backup" ] && mv composer.lock.backup composer.lock || rm -f composer.lock