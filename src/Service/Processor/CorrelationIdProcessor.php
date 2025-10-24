<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use Monolog\Processor\ProcessorInterface;
use Paysera\LoggingExtraBundle\Service\CorrelationIdProvider;

if (class_exists('Monolog\LogRecord')) {
    // Monolog v3+ - has LogRecord class with typed ProcessorInterface
    class CorrelationIdProcessor implements ProcessorInterface
    {
        private $correlationIdProvider;

        public function __construct(CorrelationIdProvider $correlationIdProvider)
        {
            $this->correlationIdProvider = $correlationIdProvider;
        }

        public function __invoke(\Monolog\LogRecord $record): \Monolog\LogRecord
        {
            $correlationId = $this->correlationIdProvider->getCorrelationId();

            return new \Monolog\LogRecord(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $record->context,
                array_merge($record->extra, ['correlation_id' => $correlationId]),
                $record->formatted
            );
        }
    }
} else {
    // Monolog v1/v2 - uses array with untyped ProcessorInterface
    class CorrelationIdProcessor implements ProcessorInterface
    {
        private $correlationIdProvider;

        public function __construct(CorrelationIdProvider $correlationIdProvider)
        {
            $this->correlationIdProvider = $correlationIdProvider;
        }

        /**
         * @param array $record
         * @return array
         */
        public function __invoke($record)
        {
            $correlationId = $this->correlationIdProvider->getCorrelationId();
            $record['extra']['correlation_id'] = $correlationId;
            return $record;
        }
    }
}
