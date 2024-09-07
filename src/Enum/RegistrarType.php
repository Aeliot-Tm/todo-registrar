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

namespace Aeliot\TodoRegistrar\Enum;

enum RegistrarType: string
{
    case AzureBoards = 'AzureBoards';
    case Github = 'Github';
    case Gitlab = 'Gitlab';
    case JIRA = 'JIRA';
    case PivotalTracker = 'PivotalTracker';
    case Redmine = 'Redmine';
    case YouTrack = 'YouTrack';
}
