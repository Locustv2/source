<?php
/**
 * @link https://github.com/Locustv2/source
 * @copyright Copyright (c) 2017 locustv2
 * @license https://github.com/Locustv2/source/blob/master/LICENSE.md
 * @author Yuv Joodhisty <locustv2@gmail.com>
 */

namespace source\mutex;

/**
 * FileMutex if the implementation of mutex using file locking mechanism.
 * When a resource needs to be locked, a temporary file will be created and
 * locked using php `flock` function.
 */
class FileMutex extends Mutex
{
    /**
     * @var array the list of currently opened and locked files to keep track
     * of locked resources.
     */
    protected $openedFiles = [];

    /**
     * @inheritdoc
     */
    protected function acquireLock($resourceId, int $timeout = 0): bool
    {
        $waitTime = 0;
        while (true) {
            $file = fopen($this->getLockFilePath($resourceId), 'w+');
            if ($file === false) {
                return false;
            }

            chmod($this->getLockFilePath($resourceId), 0755);
            if (flock($file, LOCK_EX | LOCK_NB)) {
                $this->openedFiles[$resourceId] = $file;
                break;
            }

            $waitTime++;
            if ($waitTime > $timeout) {
                return false;
            }

            fclose($file);
            sleep(1);
        }

        return true;
    }

    /**
     * @inheritdoc
     */
    protected function releaseLock($resourceId): bool
    {
        if (!isset($this->openedFiles[$resourceId]) || !flock($this->openedFiles[$resourceId], LOCK_UN)) {
            return false;
        }

        fclose($this->openedFiles[$resourceId]);
        unset($this->openedFiles[$resourceId]);
        unlink($this->getLockFilePath($resourceId));

        return true;
    }

    /**
     * Generates and returns the file path that will be used for the locking.
     * @param string $resourceId the resourceId that will be locked
     * @return string the path of the filed created for the locking.
     */
    protected function getLockFilePath($resourceId): string
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'runtime/mutex';
        if (!is_dir($path) && !mkdir($path, 0775, true)) {
            return false;
        }

        chmod(dirname($path), 0755);

        return $path . DIRECTORY_SEPARATOR . md5($resourceId) . '.lock';
    }
}
