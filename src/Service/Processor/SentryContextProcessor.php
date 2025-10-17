<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use Monolog\Processor\ProcessorInterface;

class SentryContextProcessor implements ProcessorInterface
{
    /**
     * @param array|\Monolog\LogRecord $record
     * @return array|\Monolog\LogRecord
     */
    public function __invoke($record)
    {
        // Handle both Monolog v2 (array) and v3 (LogRecord)
        // Check if it's a LogRecord without importing the class (Monolog v3+)
        if (is_object($record) && get_class($record) === 'Monolog\LogRecord') {
            $recordArray = $record->toArray();
            $recordArray['context']['extra'] = ($recordArray['context']['extra'] ?? []) + $recordArray['extra'] + $recordArray['context'];
            if (isset($recordArray['extra']['correlation_id'])) {
                $recordArray['context']['tags']['correlation_id'] = $recordArray['extra']['correlation_id'];
            }
            unset($recordArray['context']['extra']['tags']);
            unset($recordArray['context']['extra']['exception']);
            // Create new LogRecord with modified context
            $logRecordClass = get_class($record);
            return new $logRecordClass(
                $record->datetime,
                $record->channel,
                $record->level,
                $record->message,
                $recordArray['context'],
                $record->extra,
                $record->formatted
            );
        }

        // Monolog v1/v2 array handling
        $record['context']['extra'] = ($record['context']['extra'] ?? []) + $record['extra'] + $record['context'];
        if (isset($record['extra']['correlation_id'])) {
            $record['context']['tags']['correlation_id'] = $record['extra']['correlation_id'];
        }
        unset($record['context']['extra']['tags']);
        unset($record['context']['extra']['exception']);
        return $record;
    }
}
