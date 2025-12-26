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

use Aeliot\TodoRegistrar\Contracts\TodoInterface;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\Issue;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\IssueApiClient;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\IssueFactory;
use Aeliot\TodoRegistrar\Service\Registrar\Redmine\RedmineRegistrar;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RedmineRegistrar::class)]
final class RedmineRegistrarTest extends TestCase
{
    public function testRegisterExtractsIssueIdFromIssueElement(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $issue = new Issue();

        $issueFactory = $this->createMock(IssueFactory::class);
        $issueFactory->method('create')->with($todo)->willReturn($issue);

        $response = new \SimpleXMLElement('<?xml version="1.0"?><issue><id>123</id></issue>');

        $issueApiClient = $this->createMock(IssueApiClient::class);
        $issueApiClient->method('create')->with($issue)->willReturn($response);

        $registrar = new RedmineRegistrar($issueFactory, $issueApiClient);

        $result = $registrar->register($todo);

        self::assertSame('#123', $result);
    }

    public function testRegisterExtractsIssueIdFromDirectIdElement(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $issue = new Issue();

        $issueFactory = $this->createMock(IssueFactory::class);
        $issueFactory->method('create')->willReturn($issue);

        // Create XML with id as direct child of root (fallback case)
        // The code checks for $response->id, so we need a root element with id as child
        $xml = '<?xml version="1.0"?><response><id>789</id></response>';
        $response = new \SimpleXMLElement($xml);

        $issueApiClient = $this->createMock(IssueApiClient::class);
        $issueApiClient->method('create')->willReturn($response);

        $registrar = new RedmineRegistrar($issueFactory, $issueApiClient);

        $result = $registrar->register($todo);

        self::assertSame('#789', $result);
    }

    public function testRegisterThrowsExceptionWhenIssueIdCannotBeExtracted(): void
    {
        $todo = $this->createMock(TodoInterface::class);
        $issue = new Issue();

        $issueFactory = $this->createMock(IssueFactory::class);
        $issueFactory->method('create')->willReturn($issue);

        $response = new \SimpleXMLElement('<?xml version="1.0"?><issue></issue>');

        $issueApiClient = $this->createMock(IssueApiClient::class);
        $issueApiClient->method('create')->willReturn($response);

        $registrar = new RedmineRegistrar($issueFactory, $issueApiClient);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Unable to extract issue ID from Redmine API response');

        $registrar->register($todo);
    }
}
