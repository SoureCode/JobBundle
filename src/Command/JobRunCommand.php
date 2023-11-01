<?php

namespace SoureCode\Bundle\Job\Command;

use SoureCode\Bundle\Job\Job\Runner;
use SoureCode\Bundle\Job\Manager\JobManager;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Command\SignalableCommandInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

#[AsCommand(
    name: 'job:run',
    description: 'Run a job',
)]
final class JobRunCommand extends Command implements SignalableCommandInterface
{

    public function __construct(
        private readonly JobManager $jobManager,
        private readonly Runner     $jobRunner,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addArgument('id', InputArgument::REQUIRED, 'The job id');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $id = (int)$input->getArgument('id');
        $job = $this->jobManager->get($id);

        return $this->jobRunner->run($job);
    }

    public function getSubscribedSignals(): array
    {
        return [
            2, // SIGINT
            15, // SIGTERM
        ];
    }

    public function handleSignal(int $signal, int|false $previousExitCode = 0): false|int
    {
        return $this->jobRunner->signal($signal);
    }
}
