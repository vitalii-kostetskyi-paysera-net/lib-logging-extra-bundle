<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use InvalidArgumentException;
use Monolog\Processor\ProcessorInterface;

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
     * @param array|\Monolog\LogRecord $record
     * @return array|\Monolog\LogRecord
     */
    public function __invoke($record)
    {
        // Handle both Monolog v2 (array) and v3 (LogRecord)
        // Check if it's a LogRecord without importing the class (Monolog v3+)
        if (is_object($record) && get_class($record) === 'Monolog\LogRecord') {
            $message = str_replace($this->rootPrefix, '<root>', $record->message);
            // Create new LogRecord with modified message
            $logRecordClass = get_class($record);
            return new $logRecordClass(
                $record->datetime,
                $record->channel,
                $record->level,
                $message,
                $record->context,
                $record->extra,
                $record->formatted
            );
        }

        // Monolog v1/v2 array handling
        $record['message'] = str_replace($this->rootPrefix, '<root>', $record['message']);
        return $record;
    }
}
