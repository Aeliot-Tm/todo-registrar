<?php

namespace App\Services;

// TODO: File level comment
interface ServiceInterface
{
    // TODO: Interface method comment
    public function execute(): void;
}

interface RepositoryInterface
{
    // TODO: Repository interface comment
    public function find(int $id): mixed;
}

namespace App\Models;

// TODO: First class comment
class User
{
    // TODO: Property comment
    private string $name;

    // TODO: Constructor comment
    public function __construct(string $name)
    {
        $this->name = $name;
    }

    // TODO: Method comment
    public function getName(): string
    {
        // TODO: Inside method comment
        return $this->name;
    }

    // TODO: Method with closure comment
    public function process(): void
    {
        $callback = function () {
            // TODO: Inside closure comment
            return true;
        };

        $callback();
    }

    // TODO: Method with arrow function comment
    public function filter(array $items): array
    {
        // TODO: Before arrow function comment
        return array_filter(
            $items,
            fn ($item) => $item > 0 /* TODO: Inside arrow function comment */
        );
    }
}

// TODO: Second class comment
class Product
{
    // TODO: Product property comment
    private float $price;

    // TODO: Product method comment
    public function getPrice(): float
    {
        // TODO: Match expression comment
        return match ($this->price) {
            // TODO: Inside match comment
            0 => 0.0,
            default => $this->price,
        };
    }
}

namespace App\Helpers;

// TODO: Function comment
function helper(): string
{
    // TODO: Inside function comment
    return 'helper';
}

// TODO: Second function comment
function anotherHelper(): void
{
    // TODO: Inside another function comment
    $nested = function () {
        // TODO: Nested closure in function comment
        return true;
    };
}

namespace App\Traits;

// TODO: Trait comment
trait LoggerTrait
{
    // TODO: Trait method comment
    public function log(string $message): void
    {
        // TODO: Inside trait method comment
        echo $message;
    }
}

namespace App\Enums;

// TODO: Enum comment
enum Status: string
{
    // TODO: Enum case comment
    case Active = 'active';
    case Inactive = 'inactive';

    // TODO: Enum method comment
    public function isActive(): bool
    {
        // TODO: Inside enum method comment
        return $this === self::Active;
    }
}

namespace App\Factory;

// TODO: Factory function comment
function createService(): object
{
    // TODO: Anonymous class comment
    return new class {
        // TODO: Inside anonymous class comment
        private string $value = 'test';

        // TODO: Anonymous class method comment
        public function getValue(): string
        {
            // TODO: Inside anonymous class method comment
            return $this->value;
        }
    };
}

namespace App\Controller;

#[\Attribute]
class Route
{
    public function __construct(public string $path) {}
}

#[\Attribute]
class AsService
{
}

// TODO: Class with attribute comment
#[Route('/api/users')]
class UserController
{
    // TODO: Method with attribute comment
    #[Route('/list')]
    public function list(): array
    {
        // TODO: Inside method with attribute comment
        return [];
    }

    // TODO: Multiple attributes comment
    #[Route('/create')]
    #[Route('/store')]
    public function create(): void
    {
        // TODO: Inside method with multiple attributes comment
    }

    public function item(
        object $item,
        // TODO: comment to argument
        #[AsService]
        callable $callback,
    ): mixed
    {
        return $callback($item);
    }
}

// TODO: Property with attribute comment
class Entity
{
    // TODO: Class constant comment
    public const MAX_SIZE = 100;

    // TODO: Multiple constants comment
    private const MIN_SIZE = 1, DEFAULT_SIZE = 10;

    // TODO: Property attribute comment
    #[Route('/test')]
    private string $field;
}

class Multiple
{
    /**
     * TODO: refactor invalid implementation
     */
    private /*TODO: make protected */ /* TODO: make object type */ array /* TODO: rename */ $propA = [];

    /**
     * TODO: refactor invalid implementation
     */
    var /*TODO: make protected */ /* TODO: make object type */
    array /* TODO: rename */
        $propD = [];

    public function __construct(
        private /*TODO: make protected */ /* TODO: make object type */ array /* TODO: rename */ $propB = [],
    ) {
    }

    public function withProp(
        /* TODO: make object type */ array /* TODO: rename */ $propC = /* TODO: change default value */ []
    ): array {
        return [$this->propA, $this->propB, $propC, $this->propD];
    }
}
