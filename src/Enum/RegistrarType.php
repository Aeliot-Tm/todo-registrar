<?php

declare(strict_types=1);

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
