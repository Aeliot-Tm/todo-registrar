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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\Redmine;

use Aeliot\TodoRegistrar\Exception\UnexpectedApiResponseException;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\Issue;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\IssueApiClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Redmine\Api\Issue as IssueApi;
use Redmine\Client\Client;

#[CoversClass(IssueApiClient::class)]
final class IssueApiClientTest extends TestCase
{
    public function testCreateReturnsSimpleXMLElement(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $response = new \SimpleXMLElement('<?xml version="1.0"?><issue><id>123</id></issue>');

        $issueApi = $this->createMock(IssueApi::class);
        $issueApi->method('create')->with($issue->getData())->willReturn($response);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue')->willReturn($issueApi);

        $apiClient = new IssueApiClient($client);

        $result = $apiClient->create($issue);

        self::assertInstanceOf(\SimpleXMLElement::class, $result);
        // The actual value extraction and structure are tested in RedmineRegistrarTest
    }

    public function testCreateThrowsExceptionOnClientException(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $clientException = new class('HTTP 403 Forbidden') extends \Exception implements \Psr\Http\Client\ClientExceptionInterface {
        };

        $issueApi = $this->createMock(IssueApi::class);
        $issueApi->method('create')->willThrowException($clientException);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue')->willReturn($issueApi);

        $apiClient = new IssueApiClient($client);

        $this->expectException(UnexpectedApiResponseException::class);
        $this->expectExceptionMessage('Redmine API error: HTTP 403 Forbidden');

        $apiClient->create($issue);
    }

    public function testCreateThrowsExceptionOnStringResponse(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $issueApi = $this->createMock(IssueApi::class);
        $issueApi->method('create')->willReturn('Error message');

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue')->willReturn($issueApi);

        $apiClient = new IssueApiClient($client);

        $this->expectException(UnexpectedApiResponseException::class);
        $this->expectExceptionMessage('Redmine API error: Error message');

        $apiClient->create($issue);
    }

    public function testCreateThrowsExceptionOnEmptyStringResponse(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $issueApi = $this->createMock(IssueApi::class);
        $issueApi->method('create')->willReturn('');

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue')->willReturn($issueApi);

        $apiClient = new IssueApiClient($client);

        $this->expectException(UnexpectedApiResponseException::class);
        $this->expectExceptionMessage('Redmine API error: Empty response from Redmine API');

        $apiClient->create($issue);
    }

    public function testCreateThrowsExceptionOnUnexpectedResponseType(): void
    {
        $issue = new Issue();
        $issue->setSubject('Test Subject');

        $issueApi = $this->createMock(IssueApi::class);
        $issueApi->method('create')->willReturn(['unexpected' => 'array']);

        $client = $this->createMock(Client::class);
        $client->method('getApi')->with('issue')->willReturn($issueApi);

        $apiClient = new IssueApiClient($client);

        $this->expectException(UnexpectedApiResponseException::class);
        $this->expectExceptionMessage('Redmine API returned unexpected response type: expected SimpleXMLElement, got array');

        $apiClient->create($issue);
    }
}
