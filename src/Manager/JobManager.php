<?php

namespace SoureCode\Bundle\Job\Manager;

use LogicException;
use Psr\Clock\ClockInterface;
use SoureCode\Bundle\Job\Entity\Job;
use SoureCode\Bundle\Job\Job\Serializer;
use SoureCode\Bundle\Job\Repository\JobRepository;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\Process\PhpExecutableFinder;
use Symfony\Component\Process\Process;

class JobManager
{
    public function __construct(
        private readonly JobRepository          $jobRepository,
        private readonly Serializer             $serializer,
        private readonly ClockInterface         $clock,
        private readonly string                 $projectDirectory,
    )
    {
    }

    public function create(string|object $entityOrIdentity, object $payload): Job
    {
        $encodedPayload = $this->serializer->encode($payload);

        $job = new Job();

        $identity = $this->jobRepository->createIdentity($entityOrIdentity, $payload);

        $job->setIdentity($identity);
        $job->setCreatedAt($this->clock->now());
        $job->setEncodedPayload($encodedPayload);

        $this->jobRepository->save($job);

        return $job;
    }


    /**
     * @param string|null $argument
     * @return string
     * @copyright From package symfony/process in class \Symfony\Component\Process\Process
     */
    private function escapeArgument(?string $argument): string
    {
        if ('' === $argument || null === $argument) {
            return '""';
        }
        if ('\\' !== \DIRECTORY_SEPARATOR) {
            return "'" . str_replace("'", "'\\''", $argument) . "'";
        }
        if (str_contains($argument, "\0")) {
            $argument = str_replace("\0", '?', $argument);
        }
        if (!preg_match('/[\/()%!^"<>&|\s]/', $argument)) {
            return $argument;
        }
        $argument = preg_replace('/(\\\\+)$/', '$1$1', $argument);

        return '"' . str_replace(['"', '^', '%', '!', "\n"], ['""', '"^^"', '"^%"', '"^!"', '!LF!'], $argument) . '"';
    }

    public function dispatch(string|object $entityOrIdentity, object $payload): Job
    {
        $pendingJobs = $this->jobRepository->getPending($entityOrIdentity, $payload);

        if (count($pendingJobs) > 0) {
            throw new LogicException("Job with identity already queued.");
        }

        $job = $this->create($entityOrIdentity, $payload);

        $command = [
            ...$this->getPhpBinary(),
            Path::join($this->projectDirectory, 'bin', 'console'),
            'job:run',
            $job->getId(),
        ];

        $command = array_map($this->escapeArgument(...), $command);
        $command = implode(" ", $command);

        // stdin: null, stdout: null, stderr: null and run in background
        $command .= " </dev/null 1>/dev/null 2>/dev/null &";

        $process = Process::fromShellCommandline(
            $command,
            cwd: $this->projectDirectory,
            env: null,
            input: null,
            timeout: 0,
        );

        // $process->disableOutput();
        $process->start();

        return $job;
    }

    protected function getPhpBinary(): ?array
    {
        $executableFinder = new PhpExecutableFinder();
        $php = $executableFinder->find(false);

        if (false === $php) {
            return null;
        }

        return array_merge([$php], $executableFinder->findArguments());
    }

    public function get(int $id): Job
    {
        $job = $this->jobRepository->find($id);

        if (!$job) {
            throw new \InvalidArgumentException(sprintf('Job with id "%s" not found.', $id));
        }

        return $job;
    }

}