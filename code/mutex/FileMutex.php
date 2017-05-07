<?php

namespace source\mutex;

class FileMutex extends Mutex
{
    protected $openedFiles = [];

    protected function acquireLockxxx($resourceId, int $timeout = 0): bool
    {
        $file = fopen($this->getLockFilePath($resourceId), 'w+');
        if ($file === false) {
            return false;
        }

        chmod($this->getLockFilePath($resourceId), 0755);

        $waitTime = 0;
        while (!flock($file, LOCK_EX | LOCK_NB)) {
            sleep(1);
            $waitTime++;
            if ($waitTime > $timeout) {
                fclose($file);

                return false;
            }
        }

        $this->openedFiles[$resourceId] = $file;

        return true;
    }

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

    protected function getLockFilePath($name): string
    {
        $path = dirname(__DIR__, 2) . DIRECTORY_SEPARATOR . 'runtime/mutex';
        if (!is_dir($path) && !mkdir($path, 0775, true)) {
            return false;
        }

        chmod(dirname($path), 0755);

        return $path . DIRECTORY_SEPARATOR . md5($name) . '.lock';
    }
}
