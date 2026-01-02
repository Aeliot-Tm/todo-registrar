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

namespace Aeliot\TodoRegistrar\Exception;

use Aeliot\TodoRegistrarContracts\Exception\InvalidConfigException as InvalidConfigExceptionInterface;
use Symfony\Component\Validator\ConstraintViolationListInterface;

final class ConfigValidationException extends \RuntimeException implements InvalidConfigExceptionInterface
{
    public function __construct(
        private readonly ConstraintViolationListInterface $violations,
        string $configType = 'issue',
    ) {
        parent::__construct(\sprintf('Invalid %s configuration', $configType));
    }

    public function getViolations(): ConstraintViolationListInterface
    {
        return $this->violations;
    }

    /**
     * @return string[]
     */
    public function getErrorMessages(): array
    {
        $messages = [];
        foreach ($this->violations as $violation) {
            $messages[] = (string) $violation->getMessage();
        }

        return $messages;
    }
}
