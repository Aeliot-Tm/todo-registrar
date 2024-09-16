<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\Github;

use Aeliot\TodoRegistrar\Dto\Registrar\Todo;
use Aeliot\TodoRegistrar\Service\Registrar\RegistrarInterface;

final class GithubRegistrar implements RegistrarInterface
{
    public function isRegistered(Todo $todo): bool
    {
        return (bool) preg_match('/^\\s*\\b#\\d+\\b/i', $todo->getSummary());
    }

    public function register(Todo $todo): string
    {
        $client = new \Github\Client();
        // TODO add authentication

        $params = [
            'title' => $todo->getSummary(),
            'body' => $todo->getDescription(),
        ];
        if ($assignee = $todo->getAssignee()) {
            $params['assignee'] = $assignee;
        }
        if ($labels = $todo->getInlineConfig()['labels'] ?? null) {
            $params['labels'] = $labels;
            // TODO create labels when not exists
            // https://github.com/KnpLabs/php-github-api/blob/master/doc/issue/labels.md
        }
        // TODO get username and repo from config
        $response = $client->api('issue')->create('KnpLabs', 'php-github-api-example', $params);
        // TODO: Implement register() method.

        return (string) $response['number'];
    }
}
