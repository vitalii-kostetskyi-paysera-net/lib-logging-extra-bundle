<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use Monolog\Processor\ProcessorInterface;
use Paysera\LoggingExtraBundle\Service\CorrelationIdProvider;

class CorrelationIdProcessor implements ProcessorInterface
{
    private $correlationIdProvider;

    public function __construct(CorrelationIdProvider $correlationIdProvider)
    {
        $this->correlationIdProvider = $correlationIdProvider;
    }

    /**
     * @param array|\Monolog\LogRecord $record
     * @return array|\Monolog\LogRecord
     */
    public function __invoke($record)
    {
        $correlationId = $this->correlationIdProvider->getCorrelationId();

        // Handle both Monolog v2 (array) and v3 (LogRecord)
        // Check if it's a LogRecord without importing the class (Monolog v3+)
        if (is_object($record) && get_class($record) === 'Monolog\LogRecord') {
            // Create new LogRecord with modified extra field
            $logRecordClass = get_class($record);
            return new $logRecordClass(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $record->context,
                array_merge($record->extra, ['correlation_id' => $correlationId]),
                $record->formatted
            );
        }

        // Monolog v1/v2 array handling
        $record['extra']['correlation_id'] = $correlationId;
        return $record;
    }
}
