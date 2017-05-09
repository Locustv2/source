<?php
/**
 * @link https://github.com/Locustv2/source
 * @copyright Copyright (c) 2017 locustv2
 * @license https://github.com/Locustv2/source/blob/master/LICENSE.md
 * @author Yuv Joodhisty <locustv2@gmail.com>
 */

namespace source\mutex;

/**
 * A Mutex is a way of locking resources that can only be used by a single
 * thread. It is a programming concept that is used to protect shared resources
 * from being simultaneously accessed by multiple threads.
 * This class provide an abstraction implementation of a Mutex. Child or
 * subclasses should implement their way of locking and unlocking a resource.
 *
 * @example
 * ```php
 * $somedata = ChildMutex::exec('this_task_id', function($mutex) {
 *     // do something
 *     return 'something';
 * });
 * ```
 */
abstract class Mutex
{
    /**
     * @var [[Mutex]] the singleton instance that will be used for the Mutex.
     */
    protected static $instance = null;

    /**
     * Returns the singleton instance to use. If there are no current instance,
     * a new one will be created and returned.
     * @return [[Mutex]] the mutex instance.
     */
    protected static function getInstance(): self
    {
        if(!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    /**
     * Obtains the instance through late static binding and tries to see if the
     * wanted resource is locked before taking control of it. If the requested
     * resource is currently locked, a timeout can be set to wait for it to be
     * available, otherwise it will fail and return null.
     *
     * @param mixed $resource the resource that should not be accessed
     * simultaneously.
     * @param Closure $closure an anonymous function that can be used to change
     * the state of the resource
     * @param int $timeout the timeout of seconds to wait for the availability
     * of the resource.
     * @return mixed an optional return if you want to return something.
     */
    public static function exec($resource, \Closure $closure, int $timeout = 0)
    {
        $instance = static::getInstance();
        $output = null;

        $parsedResourceId = $resource;
        if(!is_scalar($parsedResourceId)) {
            $parsedResourceId = serialize($resource);
        }

        $parsedResourceId = md5($parsedResourceId);
        if($instance->acquireLock($parsedResourceId, $timeout)) {
            $output = $closure($resource, $instance);
            $instance->releaseLock($parsedResourceId);
        }
        return $output;
    }

    /**
     * The implementation of the locking mechanism.
     * @param int|string $resourceId the id of the resource to lock
     * @param int $timeout the timeout of seconds to wait for the availability
     * of the resource.
     * @return bool whether the lock was acquired or not.
     */
    abstract protected function acquireLock($resourceId, int $timeout = 0): bool;

    /**
     * The implementation of the unlocking mechanism.
     * @param int|string $resourceId the id of the resource to unlock
     * @return bool whether the lock was released or not.
     */
    abstract protected function releaseLock($resourceId): bool;
}
