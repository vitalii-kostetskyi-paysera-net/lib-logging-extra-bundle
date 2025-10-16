<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

use DateTimeInterface;
use Doctrine\Common\Persistence\Proxy as LegacyProxy;
use Doctrine\Persistence\Proxy;
use Doctrine\ORM\PersistentCollection;
use Monolog\Utils;
use Throwable;

/**
 * To be used on classes extending NormalizerFormatter
 */
trait FormatterTrait
{
    protected function normalize($data, $depth = 0)
    {
        $prenormalizedData = $this->prenormalizeData($data, $depth);

        return parent::normalize($prenormalizedData, $depth);
    }

    private function prenormalizeData($data, $depth)
    {
        // Check for PersistentCollection and Proxy first
        if ($data instanceof PersistentCollection) {
            // Check initialization status first, before accessing the collection
            $isInitialized = $data->isInitialized();

            // Only expand initialized collections if depth <= 3
            // When expanded, return array of class names for entities
            if ($isInitialized && $depth <= 3) {
                $result = [];
                foreach ($data as $entity) {
                    // Convert entities in collections to just their class name
                    $result[] = is_object($entity) ? get_class($entity) : $entity;
                }
                return $result;
            }
            // Always return class name for uninitialized or deep collections
            return get_class($data);
        }

        // Always normalize Proxies regardless of depth to get at least the ID
        if ($data instanceof Proxy || $data instanceof LegacyProxy) {
            return $this->normalizeProxy($data);
        }

        if ($depth > 3) {
            return $this->getScalarRepresentation($data);
        }

        if (
            is_object($data)
            && !$data instanceof DateTimeInterface
            && !$data instanceof Throwable
        ) {
            return $this->normalizeObject($data);
        }

        return $data;
    }

    private function getScalarRepresentation($data)
    {
        if (is_scalar($data) || $data === null) {
            return $data;
        }

        if (is_object($data)) {
            return get_class($data);
        }

        return gettype($data);
    }

    private function normalizeObject($data)
    {
        $result = [];
        foreach ((array)$data as $key => $value) {
            $parts = explode("\0", $key);
            $fixedKey = end($parts);
            if (substr($fixedKey, 0, 2) === '__') {
                continue;
            }

            $result[$fixedKey] = $value;
        }

        return $result;
    }

    private function normalizeProxy(Proxy $data)
    {
        if ($data->__isInitialized()) {
            return $this->normalizeObject($data);
        }

        if (method_exists($data, 'getId')) {
            return ['id' => $data->getId()];
        }

        return '[Uninitialized]';
    }

    protected function toJson($data, $ignoreErrors = false): string
    {
        // Monolog 2.x has Utils::jsonEncode(), Monolog 1.x does not
        if (method_exists(Utils::class, 'jsonEncode')) {
            return Utils::jsonEncode(
                $data,
                JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE,
                $ignoreErrors
            );
        }

        // Fallback for Monolog 1.x
        $json = json_encode(
            $data,
            JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false && !$ignoreErrors) {
            throw new \RuntimeException('JSON encoding failed: ' . json_last_error_msg());
        }

        return $json ?: '{}';
    }
}
