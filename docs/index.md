
# JobBundle

## Requirements

- PHP 8.2 or higher
- Symfony 6.3 or higher

## Commands

- [`job:run`](./job-run.md) - Runs a job.

## Examples

```php
use SoureCode\Bundle\Job\Manager\JobManager;

$jobManager = $container->get(JobManager::class);

// run a job bound to given entity
$jobManager->dispatch($entity, new MakeJob());

// Run a job bound to a string key
$jobManager->dispatch("test", new DoJob());
```

## Installation

Make sure Composer is installed globally, as explained in the
[installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.

### Applications that use Symfony Flex

Open a command console, enter your project directory and execute:

```console
composer require sourecode/job-bundle
```

### Applications that don't use Symfony Flex

#### Step 1: Download the Bundle

Open a command console, enter your project directory and execute the
following command to download the latest stable version of this bundle:

```console
composer require sourecode/job-bundle
```

#### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    \SoureCode\Bundle\Job\SoureCodeJobBundle::class => ['all' => true],
];
```