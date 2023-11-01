<?php

namespace SoureCode\Bundle\Job\Tests\app\src\JobHandler;

use SoureCode\Bundle\Job\Attribute\AsJobHandler;
use SoureCode\Bundle\Job\Job\JobHandlerInterface;
use SoureCode\Bundle\Job\Tests\app\src\Job\FastJob;

#[AsJobHandler(handle: FastJob::class)]
/**
 * @implements JobHandlerInterface<FastJob>
 */
class FastJobHandler implements JobHandlerInterface
{
    public function handle($payload): string
    {
        usleep(500);

        return $payload->foo . 'baz';
    }
}