<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Tests\Functional\Fixtures\Logger;

use Psr\Log\LoggerInterface;

/**
 * @php-cs-fixer-ignore Paysera/php_basic_code_style_default_values_in_constructor
 */
if (class_exists('Symfony\Bridge\Doctrine\Logger\DbalLogger')) {
    // Symfony < 7
    class TestDbalLogger extends \Symfony\Bridge\Doctrine\Logger\DbalLogger
    {
        private $queryCount = 0;

        public function startQuery($sql, array $params = null, array $types = null): void
        {
            parent::startQuery($sql, $params, $types);
            $this->queryCount++;
        }

        public function getQueryCount()
        {
            return $this->queryCount;
        }
    }
} else {
    // Symfony 7+ - DbalLogger was removed, provide a minimal implementation
    class TestDbalLogger
    {
        private $queryCount = 0;
        private $logger;

        public function __construct(LoggerInterface $logger = null)
        {
            $this->logger = $logger;
        }

        public function startQuery($sql, array $params = null, array $types = null): void
        {
            $this->queryCount++;
            if ($this->logger) {
                $this->logger->debug($sql, ['params' => $params, 'types' => $types]);
            }
        }

        public function stopQuery(): void
        {
            // No-op for test purposes
        }

        public function getQueryCount()
        {
            return $this->queryCount;
        }
    }
}
