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

namespace Aeliot\TodoRegistrar\Service\Registrar\YandexTracker;

use BugrovWeb\YandexTracker\Api\Client;
use BugrovWeb\YandexTracker\Api\Requests\Issue\IssueRequest;

/**
 * Extended issue create request with tags support.
 *
 * @internal
 *
 * @method ExtendedIssueCreateRequest summary(string $name) Issue title. Required
 * @method ExtendedIssueCreateRequest queue(array<string, mixed>|string|int $queue) Queue. Required
 * @method ExtendedIssueCreateRequest parent(string|array<string, mixed> $parent) Parent issue
 * @method ExtendedIssueCreateRequest description(string $text) Issue description
 * @method ExtendedIssueCreateRequest sprint(array<string, mixed> $sprintArray) Sprint information
 * @method ExtendedIssueCreateRequest type(array<string, mixed>|string|int $issueType) Issue type
 * @method ExtendedIssueCreateRequest priority(array<string, mixed>|string|int $priority) Issue priority
 * @method ExtendedIssueCreateRequest followers(array<int, string> $followersArray) Followers
 * @method ExtendedIssueCreateRequest assignee(array<string, mixed>|string $assignee) Assignee
 * @method ExtendedIssueCreateRequest unique(string $uniqueField) Unique field to prevent duplicates
 * @method ExtendedIssueCreateRequest attachmentIds(array<int, string> $attachments) Attachments
 * @method ExtendedIssueCreateRequest tags(array<int, string> $tags) Issue tags
 */
class ExtendedIssueCreateRequest extends IssueRequest
{
    public const ACTION = 'issues';
    public const METHOD = Client::METHOD_POST;

    /**
     * @var array<string, array<string, mixed>>
     */
    protected array $data = [
        'queryParams' => [],
        'bodyParams' => [],
    ];

    /**
     * @var string[]
     */
    protected array $bodyParams = [
        'summary',
        'queue',
        'parent',
        'description',
        'sprint',
        'type',
        'priority',
        'followers',
        'assignee',
        'unique',
        'attachmentIds',
        'tags',
    ];

    public function __construct()
    {
        $this->url = self::ACTION . '/';
    }
}
