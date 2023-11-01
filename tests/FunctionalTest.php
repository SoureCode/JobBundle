<?php

namespace SoureCode\Bundle\Job\Tests;

use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\Job\Manager\JobManager;
use SoureCode\Bundle\Job\Tests\app\src\Entity\Bug;
use SoureCode\Bundle\Job\Tests\app\src\Job\FastJob;
use SoureCode\Bundle\Job\Tests\app\src\Job\WaitJob;

class FunctionalTest extends AbstractBaseFunctionalTestCase
{
    public function testWaitJobDispatchAndExecution(): void
    {
        $container = self::getContainer();
        /**
         * @var JobManager $jobManager
         */
        $jobManager = $container->get(JobManager::class);
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $entity = new Bug();
        $entity->setDescription('Yeet');
        $entity->setCreated(new \DateTime());
        $entity->setStatus('pending');

        $entityManager->persist($entity);
        $entityManager->flush();

        $job = $jobManager->create($entity, new WaitJob());

        self::assertTrue($job->isPending());

        $now = new \DateTime();
        $jobManager->dispatch($job);
        $after = new \DateTime();
        $diff = $after->getTimestamp() - $now->getTimestamp();

        self::assertLessThan(1, $diff);

        sleep(1);

        $entityManager->refresh($job);

        self::assertNull($job->getError());
        self::assertNull($job->getResult());

        self::assertNull($job->getFinishedAt());
        self::assertFalse($job->isFinished());

        self::assertNotNull($job->getStartedAt());
        self::assertTrue($job->isRunning());

        self::assertNull($job->getFailedAt());
        self::assertFalse($job->isFailed());

        self::assertNull($job->getCancelledAt());
        self::assertFalse($job->isCancelled());

        sleep(2);

        $entityManager->refresh($job);

        self::assertNull($job->getError());
        self::assertNull($job->getResult());

        self::assertNotNull($job->getFinishedAt());
        self::assertTrue($job->isFinished());

        self::assertNotNull($job->getStartedAt());
        self::assertFalse($job->isRunning());

        self::assertNull($job->getFailedAt());
        self::assertFalse($job->isFailed());

        self::assertNull($job->getCancelledAt());
        self::assertFalse($job->isCancelled());
    }

    public function testFastJobDispatchExecutionAndResult(): void
    {
        $container = self::getContainer();
        /**
         * @var JobManager $jobManager
         */
        $jobManager = $container->get(JobManager::class);
        /**
         * @var EntityManagerInterface $entityManager
         */
        $entityManager = $container->get(EntityManagerInterface::class);

        $entity = new Bug();
        $entity->setDescription('Yeet');
        $entity->setCreated(new \DateTime());
        $entity->setStatus('pending');

        $entityManager->persist($entity);
        $entityManager->flush();

        $job = $jobManager->create($entity, new FastJob("yeet"));

        $now = new \DateTime();
        $jobManager->dispatch($job);
        $after = new \DateTime();
        $diff = $after->getTimestamp() - $now->getTimestamp();

        self::assertLessThan(1, $diff);

        sleep(1);

        $entityManager->refresh($job);

        self::assertNull($job->getError());
        self::assertSame("yeetbaz", $job->getResult());
        self::assertNotNull($job->getFinishedAt());
        self::assertNotNull($job->getStartedAt());
        self::assertNull($job->getFailedAt());
        self::assertNull($job->getCancelledAt());
    }
}
