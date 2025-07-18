<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Tests\Functional\Fixtures\Service;

use Sentry\Event;
use Sentry\Transport\Result;
use Sentry\Transport\ResultStatus;
use Sentry\Transport\TransportInterface;

class TestTransport implements TransportInterface
{
    private $savedEvents = [];
    private $events = [];

    public function send(Event $event): Result
    {
        $this->savedEvents[] = $event;
        return new Result(ResultStatus::success());
    }

    public function close(?int $timeout = null): Result
    {
        $this->events = $this->savedEvents;
        return new Result(ResultStatus::success());
    }

    public function getEvents(): array
    {
        return $this->events;
    }
}
