<?php

namespace SignSystem\provider;

use SignSystem\objects\GroupSign;

class ServerProvider {

    private static array $usingServerNames = [];

    public static function addUsingServerName(string $name, GroupSign $groupSign): bool {
        if (!self::isUsingServerName($name)) return false;
        self::$usingServerNames[$name] = $groupSign;
        return true;
    }

    public static function removeUsingServerName(string $name): bool {
        if (!self::isUsingServerName($name)) return false;
        unset(self::$usingServerNames[array_search($name, self::$usingServerNames)]);
        return true;
    }

    public static function isUsingServerName(string $name): bool {
        return array_key_exists($name, self::$usingServerNames);
    }

    public static function getUsedServers(): array {
        return self::$usingServerNames;
    }
}