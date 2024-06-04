<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Test\Unit\Service\Tag;

use Aeliot\TodoRegistrar\Dto\Tag\TagMetadata;
use Aeliot\TodoRegistrar\Service\Tag\Detector;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(Detector::class)]
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

    #[DataProvider('getDataForTestAssigneeDetection')]
    public function testAssigneeDetection(string $expectedAssignee, string $line): void
    {
        $tagMetadata = (new Detector())->getTagMetadata($line);
        self::assertInstanceOf(TagMetadata::class, $tagMetadata);
        self::assertSame($expectedAssignee, $tagMetadata->getAssignee());
    }

    #[DataProvider('getDataForTestAssigneeNotDetected')]
    public function testAssigneeNotDetected(string $line): void
    {
        $tagMetadata = (new Detector())->getTagMetadata($line);
        self::assertInstanceOf(TagMetadata::class, $tagMetadata);
        self::assertNull($tagMetadata->getAssignee());
    }

    #[DataProvider('getDataForTestTagDetection')]
    public function testTagDetection(string $expectedTag, string $line, array $tags): void
    {
        $tagMetadata = (new Detector($tags))->getTagMetadata($line);
        self::assertInstanceOf(TagMetadata::class, $tagMetadata);
        self::assertSame($expectedTag, $tagMetadata->getTag());
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
        $tagMetadata = (new Detector($tags))->getTagMetadata($line);
        self::assertSame($expectedTag, $tagMetadata->getTag());
    }
}