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

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Tag;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Tag\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
#[UsesClass(TagMetadata::class)]
final class DetectorTest extends TestCase
{
    public static function getDataForTestAssigneeDetection(): iterable
    {
        yield ['an_assignee', '// TODO@an_assignee'];
        yield ['4fan', '// TODO@4fan'];
    }

    public static function getDataForTestAssigneeNotDetected(): iterable
    {
        yield ['// TODO@ an_assignee'];
        yield ['// TODO @an_assignee'];
    }

    public static function getDataForTestSeparatorOffset(): iterable
    {
        yield [7, '// TODO: fix it'];
        yield [15, '// TODO APP-123: fix it'];
        yield [14, '// TODO@markus: fix it'];
        yield [22, '// TODO@markus APP-123: fix it'];
        yield [7, '// TODO- fix it'];
        yield [8, '// TODO - fix it'];
    }

    public static function getDataForTestSeparatorOffsetNull(): iterable
    {
        yield ['// TODO fix it'];
        yield ['// TODO APP-123 fix it'];
        yield ['// TODO@markus APP-123 fix it'];
    }

    public static function getDataForTestTagDetection(): iterable
    {
        // tags collections
        yield ['TODO', '// TODO', ['todo']];
        yield ['TODO', '// TODO', ['fixme', 'todo']];
        yield ['FIXME', '// FIXME', ['fixme', 'todo']];
        yield ['FIXME', '// FIXME', ['fixme']];
        yield ['MY_CUSTOM_TAG', '// MY_CUSTOM_TAG', ['my_custom_tag']];

        // line prefixes
        yield ['TODO', '// TODO', ['todo']];
        yield ['TODO', ' // TODO', ['todo']];
        yield ['TODO', '# TODO', ['todo']];
        yield ['TODO', ' # TODO', ['todo']];
        yield ['TODO', '* TODO', ['todo']];
        yield ['TODO', ' * TODO', ['todo']];
        yield ['TODO', ' /* TODO', ['todo']];
        yield ['TODO', ' /** TODO', ['todo']];
        yield ['TODO', ' TODO', ['todo']];
        yield ['TODO', 'TODO', ['todo']];

        // line formats
        yield ['TODO', '// TODO:', ['todo']];
        yield ['TODO', '// TODO: and comment', ['todo']];
        yield ['TODO', '// TODO@an_assignee', ['todo']];
        yield ['TODO', '// TODO@an_assignee:', ['todo']];
        yield ['TODO', '// TODO@an_assignee: and comment', ['todo']];
        yield ['TODO', '// TODO@an_assignee and comment', ['todo']];
    }

    public static function getDataForTestTagNotDetected(): iterable
    {
        yield ['// FIXME', ['todo']];
        yield ['// TODO', ['fixme']];
    }

    public static function getDataForTestPrefixLength(): iterable
    {
        // line formats
        yield [7, '// TODO text of comment'];
        yield [8, '// TODO: text of comment'];
        yield [19, '// TODO@an_assignee text of comment'];
        yield [20, '// TODO@an_assignee: text of comment'];
    }

    public static function getDataForTestTagUppercased(): iterable
    {
        yield ['TAG', '// tag', ['tag']];
        yield ['TAG', '// Tag', ['tag']];
        yield ['TAG', '// tAg', ['tag']];
        yield ['TAG', '// taG', ['tag']];
        yield ['TAG', '// TAg', ['tag']];
        yield ['TAG', '// tAG', ['tag']];
        yield ['TAG', '// TaG', ['tag']];
        yield ['TAG', '// TAG', ['tag']];
    }

    public static function getDataForTestTicketKeyMatch(): iterable
    {
        yield [
            '2023-12-14',
            '// TODO: 2023-12-14 This comment turns into a PHPStan error as of 14th december 2023',
        ];

        yield [
            'https://github.com/staabm/phpstan-td-by/issues/91',
            '// TODO https://github.com/staabm/phpstan-td-by/issues/91 fix me when this GitHub issue is closed',
        ];

        // url with expected tag as part of URL
        yield [
            'https://github.com/staabm/phpstan-todo-by/issues/91',
            '// TODO https://github.com/staabm/phpstan-todo-by/issues/91 fix me when this GitHub issue is closed',
        ];

        yield [
            '<1.0.0',
            '// TODO: <1.0.0 This has to be in the first major release of this repo',
        ];

        yield [
            'phpunit/phpunit:5.3',
            '// TODO: phpunit/phpunit:5.3 This has to be fixed when updating phpunit to 5.3.x or higher',
        ];

        yield [
            'php:8',
            '// TODO: php:8 drop this polyfill when php 8.x is required',
        ];

        yield [
            'APP-2137',
            '// TODO: APP-2137 A comment which errors when the issue tracker ticket gets resolved',
        ];

        yield ['2023-12-14', '// todo 2023-12-14'];
        yield ['2023-12-14', '// @todo: 2023-12-14 fix it'];
        yield ['2023-12-14', '// @todo 2023-12-14: fix it'];
        yield ['2023-12-14', '// TODO - 2023-12-14 fix it'];
        yield ['2023-12-14', '// FIXME 2023-12-14 - fix it'];
        yield ['2023-12-14', '// TODO@staabm 2023-12-14 - fix it'];
        yield ['2023-12-14', '// TODO@markus: 2023-12-14 - fix it'];
        yield ['>123.4', '// TODO >123.4: Must fix this or bump the version'];
        yield ['>v123.4', '// TODO >v123.4: Must fix this or bump the version'];
        yield ['phpunit/phpunit:<5', '// TODO: phpunit/phpunit:<5 This has to be fixed before updating to phpunit 5.x'];

        yield [
            'phpunit/phpunit:5.3',
            '// TODO@markus: phpunit/phpunit:5.3 This has to be fixed when updating phpunit to 5.3.x or higher',
        ];

        yield ['APP-123', '// TODO: APP-123 fix it when this Jira ticket is closed'];
        yield ['#123', '// TODO: #123 fix it when this GitHub issue is closed'];
        yield ['GH-123', '// TODO: GH-123 fix it when this GitHub issue is closed'];
        yield [
            'some-organization/some-repo#123',
            '// TODO: some-organization/some-repo#123 change me if this GitHub pull request is closed',
        ];
    }

    #[DataProvider('getDataForTestAssigneeDetection')]
    public function testAssigneeDetection(string $expectedAssignee, string $line): void
    {
        self::assertSame($expectedAssignee, $this->getTagMetadata($line)->getAssignee());
    }

    #[DataProvider('getDataForTestAssigneeNotDetected')]
    public function testAssigneeNotDetected(string $line): void
    {
        self::assertNull($this->getTagMetadata($line)->getAssignee());
    }

    #[DataProvider('getDataForTestPrefixLength')]
    public function testPrefixLength(int $expectedPrefixLength, string $line): void
    {
        self::assertSame($expectedPrefixLength, $this->getTagMetadata($line)->getPrefixLength());
    }

    #[DataProvider('getDataForTestSeparatorOffset')]
    public function testSeparatorOffset(int $expectedOffset, string $line): void
    {
        self::assertSame($expectedOffset, $this->getTagMetadata($line)->getSeparatorOffset());
    }

    #[DataProvider('getDataForTestSeparatorOffsetNull')]
    public function testSeparatorOffsetNull(string $line): void
    {
        self::assertNull($this->getTagMetadata($line)->getSeparatorOffset());
    }

    #[DataProvider('getDataForTestTagDetection')]
    public function testTagDetection(string $expectedTag, string $line, array $tags): void
    {
        self::assertSame($expectedTag, $this->getTagMetadata($line, $tags)->getTag());
    }

    #[DataProvider('getDataForTestTagNotDetected')]
    public function testTagNotDetected(string $line, array $tags): void
    {
        $tagMetadata = (new Detector($tags))->getTagMetadata($line);
        self::assertNull($tagMetadata);
    }

    #[DataProvider('getDataForTestTagUppercased')]
    public function testTagUppercased(string $expectedTag, string $line, array $tags): void
    {
        self::assertSame($expectedTag, $this->getTagMetadata($line, $tags)->getTag());
    }

    // vendor/bin/phpunit --filter='DetectorTest::testTicketKeyMatch'
    #[DataProvider('getDataForTestTicketKeyMatch')]
    public function testTicketKeyMatch(string $expectedTicketKey, string $line): void
    {
        self::assertSame($expectedTicketKey, $this->getTagMetadata($line)->getTicketKey());
    }

    private function getTagMetadata(string $line, array $tags = []): TagMetadata
    {
        $detector = $tags ? new Detector($tags) : new Detector();
        $tagMetadata = $detector->getTagMetadata($line);
        self::assertInstanceOf(TagMetadata::class, $tagMetadata);

        return $tagMetadata;
    }
}
