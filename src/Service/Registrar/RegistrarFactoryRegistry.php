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

namespace Aeliot\TodoRegistrar\Service\Registrar;

use Aeliot\TodoRegistrar\Enum\RegistrarType;
use Aeliot\TodoRegistrar\Service\Registrar\Github\GithubRegistrarFactory;
use Aeliot\TodoRegistrar\Service\Registrar\JIRA\JiraRegistrarFactory;

class RegistrarFactoryRegistry
{
    public function getFactory(RegistrarType $type): RegistrarFactoryInterface
    {
        return match ($type) {
            RegistrarType::Github => new GithubRegistrarFactory(),
            RegistrarType::JIRA => new JiraRegistrarFactory(),
            // TODO add factory of different registrars
            default => throw new \DomainException(\sprintf('Not supported registrar type "%s"', $type->value)),
        };
    }
}
