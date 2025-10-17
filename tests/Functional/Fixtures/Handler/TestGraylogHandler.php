<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Tests\Functional\Fixtures\Handler;

use Monolog\Handler\GelfHandler;

/**
 * @php-cs-fixer-ignore Paysera/php_basic_code_style_default_values_in_constructor
 */
if (class_exists('Monolog\LogRecord')) {
    // Monolog v3+
    class TestGraylogHandler extends GelfHandler
    {
        private $publishedMessages = [];

        protected function write(\Monolog\LogRecord $record): void
        {
            $message = $this->getFormatter()->format($record);
            $this->publishedMessages[] = $message;
        }

        public function flushPublishedMessages()
        {
            $messages = $this->publishedMessages;
            $this->publishedMessages = [];
            return $messages;
        }
    }
} elseif (interface_exists('Monolog\Handler\ProcessableHandlerInterface')) {
    // Monolog v2
    class TestGraylogHandler extends GelfHandler
    {
        private $publishedMessages = [];

        protected function write(array $record): void
        {
            $message = $this->getFormatter()->format($record);
            $this->publishedMessages[] = $message;
        }

        public function flushPublishedMessages()
        {
            $messages = $this->publishedMessages;
            $this->publishedMessages = [];
            return $messages;
        }
    }
} else {
    // Monolog v1
    class TestGraylogHandler extends GelfHandler
    {
        private $publishedMessages = [];

        protected function write(array $record)
        {
            $message = $this->getFormatter()->format($record);
            $this->publishedMessages[] = $message;
        }

        public function flushPublishedMessages()
        {
            $messages = $this->publishedMessages;
            $this->publishedMessages = [];
            return $messages;
        }
    }
}
