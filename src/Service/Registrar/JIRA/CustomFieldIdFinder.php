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

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Exception\Api\UnexpectedResponseException;
use JiraRestApi\Field\Field;
use JiraRestApi\Field\FieldService;
use JiraRestApi\JiraException;

/**
 * @internal
 */
final class CustomFieldIdFinder
{
    /**
     * @var Field[]|null
     */
    private ?array $customFields = null;

    public function __construct(private readonly FieldService $fieldService)
    {
    }

    /**
     * @throws UnexpectedResponseException
     */
    public function getId(string $nameOrId): ?string
    {
        foreach ($this->getCustomFields() as $field) {
            if ($this->isMatch($field, $nameOrId)) {
                return $field->id;
            }
        }

        return null;
    }

    /**
     * @return Field[]
     *
     * @throws UnexpectedResponseException
     */
    private function getCustomFields(): array
    {
        if (null === $this->customFields) {
            try {
                /** @var \ArrayObject<int,Field>|iterable<Field>|Field[] $customFields */
                $customFields = $this->fieldService->getAllFields(Field::CUSTOM);
            } catch (JiraException $exception) {
                throw new UnexpectedResponseException('Cannot get custom fields from JIRA', 0, $exception);
            }

            if ($customFields instanceof \ArrayObject) {
                $customFields = $customFields->getArrayCopy();
            }
            if ($customFields instanceof \IteratorAggregate) {
                $customFields = $customFields->getIterator();
            }
            $this->customFields = $customFields instanceof \Traversable
                ? iterator_to_array($customFields)
                : $customFields;
        }

        return $this->customFields;
    }

    private function isMatch(Field $field, string $nameOrId): bool
    {
        if (0 === strcasecmp($field->id, $nameOrId)) {
            return true;
        }

        if ($nameOrId === $field->name) {
            return true;
        }

        if (preg_match('/^\d+$/', $nameOrId) && 0 === strcasecmp($field->id, 'customfield_' . $nameOrId)) {
            return true;
        }

        if (\in_array($nameOrId, $field->clauseNames, true)) {
            return true;
        }

        return false;
    }
}
