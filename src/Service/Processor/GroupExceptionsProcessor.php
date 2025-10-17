<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Service\Processor;

use Monolog\Processor\ProcessorInterface;
use Sentry\SentrySdk;
use Sentry\State\Scope;

/**
 * @php-cs-fixer-ignore Paysera/php_basic_code_style_chained_method_calls
 */
class GroupExceptionsProcessor implements ProcessorInterface
{
    private $exceptionsClassesToGroup;

    public function __construct(array $exceptionsClassesToGroup)
    {
        $this->exceptionsClassesToGroup = array_flip($exceptionsClassesToGroup);
    }

    /**
     * @param array|\Monolog\LogRecord $record
     * @return array|\Monolog\LogRecord
     */
    public function __invoke($record)
    {
        // Get context from LogRecord or array
        // Check if it's a LogRecord without importing the class (Monolog v3+)
        $isLogRecord = is_object($record) && get_class($record) === 'Monolog\LogRecord';
        $context = $isLogRecord ? $record->context : $record['context'];

        if (!isset($context['exception'])) {
            return $record;
        }

        $exception = $context['exception'];
        $exceptionClass = get_class($exception);

        if (isset($this->exceptionsClassesToGroup[$exceptionClass])) {
            SentrySdk::getCurrentHub()
                ->configureScope(function (Scope $scope) use ($exceptionClass) {
                    $scope->setFingerprint([$exceptionClass]);
                })
            ;
        }

        return $record;
    }
}
