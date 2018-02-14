<?php
namespace Thunder\Serializard\HydratorContainer;

use Thunder\Serializard\Exception\HydratorConflictException;
use Thunder\Serializard\Exception\HydratorNotFoundException;
use Thunder\Serializard\Exception\InvalidClassNameException;
use Thunder\Serializard\Exception\InvalidHydratorException;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class FallbackHydratorContainer implements HydratorContainerInterface
{
    private $handlers = [];
    private $interfaces = [];
    private $aliases = [];

    public function add($class, $handler)
    {
        if(false === is_callable($handler)) {
            throw new InvalidHydratorException(sprintf('Invalid handler for class %s!', $class));
        }

        if(class_exists($class)) {
            $this->aliases[$class] = $class;
            $this->handlers[$class] = $handler;
        } elseif(interface_exists($class)) {
            $this->aliases[$class] = $class;
            $this->interfaces[$class] = $handler;
        } else {
            throw new InvalidClassNameException(sprintf('Given value %s is neither class nor interface name!', $class));
        }
    }

    public function addAlias($alias, $class)
    {
        $handler = $this->getHandler($class);

        if(null === $handler) {
            throw new HydratorNotFoundException(sprintf('Handler for class %s does not exist!', $class));
        }

        $this->handlers[$alias] = $handler;
        $this->aliases[$alias] = $this->aliases[$class];
    }

    public function getHandler($class)
    {
        if(array_key_exists($class, $this->handlers)) {
            return $this->handlers[$class];
        }

        $parents = array_intersect(array_keys($this->handlers), class_parents($class));
        if($parents) {
            return $this->handlers[array_pop($parents)];
        }

        $interfaces = array_intersect(array_keys($this->interfaces), array_values(class_implements($class)));
        if($interfaces) {
            if(count($interfaces) > 1) {
                throw new HydratorConflictException(sprintf('Class %s implements interfaces with colliding handlers!', $class));
            }

            return $this->interfaces[array_shift($interfaces)];
        }

        return null;
    }

    public function hydrate($class, array $data)
    {
        return call_user_func($this->getHandler($class), $data, $this);
    }
}
