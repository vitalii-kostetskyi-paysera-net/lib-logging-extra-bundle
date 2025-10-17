<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

/**
 * Provides normalize() method with correct signature based on PHP version
 */
if (PHP_VERSION_ID >= 80000) {
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
