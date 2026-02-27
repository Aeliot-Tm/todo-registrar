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
use Aeliot\TodoRegistrar\Enum\ReportFormat;
use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use Aeliot\TodoRegistrar\Service\HeapRunnerFactory;
use Aeliot\TodoRegistrar\Service\Report\ReportBuilder;
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
    public function __construct(
        private readonly ReportBuilder $awareReportFormatter,
        private readonly HeapRunnerFactory $heapRunnerFactory,
    ) {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->addOption('config', 'c', InputOption::VALUE_OPTIONAL, 'Path to configuration file');
        $this->addOption(
            'report-format',
            null,
            InputOption::VALUE_REQUIRED,
            'Export format: ' . implode(', ', ReportFormat::getValues()),
            ReportFormat::NONE->value
        );
        $this->addOption(
            'report-path',
            null,
            InputOption::VALUE_REQUIRED,
            'Report file path (default: todo-registrar-report.<format>). Use "-" for stdout'
        );
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $configPath = $input->getOption('config');
        $outputAdapter = new OutputAdapter($output);
        $reportFormat = $this->getReportFormat($input);
        if (null === $reportFormat) {
            $outputAdapter->writeErr('[ERROR] Invalid report format');

            return self::INVALID;
        }

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
            \sprintf(
                'Registered %d of %d TODOs for %d of %d files. Ignored: %d. Glued: %d.',
                $statistic->getCountRegisteredTODOs(),
                $statistic->getTodosTotal(),
                $statistic->getCountUpdatedFiles(),
                $statistic->getCountAnalyzedFiles(),
                $statistic->getCountIgnoredTodos(),
                $statistic->getCountGluedTodos(),
            ),
            OutputAdapter::VERBOSITY_NORMAL
        );

        if (!$reportFormat->isNone()) {
            $path = $this->getReportPath($input, $reportFormat);
            $content = $this->awareReportFormatter->format($reportFormat, $statistic);
            file_put_contents('-' === $path ? 'php://stdout' : $path, $content);
        }

        return self::SUCCESS;
    }

    private function getReportFormat(InputInterface $input): ?ReportFormat
    {
        $value = $input->getOption('report-format');

        return \is_string($value) ? ReportFormat::tryFrom($value) : null;
    }

    private function getReportPath(InputInterface $input, ReportFormat $format): string
    {
        $reportPath = $input->getOption('report-path');

        return \is_string($reportPath) ? $reportPath : \sprintf('todo-registrar-report.%s', $format->value);
    }
}
