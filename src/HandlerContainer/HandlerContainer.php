<?php
namespace Thunder\Serializard\HandlerContainer;

/**
 * @author Tomasz Kowalczyk <tomasz@kowalczyk.cc>
 */
final class HandlerContainer implements HandlerContainerInterface
{
    private $handlers = array();
    private $interfaces = array();
    private $aliases = array();

    public function add($class, $root, $handler)
    {
        if(false === is_callable($handler)) {
            throw new \RuntimeException(sprintf('Invalid handler for class %s!', $class));
        }

        if(class_exists($class)) {
            $this->aliases[$class] = $root;
            $this->handlers[$class] = $handler;
        } elseif(interface_exists($class)) {
            $this->aliases[$class] = $root;
            $this->interfaces[$class] = $handler;
        } else {
            throw new \RuntimeException(sprintf('Given value %s is neither class nor interface name!', $class));
        }
    }

    public function addAlias($alias, $class)
    {
        $handler = $this->getHandler($class);

        if(null === $handler) {
            throw new \RuntimeException(sprintf('Handler for class %s does not exist!', $class));
        }

        $this->handlers[$alias] = $handler;
        $this->aliases[$alias] = $this->aliases[$class];
    }

    public function getRoot($class)
    {
        return $this->aliases[$class];
    }

    public function getHandler($class)
    {
        if(array_key_exists($class, $this->handlers)) {
            return $this->handlers[$class];
        }

        $implemented = array_intersect(array_keys($this->interfaces), array_values(class_implements($class)));
        if($implemented) {
            if(count($implemented) > 1) {
                throw new \RuntimeException(sprintf('Class %s implements interfaces with colliding handlers!', $class));
            }

            return $this->interfaces[$implemented[0]];
        }

        return null;
    }
}
