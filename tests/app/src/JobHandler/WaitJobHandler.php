<?php

namespace SoureCode\Bundle\Job\Tests\app\src\JobHandler;

use Override;
use SoureCode\Bundle\Job\Attribute\AsJobHandler;
use SoureCode\Bundle\Job\Job\JobHandlerInterface;
use SoureCode\Bundle\Job\Tests\app\src\Job\WaitJob;

#[AsJobHandler(handle: WaitJob::class)]
/**
 * @implements JobHandlerInterface<WaitJob>
 */
class WaitJobHandler implements JobHandlerInterface
{
    public function handle($payload): void
    {
        sleep($payload->seconds);
    }
}