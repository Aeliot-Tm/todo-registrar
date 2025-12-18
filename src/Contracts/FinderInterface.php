<?php
declare(strict_types=1);

namespace Aeliot\TodoRegistrar\Contracts;

/**
 * @template-extends \IteratorAggregate<non-empty-string, \SplFileInfo>
 */
interface FinderInterface extends \IteratorAggregate, \Countable
{
}
