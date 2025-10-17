<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

/**
 * Provides normalize() method with correct signature based on Monolog version
 *
 * Monolog v3 (requires PHP >= 8.1) uses mixed type hints
 * Monolog v1/v2 (works with PHP 7.4-8.x) does not use type hints
 */
if (class_exists('Monolog\LogRecord')) {
    // Monolog v3+ - uses mixed type hints
    trait NormalizeCompatibilityTrait
    {
        /**
         * {@inheritdoc}
         */
        protected function normalize(mixed $data, int $depth = 0): mixed
        {
            return $this->normalizeWithPrenormalization($data, $depth);
        }
    }
} else {
    // Monolog v1/v2 - no type hints
    trait NormalizeCompatibilityTrait
    {
        /**
         * {@inheritdoc}
         * @param mixed $data
         * @param int $depth
         * @return mixed
         */
        protected function normalize($data, $depth = 0)
        {
            return $this->normalizeWithPrenormalization($data, $depth);
        }
    }
}
