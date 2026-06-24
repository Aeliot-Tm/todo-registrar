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

use Aeliot\TodoRegistrar\Service\Registrar\JIRA\CustomFieldIdFinder;
use JiraRestApi\Field\Field;
use JiraRestApi\Field\FieldService;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

#[CoversClass(CustomFieldIdFinder::class)]
final class CustomFieldIdFinderTest extends TestCase
{
    /**
     * @return iterable<array{0: string, 1: string}>
     */
    public static function getDataForTestPositiveFlow(): iterable
    {
        yield ['customfield_123', 'My Custom Field'];
        yield ['customfield_123', 'customfield_123'];
        yield ['customfield_123', 'CUSTOMFIELD_123'];
        yield ['customfield_123', '123'];
        yield ['customfield_123', 'cf[123]'];

        yield ['customfield_10100', 'Explanation'];
        yield ['customfield_10100', '10100'];
        yield ['customfield_10100', 'cf[10100]'];

        yield ['customfield_10200', 'Platform'];
        yield ['customfield_10200', 'cf[10200]'];
    }

    #[DataProvider('getDataForTestPositiveFlow')]
    public function testPositiveFlow(string $expectedId, string $alias): void
    {
        $finder = $this->createCustomFieldIdFinder();

        self::assertSame($expectedId, $finder->getId($alias));
    }

    public function testReturnsNullForUnknownField(): void
    {
        $finder = $this->createCustomFieldIdFinder();

        self::assertNull($finder->getId('Unknown field'));
    }

    private function createCustomFieldIdFinder(): CustomFieldIdFinder
    {
        $service = $this->createMock(FieldService::class);
        $service->method('getAllFields')->willReturn($this->createCustomFields());

        return new CustomFieldIdFinder($service);
    }

    /**
     * @return \ArrayObject<int,Field>
     */
    private function createCustomFields(): \ArrayObject
    {
        $fields = [];
        $contents = file_get_contents(__DIR__ . '/../../../../fixtures/jira_custom_fields.json');
        $data = json_decode($contents, true, 5, \JSON_THROW_ON_ERROR);
        foreach ($data as $datum) {
            $field = new Field();
            $field->id = $datum['id'];
            $field->name = $datum['name'];
            $field->custom = $datum['custom'];
            $field->clauseNames = $datum['clauseNames'];
            $fields[] = $field;
        }

        return new \ArrayObject($fields);
    }
}
