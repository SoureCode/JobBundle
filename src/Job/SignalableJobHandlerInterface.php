<?php

namespace SoureCode\Bundle\Job\Job;

interface SignalableJobHandlerInterface
{
    public function handleSignal(int $signal): void;
}