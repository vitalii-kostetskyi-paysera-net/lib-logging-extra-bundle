<?php

declare(strict_types=1);

namespace Paysera\LoggingExtraBundle\Listener;

use Paysera\LoggingExtraBundle\Service\CorrelationIdProvider;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent as LegacyResponseEvent;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

if (class_exists(ResponseEvent::class)) {
    class_alias(ResponseEvent::class, 'Paysera\LoggingExtraBundle\Listener\ResponseEventAlias');
} else {
    class_alias(LegacyResponseEvent::class, 'Paysera\LoggingExtraBundle\Listener\ResponseEventAlias');
}

class CorrelationIdListener
{
    public const HEADER_NAME = 'Paysera-Correlation-Id';

    private CorrelationIdProvider $correlationIdProvider;

    public function __construct(CorrelationIdProvider $correlationIdProvider)
    {
        $this->correlationIdProvider = $correlationIdProvider;
    }

    public function onKernelResponse(ResponseEventAlias $event): void
    {
        $mainRequestType = defined(HttpKernelInterface::class.'::MAIN_REQUEST')
            ? HttpKernelInterface::MAIN_REQUEST   // Symfony 5.3+
            : HttpKernelInterface::MASTER_REQUEST; // Symfony <=5.2

        if ($mainRequestType !== $event->getRequestType()) {
            return;
        }

        $event->getResponse()->headers->set(
            self::HEADER_NAME,
            $this->correlationIdProvider->getCorrelationId()
        );
    }
}
