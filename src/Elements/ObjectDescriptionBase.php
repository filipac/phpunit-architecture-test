<?php

declare(strict_types=1);

namespace PHPUnit\Architecture\Elements;

use Exception;
use PhpParser\Node;
use PHPUnit\Architecture\Enums\ObjectType;
use PHPUnit\Architecture\Services\ServiceContainer;
use ReflectionClass;

abstract class ObjectDescriptionBase
{
    public string $type;

    public string $path;

    /**
     * @var class-string<mixed>
     */
    public string $name;

    /**
     * @var Node[]
     */
    public array $stmts;

    public ReflectionClass $reflectionClass; // @phpstan-ignore-line

    public static function make(string $path): ?self
    {
        $ast = null;
        $content = file_get_contents($path);
        if ($content === false) {
            throw new Exception("Path: '{$path}' not found");
        }

        try {
            $ast = ServiceContainer::$parser->parse($content);
        } catch (Exception $e) {
            if (ServiceContainer::$showException) {
                echo "Path: $path Exception: {$e->getMessage()}";
            }
        }

        if ($ast === null) {
            return null;
        }

        $stmts = ServiceContainer::$nodeTraverser->traverse($ast);

        /** @var Node\Stmt\Class_|Node\Stmt\Trait_|Node\Stmt\Interface_|Node\Stmt\Enum_|null $object */
        $object = ServiceContainer::$nodeFinder->findFirst($stmts, static function (Node $node) {
            return $node instanceof Node\Stmt\Class_
                || $node instanceof Node\Stmt\Trait_
                || $node instanceof Node\Stmt\Interface_
                || $node instanceof Node\Stmt\Enum_//
                ;
        });

        if ($object === null) {
            return null;
        }

        if (!property_exists($object, 'namespacedName')) {
            return null;
        }

        if ($object->namespacedName === null) {
            return null;
        }

        $description = new static(); // @phpstan-ignore-line

        if ($object instanceof Node\Stmt\Class_) {
            $description->type = 'class';
        } elseif ($object instanceof Node\Stmt\Trait_) {
            $description->type = 'trait';
        } elseif ($object instanceof Node\Stmt\Interface_) {
            $description->type = 'interface';
        } elseif ($object instanceof Node\Stmt\Enum_) {
            $description->type = 'enum';
        }

        /** @var class-string $className */
        $className = $object->namespacedName->toString();

//        ray($description, $className, $path);

        $description->path = $path;
        $description->name = $className;
        $description->stmts = $stmts;
        $description->reflectionClass = new ReflectionClass($description->name);

        return $description;
    }

    public function __toString()
    {
        return $this->name;
    }
}
