<?php

declare(strict_types=1);

namespace App\IoC\Scope;

use Fiber;
use SplObjectStorage;
use stdClass;

class ThreadLocalScopeStorage
{
    private static ?SplObjectStorage $storage = null;

    private static bool $locked = false;

    public static function get(): ScopeManager
    {
        self::lock();

        try {
            self::initStorage();

            $threadId = self::getThreadId();

            if (!self::$storage->contains($threadId)) {
                self::$storage[$threadId] = new ScopeManager();
            }

            return self::$storage[$threadId];
        } finally {
            self::unlock();
        }
    }

    public static function set(ScopeManager $scopeManager): void
    {
        self::lock();

        try {
            self::initStorage();

            $threadId = self::getThreadId();
            self::$storage[$threadId] = $scopeManager;
        } finally {
            self::unlock();
        }
    }

    public static function clear(): void
    {
        self::lock();

        try {
            self::initStorage();

            $threadId = self::getThreadId();
            if (self::$storage->contains($threadId)) {
                unset(self::$storage[$threadId]);
            }
        } finally {
            self::unlock();
        }
    }

    public static function clearAll(): void
    {
        self::lock();

        try {
            self::$storage = new SplObjectStorage();
        } finally {
            self::unlock();
        }
    }

    private static function initStorage(): void
    {
        if (self::$storage === null) {
            self::$storage = new SplObjectStorage();
        }
    }

    private static function getThreadId(): object
    {
        $fiber = Fiber::getCurrent();
        if ($fiber !== null) {
            return $fiber;
        }

        static $mainThread = null;
        if ($mainThread === null) {
            $mainThread = new stdClass();
        }

        return $mainThread;
    }

    private static function lock(): void
    {
        while (self::$locked) {
            usleep(1);
        }
        self::$locked = true;
    }

    private static function unlock(): void
    {
        self::$locked = false;
    }
}
