<?php

declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Service\Registrar\JIRA;

use Aeliot\TodoRegistrar\Service\InlineConfig\JIRANotSupportedLinkTypeException;
use JiraRestApi\IssueLink\IssueLinkType;

final class IssueLinkTypeProvider
{
    /**
     * @var IssueLinkType[]|null
     */
    private ?array $supportedLinkTypes = null;

    public function __construct(private readonly ServiceFactory $serviceFactory)
    {
    }

    public function getLinkType(string $alias): IssueLinkType
    {
        foreach ($this->getSupportedLinkTypes() as $issueLinkType) {
            if ($this->isMatch($issueLinkType, $alias)) {
                return $issueLinkType;
            }
        }

        throw new JIRANotSupportedLinkTypeException($alias);
    }

    /**
     * @return IssueLinkType[]
     */
    private function getSupportedLinkTypes(): array
    {
        if (null === $this->supportedLinkTypes) {
            /** @var \ArrayObject<int,IssueLinkType>|iterable<IssueLinkType>|IssueLinkType[] $issueLinkTypes */
            $issueLinkTypes = $this->serviceFactory->createIssueLinkService()->getIssueLinkTypes();
            if ($issueLinkTypes instanceof \ArrayObject) {
                $issueLinkTypes = $issueLinkTypes->getArrayCopy();
            }
            if ($issueLinkTypes instanceof \IteratorAggregate) {
                $issueLinkTypes = $issueLinkTypes->getIterator();
            }
            $this->supportedLinkTypes = $issueLinkTypes instanceof \Traversable
                ? iterator_to_array($issueLinkTypes)
                : $issueLinkTypes;
        }

        return $this->supportedLinkTypes;
    }

    private function isMatch(IssueLinkType $lintType, string $alias): bool
    {
        $quotedAlis = preg_quote($alias, '/');
        $regex1 = \sprintf('/^%s/i', str_replace('_', '[^0-9a-z]+', $quotedAlis));
        $regex2 = \sprintf('/^%s/i', str_replace('_', '[^0-9a-z]+', str_replace('_to_', ' -> ', $quotedAlis)));

        return $alias === $lintType->name
            || $alias === $lintType->inward
            || $alias === $lintType->outward
            || preg_match($regex1, $lintType->name)
            || preg_match($regex1, $lintType->inward)
            || preg_match($regex1, $lintType->outward)
            || preg_match($regex2, $lintType->name);
    }
}
