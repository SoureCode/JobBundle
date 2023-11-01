<?php

namespace SoureCode\Bundle\Job\Tests;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use SoureCode\Bundle\Job\Entity\Job;
use SoureCode\Bundle\Job\Tests\app\src\Entity\Bug;

abstract class AbstractBaseFunctionalTestCase extends AbstractBaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $container = self::getContainer();
        $entityManager = $container->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->updateSchema([
            $entityManager->getClassMetadata(Bug::class),
            $entityManager->getClassMetadata(Job::class),
        ]);
    }

    protected function tearDown(): void
    {
        $container = self::getContainer();

        $entityManager = $container->get(EntityManagerInterface::class);

        $schemaTool = new SchemaTool($entityManager);
        $schemaTool->dropDatabase();

        parent::tearDown();
    }
}
