<?php

namespace SoureCode\Bundle\Job;

use Doctrine\ORM\EntityManagerInterface;
use SoureCode\Bundle\Job\Attribute\AsJobHandler;
use SoureCode\Bundle\Job\Command\JobRunCommand;
use SoureCode\Bundle\Job\DependencyInjection\JobCompilerPass;
use SoureCode\Bundle\Job\Job\JobHandlerLocator;
use SoureCode\Bundle\Job\Job\Runner;
use SoureCode\Bundle\Job\Job\Serializer;
use SoureCode\Bundle\Job\Manager\JobManager;
use SoureCode\Bundle\Job\Repository\JobRepository;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\abstract_arg;
use function Symfony\Component\DependencyInjection\Loader\Configurator\param;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class SoureCodeJobBundle extends AbstractBundle
{
    public function configure(DefinitionConfigurator $definition): void
    {
        // @formatter:off
        $definition->rootNode()
            ->children()
            ->end();
        // @formatter:on
    }

    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $services = $container->services();

        $services->set('soure_code.job.repository.job', JobRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.repository_service');

        $services
            ->alias(JobRepository::class, 'soure_code.job.repository.job')
            ->public();

        $services->set('soure_code.job.manager', JobManager::class)
            ->args([
                service('soure_code.job.repository.job'),
                service('soure_code.job.serializer'),
                service('clock'),
                param('kernel.project_dir'),
            ]);

        $services->alias(JobManager::class, 'soure_code.job.manager')
            ->public();

        $services->set('soure_code.job.command.job.run', JobRunCommand::class)
            ->args([
                service('soure_code.job.manager'),
                service('soure_code.job.runner'),
            ])
            ->public()
            ->tag('console.command', ['command' => 'job:run']);

        $services->set('soure_code.job.serializer', Serializer::class)
            ->args([
                service('logger'),
                service('serializer'),
            ]);

        $services->set('soure_code.job.executor', JobHandlerLocator::class)
            ->args([
                abstract_arg('handlers'),
                service('soure_code.job.serializer'),
            ]);

        $services->set('soure_code.job.runner', Runner::class)
            ->args([
                service('soure_code.job.repository.job'),
                service('soure_code.job.executor'),
                service('soure_code.job.serializer'),
                service('clock'),
            ]);

        $builder->registerAttributeForAutoconfiguration(AsJobHandler::class,
            static function (ChildDefinition $definition, AsJobHandler $attribute): void {
                $definition->addTag('soure_code.job_handler', [
                    'handle' => $attribute->handle,
                ]);
            });
    }

    public function build(ContainerBuilder $container): void
    {
        $container->addCompilerPass(new JobCompilerPass());
    }

}