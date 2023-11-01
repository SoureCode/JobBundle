<?php

namespace SoureCode\Bundle\Job\Tests;

use Doctrine\Bundle\DoctrineBundle\DoctrineBundle;
use Nyholm\BundleTest\TestKernel;
use SoureCode\Bundle\Job\SoureCodeJobBundle;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;
use Symfony\Component\Filesystem\Path;
use Symfony\Component\HttpKernel\KernelInterface;

abstract class AbstractBaseTestCase extends KernelTestCase
{
    protected static function getKernelClass(): string
    {
        return TestKernel::class;
    }

    protected static function createKernel(array $options = []): KernelInterface
    {
        /**
         * @var TestKernel $kernel
         */
        $kernel = parent::createKernel($options);
        $kernel->addTestBundle(DoctrineBundle::class);
        $kernel->addTestBundle(SoureCodeJobBundle::class);
        $kernel->setTestProjectDir(Path::join(__DIR__, 'app'));
        $kernel->addTestConfig(Path::join($kernel->getProjectDir(), 'config', 'config.yaml'));
        $kernel->handleOptions($options);

        return $kernel;
    }
}