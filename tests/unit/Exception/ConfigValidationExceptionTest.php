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

namespace Aeliot\TodoRegistrar\Test\Unit\Exception;

use Aeliot\TodoRegistrar\Exception\ConfigValidationException;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;

#[CoversClass(ConfigValidationException::class)]
final class ConfigValidationExceptionTest extends TestCase
{
    public function testDefaultMessage(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new ConfigValidationException($violations);

        self::assertSame('Invalid issue configuration', $exception->getMessage());
    }

    public function testCustomConfigType(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new ConfigValidationException($violations, '[GitHub] Invalid general issue config');

        self::assertSame('Invalid [GitHub] Invalid general issue config configuration', $exception->getMessage());
    }

    public function testGetViolations(): void
    {
        $violation = new ConstraintViolation(
            'Test error message',
            null,
            [],
            null,
            'propertyPath',
            null
        );
        $violations = new ConstraintViolationList([$violation]);
        $exception = new ConfigValidationException($violations);

        self::assertSame($violations, $exception->getViolations());
    }

    public function testGetErrorMessages(): void
    {
        $violation1 = new ConstraintViolation('Error 1', null, [], null, 'path1', null);
        $violation2 = new ConstraintViolation('Error 2', null, [], null, 'path2', null);
        $violations = new ConstraintViolationList([$violation1, $violation2]);
        $exception = new ConfigValidationException($violations);

        $messages = $exception->getErrorMessages();

        self::assertCount(2, $messages);
        self::assertSame(['Error 1', 'Error 2'], $messages);
    }

    public function testGetErrorMessagesEmpty(): void
    {
        $violations = new ConstraintViolationList();
        $exception = new ConfigValidationException($violations);

        self::assertSame([], $exception->getErrorMessages());
    }
}
