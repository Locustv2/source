<?php
/**
 * @link https://github.com/Locustv2/source
 * @copyright Copyright (c) 2017 locustv2
 * @license https://github.com/Locustv2/source/blob/master/LICENSE.md
 * @author Yuv Joodhisty <locustv2@gmail.com>
 */

namespace source\mutex;

abstract class Mutex
{
    protected static $instance = null;

    protected static function getInstance(): self
    {
        if(!static::$instance) {
            static::$instance = new static();
        }
        return static::$instance;
    }

    public static function exec($resourceId, \Closure $closure, int $timeout = 0)
    {
        $instance = static::getInstance();
        $output = null;

        if($instance->acquireLock($resourceId, $timeout)) {
            $output = $closure($instance);
            $instance->releaseLock($resourceId);
        }
        return $output;
    }

    abstract protected function acquireLock($resourceId, int $timeout = 0): bool;

    abstract protected function releaseLock($resourceId): bool;
}
