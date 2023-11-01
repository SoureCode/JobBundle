<?php

namespace SoureCode\Bundle\Job\Tests;

use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\Job\Manager\JobManager;
use SoureCode\Bundle\Job\Tests\app\src\Entity\Bug;
use SoureCode\Bundle\Job\Tests\app\src\Job\WaitJob;

class CancelTest extends AbstractBaseFunctionalTestCase
{
    public function testCancel(): void
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

        $job = $jobManager->create($entity, new WaitJob(10));

        $now = new \DateTime();
        $jobManager->dispatch($job);
        $after = new \DateTime();
        $diff = $after->getTimestamp() - $now->getTimestamp();

        self::assertLessThan(1, $diff);

        sleep(2);

        $pid = $this->getProcessPid('job:run');
        exec('kill -s 2 ' . $pid);

        sleep(2);

        $entityManager->refresh($job);

        self::assertNull($job->getError());
        self::assertNull($job->getResult());
        self::assertNotNull($job->getFinishedAt());
        self::assertNotNull($job->getStartedAt());
        self::assertNull($job->getFailedAt());
        self::assertNotNull($job->getCancelledAt());
        self::assertTrue($job->isCancelled());
    }

    private function getProcesses(): array
    {
        $output = [];
        exec('ps -ax', $output);

        $processes = [];

        foreach ($output as $line) {
            $processes[] = $line;
        }

        return $processes;
    }

    private function findProcess(string $needle): ?string
    {
        $processes = $this->getProcesses();

        foreach ($processes as $process) {
            if (str_contains($process, $needle)) {
                return $process;
            }
        }

        return null;
    }

    private function getProcessPid(string $needle): ?int
    {
        $process = $this->findProcess($needle);

        if (null === $process) {
            return null;
        }

        $parts = preg_split('/\s+/', $process);
        $parts = array_filter($parts, static fn($value) => !empty($value));
        $parts = array_values($parts);

        return (int)$parts[0];
    }
}
