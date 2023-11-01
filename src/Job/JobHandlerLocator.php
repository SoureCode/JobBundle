<?php

namespace SoureCode\Bundle\Job\Job;

class JobHandlerLocator
{
    public function __construct(
        /**
         * @var HandlerDescriptor[] $handlers
         */
        private readonly iterable   $handlers,
    )
    {
    }

    public function findHandler(string $payloadClassName): JobHandlerInterface
    {
        foreach ($this->handlers as $handler) {
            if ($handler->getPayloadClassName() === $payloadClassName) {
                return $handler->getHandler();
            }
        }

        throw new \RuntimeException(sprintf('No handler found for payload class "%s".', $payloadClassName));
    }
}