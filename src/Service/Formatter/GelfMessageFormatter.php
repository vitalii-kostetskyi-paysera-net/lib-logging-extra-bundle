<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Formatter;

use Gelf\Message;
use Monolog\Logger;
use Monolog\Formatter\GelfMessageFormatter as MonologGelfMessageFormatter;

/**
 * GELF formatter with gelf-php v2.0 compatibility for Monolog v1
 */
if (method_exists('Gelf\Message', 'setFacility') || (defined('Monolog\Logger::API') && constant('Monolog\Logger::API') >= 2)) {
    class GelfMessageFormatter extends MonologGelfMessageFormatter
    {
        use NormalizeCompatibilityTrait;
    }
} else {
    class GelfMessageFormatter extends MonologGelfMessageFormatter
    {
        use NormalizeCompatibilityTrait;

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

        public function format(array $record)
        {
            $record = parent::normalize($record);

            if (!isset($record['datetime'], $record['message'], $record['level'])) {
                throw new \InvalidArgumentException('The record should at least contain datetime, message and level keys, '.var_export($record, true).' given');
            }

            $message = new Message();

            $timestamp = $this->convertTimestamp($record['datetime']);

            $message
                ->setTimestamp($timestamp)
                ->setShortMessage((string) $record['message'])
                ->setHost($this->systemName)
                ->setLevel($this->logLevels[$record['level']]);

            $len = 200 + strlen((string) $record['message']) + strlen($this->systemName);

            if ($len > $this->maxLength) {
                $message->setShortMessage(substr($record['message'], 0, $this->maxLength));
            }

            $this->setFacilityLineAndFile($message, $record);
            $this->addExtraFields($message, $record['extra']);
            $this->addContextFields($message, $record['context']);

            if (!$message->hasAdditional('file') && isset($record['context']['exception']['file'])) {
                if (preg_match("/^(.+):([0-9]+)$/", $record['context']['exception']['file'], $matches)) {
                    $message->setAdditional('file', $matches[1]);
                    $message->setAdditional('line', (int)$matches[2]);
                }
            }

            return $message;
        }

        private function convertTimestamp($timestamp): float
        {
            if (is_string($timestamp)) {
                return (float) $timestamp;
            }

            if ($timestamp instanceof \DateTimeInterface) {
                return (float) $timestamp->format('U.u');
            }

            return (float) $timestamp;
        }

        private function setFacilityLineAndFile(Message $message, array $record): void
        {
            if (isset($record['channel'])) {
                $message->setAdditional('facility', $record['channel']);
            }

            if (isset($record['extra']['line'])) {
                $message->setAdditional('line', $record['extra']['line']);
                unset($record['extra']['line']);
            }

            if (isset($record['extra']['file'])) {
                $message->setAdditional('file', $record['extra']['file']);
                unset($record['extra']['file']);
            }
        }

        private function addExtraFields(Message $message, array $extra): void
        {
            foreach ($extra as $key => $val) {
                $val = is_scalar($val) || null === $val ? $val : $this->toJson($val);
                $len = strlen($this->extraPrefix . $key . $val);
                if ($len > $this->maxLength) {
                    $message->setAdditional($this->extraPrefix . $key, substr($val, 0, $this->maxLength));
                    break;
                }
                $message->setAdditional($this->extraPrefix . $key, $val);
            }
        }

        private function addContextFields(Message $message, array $context): void
        {
            foreach ($context as $key => $val) {
                $val = is_scalar($val) || null === $val ? $val : $this->toJson($val);
                $len = strlen($this->contextPrefix . $key . $val);
                if ($len > $this->maxLength) {
                    $message->setAdditional($this->contextPrefix . $key, substr($val, 0, $this->maxLength));
                    break;
                }
                $message->setAdditional($this->contextPrefix . $key, $val);
            }
        }
    }
}
