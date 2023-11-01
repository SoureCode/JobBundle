<?php

namespace SoureCode\Bundle\Job\DependencyInjection;

use ReflectionClass;
use RuntimeException;
use SoureCode\Bundle\Job\Job\HandlerDescriptor;
use SoureCode\Bundle\Job\Job\JobHandlerInterface;
use Symfony\Component\DependencyInjection\Argument\IteratorArgument;
use Symfony\Component\DependencyInjection\ChildDefinition;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;

class JobCompilerPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        $jobHandlers = $container->findTaggedServiceIds('soure_code.job_handler', true);

        $mapping = [];

        foreach ($jobHandlers as $serviceId => $tags) {
            $className = $this->getServiceClass($container, $serviceId);
            $classReflection = $this->getClassReflection($container, $className, $serviceId);

            if (!$classReflection->implementsInterface(JobHandlerInterface::class)) {
                throw new RuntimeException(sprintf('Invalid handler service "%s": class "%s" does not implement "%s".', $serviceId, $className, JobHandlerInterface::class));
            }

            $payloadClassName = $this->getPayloadClassName($serviceId, $tags);

            if (array_key_exists($payloadClassName, $mapping)) {
                throw new RuntimeException(sprintf('Handler "%s" is already registered for message "%s".', $mapping[$className], $className));
            }

            $mapping[$payloadClassName] = $className;
        }

        $references = [];

        foreach ($mapping as $jobClass => $handlerId) {
            $serviceId = $this->createDescriptorService($container, $jobClass, $handlerId);
            $references[] = new Reference($serviceId);
        }

        $definition = $container->getDefinition('soure_code.job.executor');

        $definition->setArgument(0, new IteratorArgument($references));
    }

    private function getServiceClass(ContainerBuilder $container, string $serviceId): string
    {
        while (true) {
            $definition = $container->findDefinition($serviceId);

            if (!$definition->getClass() && $definition instanceof ChildDefinition) {
                $serviceId = $definition->getParent();

                continue;
            }

            return $definition->getClass();
        }
    }

    private function createDescriptorService(
        ContainerBuilder $containerBuilder,
        string           $jobClass,
        string           $handlerId,
    ): string
    {
        $definition = new Definition(HandlerDescriptor::class);
        $definition
            ->addArgument(new Reference($handlerId))
            ->addArgument($jobClass);

        $definitionId = 'soure_code.job.descriptor.' . ContainerBuilder::hash($jobClass);

        $containerBuilder->setDefinition($definitionId, $definition);

        return $definitionId;
    }

    private function getPayloadClassName(string $serviceId, array $tags): string
    {
        $handle = null;

        foreach ($tags as $tag) {
            if (array_key_exists('handle', $tag)) {
                if ($handle !== null) {
                    throw new RuntimeException(sprintf('Invalid handler service "%s": multiple handles are not allowed.', $serviceId));
                }

                $handle = $tag['handle'];
            }
        }

        return $handle;
    }

    private function getClassReflection(ContainerBuilder $container, string $className, int|string $serviceId): ReflectionClass
    {
        $classReflection = $container->getReflectionClass($className);

        if (null === $classReflection) {
            throw new RuntimeException(sprintf('Invalid handler service "%s": class "%s" does not exist.', $serviceId, $className));
        }

        return $classReflection;
    }
}