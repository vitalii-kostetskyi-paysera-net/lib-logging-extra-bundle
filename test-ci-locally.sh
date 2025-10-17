#!/usr/bin/env bash
set -e

# Usage: ./test-ci-locally.sh [PHP_VERSION] [SYMFONY_VERSION] [DEPENDENCY_VERSION]
# Examples:
#   ./test-ci-locally.sh 8.4 7.* highest
#   ./test-ci-locally.sh 8.3 6.* lowest
#   ./test-ci-locally.sh 8.2 5.*

PHP_VERSION="${1:-8.4}"
SYMFONY_VERSION="${2:-7.*}"
DEPENDENCY_VERSION="${3:-highest}"

echo "=========================================="
echo "Testing with:"
echo "  PHP: ${PHP_VERSION}"
echo "  Symfony: ${SYMFONY_VERSION}"
echo "  Dependencies: ${DEPENDENCY_VERSION}"
echo "=========================================="
echo ""

# Build the Docker image with the specified versions
IMAGE_NAME="lib-logging-extra-bundle-test"
# Sanitize tag name by replacing special characters
SANITIZED_SYMFONY_VERSION=$(echo "${SYMFONY_VERSION}" | sed 's/[^a-zA-Z0-9._-]/-/g')
IMAGE_TAG="${PHP_VERSION}-symfony-${SANITIZED_SYMFONY_VERSION}-${DEPENDENCY_VERSION}"

echo "Building Docker image..."
docker build \
    --build-arg PHP_VERSION="${PHP_VERSION}" \
    --build-arg SYMFONY_VERSION="${SYMFONY_VERSION}" \
    --build-arg DEPENDENCY_VERSION="${DEPENDENCY_VERSION}" \
    -f Dockerfile.test \
    -t "${IMAGE_NAME}:${IMAGE_TAG}" \
    .

echo ""
echo "Running tests..."
docker run --rm "${IMAGE_NAME}:${IMAGE_TAG}"

echo ""
echo "=========================================="
echo "Tests completed successfully!"
echo "=========================================="