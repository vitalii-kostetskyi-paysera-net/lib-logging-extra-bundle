<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Tests\Functional\Fixtures\Handler;

use Monolog\Handler\GelfHandler;

/**
 * @php-cs-fixer-ignore Paysera/php_basic_code_style_default_values_in_constructor
 */
class TestGraylogHandler extends GelfHandler
{
    private $publishedMessages = [];

    /**
     * @param array|\Monolog\LogRecord $record
     */
    protected function write($record): void
    {
        // Convert LogRecord to array for Monolog v3 compatibility
        // Check if it's a LogRecord without importing the class (Monolog v3+)
        if (is_object($record) && get_class($record) === 'Monolog\LogRecord') {
            // In Monolog v3, GelfHandler formats the record into a GELF message
            // The parent's write() method expects us to handle the LogRecord
            // We need to call the parent's format method to get the GELF message
            $message = $this->getFormatter()->format($record);
            $this->publishedMessages[] = $message;
        } else {
            // Monolog v1/v2 array handling
            $this->publishedMessages[] = $record['formatted'];
        }
    }

    public function flushPublishedMessages()
    {
        $messages = $this->publishedMessages;
        $this->publishedMessages = [];
        return $messages;
    }
}
