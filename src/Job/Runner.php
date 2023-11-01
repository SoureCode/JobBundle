<?php

namespace SoureCode\Bundle\Job\Job;

use LogicException;
use Psr\Clock\ClockInterface;
use SoureCode\Bundle\Job\Entity\Job;
use SoureCode\Bundle\Job\Repository\JobRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\ErrorHandler\Exception\FlattenException;

class Runner
{
    private bool $canceled = false;
    private ?JobHandlerInterface $handler = null;
    private ?object $payloadBody = null;

    public function __construct(
        private readonly JobRepository     $jobRepository,
        private readonly JobHandlerLocator $handlerLocator,
        private readonly Serializer        $serializer,
        private readonly ClockInterface    $clock,
    )
    {
    }

    public function run(Job $job): int
    {
        $pendingJobs = $this->jobRepository->getPendingByJob($job);

        if (count($pendingJobs) > 0) {
            throw new LogicException("Job with identity already queued.");
        }

        $result = null;

        try {
            $this->loadPayload($job);

            $result = $this->execute($job);

            return Command::SUCCESS;
        } catch
        (\Throwable $exception) {
            $this->fail($job, $exception);

            return Command::FAILURE;
        } finally {
            if ($this->canceled) {
                $this->cancel($job);
            }

            $this->finish($job, $result);
        }
    }

    public function signal(int $signal): false|int
    {
        if ($this->handler instanceof SignalableJobHandlerInterface) {
            $this->handler->handleSignal($signal);
        }

        $this->canceled = true;

        return false;
    }

    private function execute(Job $job): mixed
    {
        $job->setStartedAt($this->clock->now());

        $this->jobRepository->save($job);

        return $this->handler->handle($this->payloadBody);
    }

    private function cancel(Job $job): void
    {
        $job->setCancelledAt($this->clock->now());

        $this->jobRepository->save($job);
    }

    private function fail(Job $job, \Throwable $exception): void
    {
        $job->setFailedAt($this->clock->now());
        $job->setError(FlattenException::createFromThrowable($exception)->getAsString());

        $this->jobRepository->save($job);
    }

    private function finish(Job $job, mixed $result): void
    {
        $job->setFinishedAt($this->clock->now());
        $job->setResult($result);

        $this->jobRepository->save($job);
    }

    private function loadPayload(Job $job): void
    {
        $encodedPayload = $job->getEncodedPayload();

        $payload = $this->serializer->decode($encodedPayload);

        $this->payloadBody = $payload->getBody();
        $this->handler = $this->handlerLocator->findHandler($payload->getType());
    }
}