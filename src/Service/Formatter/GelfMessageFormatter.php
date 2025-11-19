<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

use Gelf\Message;
use Monolog\Logger;
use Monolog\Formatter\GelfMessageFormatter as MonologGelfMessageFormatter;

/**
 * Custom GELF formatter that extends Monolog's GelfMessageFormatter
 *
 * Provides compatibility layer for different combinations of gelf-php and Monolog versions:
 * - gelf-php v1.x (has setFacility) works with all Monolog versions - use default
 * - gelf-php v2.x with Monolog v2+ (API >= 2) has native support - use default
 * - gelf-php v2.0.1 with Monolog v1 (API = 1) needs custom implementation - override format()
 *
 * Note: gelf-php v2.0.2+ added conflict with Monolog v1, but v2.0.1 doesn't have it.
 */
if (method_exists('Gelf\Message', 'setFacility') || (defined('Monolog\Logger::API') && constant('Monolog\Logger::API') >= 2)) {
    // gelf-php v1.x OR Monolog v2+ (API >= 2) - use default Monolog formatter
    class GelfMessageFormatter extends MonologGelfMessageFormatter
    {
        use FormatterTrait;
        use NormalizeCompatibilityTrait;
    }
} else {
    // gelf-php v2.0.1 with Monolog v1 (API = 1) - override to use setAdditional() instead of removed methods
    class GelfMessageFormatter extends MonologGelfMessageFormatter
    {
        use FormatterTrait;
        use NormalizeCompatibilityTrait;

        /**
         * Translates Monolog log levels to Graylog2 log priorities.
         */
        private $logLevels = [
            Logger::DEBUG     => 7,
            Logger::INFO      => 6,
            Logger::NOTICE    => 5,
            Logger::WARNING   => 4,
            Logger::ERROR     => 3,
            Logger::CRITICAL  => 2,
            Logger::ALERT     => 1,
            Logger::EMERGENCY => 0,
        ];

        /**
         * {@inheritdoc}
         *
         * Override to support gelf-php ^2.0 with Monolog v1 which removed setFacility(), setLine(), and setFile() methods
         */
        public function format(array $record)
        {
            $record = parent::normalize($record);

            if (!isset($record['datetime'], $record['message'], $record['level'])) {
                throw new \InvalidArgumentException('The record should at least contain datetime, message and level keys, '.var_export($record, true).' given');
            }

            $message = new Message();

            // Convert datetime to float for gelf-php v2.0 strict typing
            $timestamp = $record['datetime'];
            if (is_string($timestamp)) {
                // Monolog v1 normalizes to string format 'U.u'
                $timestamp = (float) $timestamp;
            } elseif ($timestamp instanceof \DateTimeInterface) {
                $timestamp = (float) $timestamp->format('U.u');
            }

            $message
                ->setTimestamp($timestamp)
                ->setShortMessage((string) $record['message'])
                ->setHost($this->systemName)
                ->setLevel($this->logLevels[$record['level']]);

            // message length + system name length + 200 for padding / metadata
            $len = 200 + strlen((string) $record['message']) + strlen($this->systemName);

            if ($len > $this->maxLength) {
                $message->setShortMessage(substr($record['message'], 0, $this->maxLength));
            }

            // In gelf-php v2.0, setFacility() was removed, use setAdditional('facility') instead
            if (isset($record['channel'])) {
                $message->setAdditional('facility', $record['channel']);
            }

            // In gelf-php v2.0, setLine() was removed, use setAdditional('line') instead
            if (isset($record['extra']['line'])) {
                $message->setAdditional('line', $record['extra']['line']);
                unset($record['extra']['line']);
            }

            // In gelf-php v2.0, setFile() was removed, use setAdditional('file') instead
            if (isset($record['extra']['file'])) {
                $message->setAdditional('file', $record['extra']['file']);
                unset($record['extra']['file']);
            }

            foreach ($record['extra'] as $key => $val) {
                $val = is_scalar($val) || null === $val ? $val : $this->toJson($val);
                $len = strlen($this->extraPrefix . $key . $val);
                if ($len > $this->maxLength) {
                    $message->setAdditional($this->extraPrefix . $key, substr($val, 0, $this->maxLength));
                    break;
                }
                $message->setAdditional($this->extraPrefix . $key, $val);
            }

            foreach ($record['context'] as $key => $val) {
                $val = is_scalar($val) || null === $val ? $val : $this->toJson($val);
                $len = strlen($this->contextPrefix . $key . $val);
                if ($len > $this->maxLength) {
                    $message->setAdditional($this->contextPrefix . $key, substr($val, 0, $this->maxLength));
                    break;
                }
                $message->setAdditional($this->contextPrefix . $key, $val);
            }

            if (!$message->hasAdditional('file') && isset($record['context']['exception']['file'])) {
                if (preg_match("/^(.+):([0-9]+)$/", $record['context']['exception']['file'], $matches)) {
                    $message->setAdditional('file', $matches[1]);
                    $message->setAdditional('line', (int)$matches[2]);
                }
            }

            return $message;
        }
    }
}
