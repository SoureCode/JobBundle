<?php

namespace SoureCode\Bundle\Job\Tests;

use SoureCode\Bundle\Job\Manager\JobManager;
use SoureCode\Bundle\Job\Repository\JobRepository;

class BundleInitializationTest extends AbstractBaseTestCase
{
    public function testBundleInitialization(): void
    {
        $container = self::getContainer();

        self::assertTrue($container->has(JobManager::class));
        self::assertTrue($container->has(JobRepository::class));
    }
}
