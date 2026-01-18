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

namespace Aeliot\TodoRegistrar\Service\ContextPath;

use Aeliot\TodoRegistrar\Enum\ContextPathBuilderFormat;
use Aeliot\TodoRegistrar\Exception\InvalidConfigException;
use Symfony\Component\DependencyInjection\Attribute\AutowireLocator;
use Symfony\Component\DependencyInjection\ServiceLocator;

final readonly class ContextPathBuilderRegistry
{
    /**
     * @param ServiceLocator<ContextPathBuilderInterface> $contextPathBuilderLocator
     */
    public function __construct(
        #[AutowireLocator('aeliot.todo_registrar.context_path_builder')]
        private ServiceLocator $contextPathBuilderLocator,
    ) {
    }

    public function getBuilder(ContextPathBuilderFormat|string|true $format): ContextPathBuilderInterface
    {
        $format = $this->getTransformFormat($format);
        if (!$this->contextPathBuilderLocator->has($format->value)) {
            throw new InvalidConfigException(\sprintf('Not supported registrar type "%s"', $format->value));
        }

        return $this->contextPathBuilderLocator->get($format->value);
    }

    private function getTransformFormat(true|ContextPathBuilderFormat|string $income): ContextPathBuilderFormat
    {
        if ($income instanceof ContextPathBuilderFormat) {
            $outgoing = $income;
        } elseif (\is_string($income)) {
            $outgoing = ContextPathBuilderFormat::tryFrom($income);
            if (!$outgoing) {
                throw new InvalidConfigException(\sprintf('Unknown context path builder format "%s"', $income));
            }
        } elseif (true === $income) {
            $outgoing = ContextPathBuilderFormat::CODE_BLOCK;
        }

        return $outgoing;
    }
}
