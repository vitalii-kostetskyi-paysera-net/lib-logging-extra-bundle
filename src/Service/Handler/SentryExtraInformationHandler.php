<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Handler;

use Monolog\Handler\HandlerWrapper;
use Monolog\Handler\ProcessableHandlerTrait;
use Sentry\State\Scope;
use function Sentry\withScope;

final class SentryExtraInformationHandler extends HandlerWrapper
{
    use ProcessableHandlerTrait;

    /**
     * @param array|\Monolog\LogRecord $record
     */
    public function handle($record): bool
    {
        // Check if handling - pass original record to parent
        if (!$this->isHandling($record)) {
            return false;
        }

        $result = false;

        // Process record - pass original record, it handles both
        $processedRecord = $this->processRecord($record);

        // Check if it's a LogRecord without importing the class (Monolog v3+)
        $isLogRecord = is_object($processedRecord) && get_class($processedRecord) === 'Monolog\LogRecord';
        $recordArray = $isLogRecord ? $processedRecord->toArray() : $processedRecord;

        // Format - formatter can handle both
        $recordArray['formatted'] = $this->getFormatter()->format($isLogRecord ? $processedRecord : $recordArray);

        withScope(function (Scope $scope) use ($recordArray, $processedRecord, &$result): void {
            if (isset($recordArray['context']['extra']) && is_array($recordArray['context']['extra'])) {
                foreach ($recordArray['context']['extra'] as $key => $value) {
                    $scope->setExtra((string) $key, $value);
                }
            }

            if (isset($recordArray['context']['tags']) && is_array($recordArray['context']['tags'])) {
                foreach ($recordArray['context']['tags'] as $key => $value) {
                    $scope->setTag($key, $value);
                }
            }

            $result = $this->handler->handle($processedRecord);
        });

        return $result;
    }
}
