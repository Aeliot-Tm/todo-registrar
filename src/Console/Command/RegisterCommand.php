<?php

declare(strict_types=1);

/*
 * This file is part of the TODO Registrar project.
 *
 * (c) Anatoliy Melnikov <5785276@gmail.com>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Aeliot\TodoRegistrar\Console\Command;

use Aeliot\TodoRegistrar\Console\OutputAdapter;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\HeapRunnerFactory;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * @internal
 */
#[AsCommand(name: 'register', description: 'Register TODOs from source code in issue tracker')]
final class RegisterCommand extends Command
{
    public function __construct(private readonly HeapRunnerFactory $heapRunnerFactory)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to configuration file');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');
        $outputAdapter = new OutputAdapter($output);

        try {
            $statistic = $this->heapRunnerFactory->create($configPath, $outputAdapter)->run();
        } catch (ConfigValidationException $exception) {
            $outputAdapter->writeErr("[ERROR] {$exception->getMessage()}\n");
            $outputAdapter->writeErr("Validation errors:\n");

            foreach ($exception->getErrorMessages() as $errorMessage) {
                $outputAdapter->writeErr("  - {$errorMessage}\n");
            }

            return self::FAILURE;
        }

        $outputAdapter->writeln(
            "Registered {$statistic->getCountRegisteredTODOs()} for {$statistic->getCountUpdatedFiles()} files",
            OutputAdapter::VERBOSITY_NORMAL
        );

        return self::SUCCESS;
    }
}
