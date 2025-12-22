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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Service\Registrar\JIRA\IssueLinkTypeProvider;
use JiraRestApi\IssueLink\IssueLinkService;
use JiraRestApi\IssueLink\IssueLinkType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(IssueLinkTypeProvider::class)]
final class IssueLinkTypeProviderTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public static function getDataForTestPositiveFlow(): iterable
    {
        yield ['10000', 'Blocks'];
        yield ['10000', 'is blocked by'];
        yield ['10000', 'is_blocked_by'];
        yield ['10000', 'blocks'];

        yield ['10001', 'Cloners'];
        yield ['10001', 'is cloned by'];
        yield ['10001', 'is_cloned_by'];
        yield ['10001', 'clones'];

        yield ['10002', 'Duplicate'];
        yield ['10002', 'is duplicated by'];
        yield ['10002', 'is_duplicated_by'];
        yield ['10002', 'duplicates'];

        yield ['10300', 'End -> End [Gantt]'];
        yield ['10300', 'end_end_gantt_'];
        yield ['10300', 'end_to_end_gantt_'];
        yield ['10300', 'has to be finished together with'];
        yield ['10300', 'has_to_be_finished_together_with'];

        yield ['10604', 'Gantt End to End'];
        yield ['10604', 'gantt_end_to_end'];

        yield ['10603', 'Gantt End to Start'];
        yield ['10603', 'gantt_end_to_start'];
        yield ['10603', 'has to be done after'];
        yield ['10603', 'has_to_be_done_after'];
        yield ['10603', 'has to be done before'];
        yield ['10603', 'has_to_be_done_before'];

        yield ['10601', 'Gantt Start to End'];
        yield ['10601', 'gantt_start_to_end'];
        yield ['10601', 'earliest end is start of'];
        yield ['10601', 'earliest_end_is_start_of'];
        yield ['10601', 'start is earliest end of'];
        yield ['10601', 'start_is_earliest_end_of'];

        yield ['10602', 'Gantt Start to Start'];
        yield ['10602', 'gantt_start_to_start'];
        yield ['10602', 'has to be started together with'];
        yield ['10602', 'has_to_be_started_together_with'];

        yield ['10303', 'Hierarchy [Gantt]'];
        yield ['10303', 'Hierarchy_Gantt_'];
        yield ['10303', 'is parent of'];
        yield ['10303', 'is_parent_of'];
        yield ['10303', 'is child of'];
        yield ['10303', 'is_child_of'];

        yield ['10500', 'Issue split'];
        yield ['10500', 'issue_split'];
        yield ['10500', 'split from'];
        yield ['10500', 'split_from'];
        yield ['10500', 'split to'];
        yield ['10500', 'split_to'];

        yield ['10600', 'Parent-Child'];

        yield ['10200', 'Problem/Incident'];
        yield ['10200', 'problem_incident'];
        yield ['10200', 'is caused by'];
        yield ['10200', 'is_caused_by'];
        yield ['10200', 'causes'];

        yield ['10003', 'Relates'];
        yield ['10003', 'relates to'];
        yield ['10003', 'relates_to'];

        yield ['10301', 'Start -> End [Gantt]'];
        yield ['10301', 'start_to_end_gantt_'];
        yield ['10301', 'start_end_gantt_'];

        yield ['10302', 'Start -> Start [Gantt]'];
        yield ['10302', 'start_to_start_gantt_'];
        yield ['10302', 'start_start_gantt_'];
    }

    #[DataProvider('getDataForTestPositiveFlow')]
    public function testPositiveFlow(string $expectedId, string $alias): void
    {
        $provider = $this->createLinkTypeProvider();
        $issueLinkType = $provider->getLinkType($alias);

        self::assertSame($expectedId, $issueLinkType->id);
    }

    private function createLinkTypeProvider(): IssueLinkTypeProvider
    {
        $service = $this->createMock(IssueLinkService::class);
        $service->method('getIssueLinkTypes')->willReturn($this->createIssueLinkTypes());

        return new IssueLinkTypeProvider($service);
    }

    /**
     * @return \ArrayObject<int,IssueLinkType>
     */
    private function createIssueLinkTypes(): \ArrayObject
    {
        $linkTypes = [];
        $contents = file_get_contents(__DIR__ . '/../../../../fixtures/jira_issue_link_types.json');
        $data = json_decode($contents, true, 5, \JSON_THROW_ON_ERROR);
        foreach ($data['issueLinkTypes'] as $datum) {
            $linkType = new IssueLinkType();
            $linkType->id = $datum['id'];
            $linkType->name = $datum['name'];
            $linkType->inward = $datum['inward'];
            $linkType->outward = $datum['outward'];
            $linkType->self = $datum['self'];
            $linkTypes[] = $linkType;
        }

        return new \ArrayObject($linkTypes);
    }
}
