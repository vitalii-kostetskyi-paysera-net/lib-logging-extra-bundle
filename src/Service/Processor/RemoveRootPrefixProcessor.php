<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use InvalidArgumentException;
use Monolog\Processor\ProcessorInterface;

if (class_exists('Monolog\LogRecord')) {
    // Monolog v3+ - has LogRecord class with typed ProcessorInterface
    class RemoveRootPrefixProcessor implements ProcessorInterface
    {
        private $rootPrefix;

        public function __construct(string $rootPrefix)
        {
            $this->rootPrefix = realpath($rootPrefix);
            if ($this->rootPrefix === false) {
                throw new InvalidArgumentException('Invalid root prefix specified');
            }
        }

        public function __invoke(\Monolog\LogRecord $record): \Monolog\LogRecord
        {
            $message = str_replace($this->rootPrefix, '<root>', $record->message);

            return new \Monolog\LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $message,
                $record->context,
                $record->extra,
                $record->formatted
            );
        }
    }
} else {
    // Monolog v1/v2 - uses array with untyped ProcessorInterface
    class RemoveRootPrefixProcessor implements ProcessorInterface
    {
        private $rootPrefix;

        public function __construct(string $rootPrefix)
        {
            $this->rootPrefix = realpath($rootPrefix);
            if ($this->rootPrefix === false) {
                throw new InvalidArgumentException('Invalid root prefix specified');
            }
        }

        /**
         * @param array $record
         * @return array
         */
        public function __invoke($record)
        {
            $record['message'] = str_replace($this->rootPrefix, '<root>', $record['message']);
            return $record;
        }
    }
}
