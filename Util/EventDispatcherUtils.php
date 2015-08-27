<?php

namespace ITE\FormBundle\Util;

use ITE\Common\Util\ReflectionUtils;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\EventDispatcher\ImmutableEventDispatcher;

/**
 * Class EventDispatcherUtils
 *
 * @author c1tru55 <mr.c1tru55@gmail.com>
 */
class EventDispatcherUtils
{
    /**
     * @param EventDispatcherInterface $ed1
     * @param EventDispatcherInterface $ed2
     */
    public static function extend(EventDispatcherInterface $ed1, EventDispatcherInterface $ed2)
    {
        $rawEd1 = self::getRawEventDispatcher($ed1);
        $listeners = self::getRawListeners($ed2);

        foreach ($listeners as $eventName => $priorityEventListeners) {
            foreach ($priorityEventListeners as $priority => $eventListeners) {
                foreach ($eventListeners as $eventListener) {
                    if (!self::hasListener($ed1, $eventName, $eventListener)) {
                        $rawEd1->addListener($eventName, $eventListener, $priority);
                    }
                }
            }
        }

        ReflectionUtils::setValue($rawEd1, 'sorted', []);
    }

    /**
     * @param EventDispatcherInterface $ed
     * @return EventDispatcherInterface
     */
    public static function getRawEventDispatcher(EventDispatcherInterface $ed)
    {
        if ($ed instanceof ImmutableEventDispatcher) {
            $refClass = new \ReflectionClass($ed);

            $refProp = $refClass->getProperty('dispatcher');
            $refProp->setAccessible(true);
            $dispatcher = $refProp->getValue($ed);
            $refProp->setAccessible(false);

            return $dispatcher;
        }

        return $ed;
    }

    /**
     * @param EventDispatcherInterface $ed
     * @return array
     */
    public static function getRawListeners(EventDispatcherInterface $ed)
    {
        $rawEd = self::getRawEventDispatcher($ed);

        return ReflectionUtils::getValue($rawEd, 'listeners');
    }

    /**
     * @param EventDispatcherInterface $ed
     * @param $eventName
     * @param $listener
     * @return bool
     */
    public static function hasListener(EventDispatcherInterface $ed, $eventName, $listener)
    {
        return null !== self::getPriority($ed, $eventName, $listener);
    }

    /**
     * @param EventDispatcherInterface $ed
     * @param string $eventName
     * @param mixed $listener
     * @return int|null
     */
    public static function getPriority(EventDispatcherInterface $ed, $eventName, $listener)
    {
        $listeners = self::getRawListeners($ed);

        if (!isset($listeners[$eventName])) {
            return null;
        }
        foreach ($listeners[$eventName] as $priority => $eventListeners) {
            foreach ($eventListeners as $eventListener) {
                if (is_array($listener) && is_array($eventListener)) {
                    $classEquals = false;
                    if (is_object($listener[0]) && is_object($eventListener[0])
                        && get_class($listener[0]) === get_class($eventListener[0])) {
                        $classEquals = true;
                    } elseif ($listener[0] === $eventListener[0]) {
                        $classEquals = true;
                    }
                    if ($classEquals && $listener[1] === $eventListener[1]) {
                        return $priority;
                    }
                } elseif ($listener === $eventListener) {
                    return $priority;
                }
            }
        }

        return null;
    }
}