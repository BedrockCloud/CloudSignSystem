<?php

namespace SignSystem\task;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\GameServerState;
use pocketmine\block\BaseSign;
use pocketmine\block\utils\SignText;
use pocketmine\scheduler\Task;
use SignSystem\objects\GroupSign;
use SignSystem\provider\ServerProvider;
use SignSystem\SignSystem;
use SignSystem\utils\Utils;

class SignTask extends Task {

    public function onRun(): void {
        foreach (SignSystem::getInstance()->getSignProvider()->getSigns() as $sign) {
            if($sign instanceof GroupSign) {
                /** @var BaseSign $block */
                if (($block = $sign->getPosition()->getWorld()->getBlock($sign->getPosition())) instanceof BaseSign) {
                    if ($sign->getFounder() !== null) {
                        if (isset(CloudBridge::$gameServer[$sign->getFounder()]) && $sign->getFormat() != Utils::$SignLayout["SignFormat"]["Offline"]) {
                            if (in_array($sign->getFounder(), CloudBridge::$gameServer)) {
                                if (!ServerProvider::isUsingServerName($sign->getFounder())) ServerProvider::addUsingServerName($sign->getFounder(), $sign);
                            } else {
                                if (ServerProvider::isUsingServerName($sign->getFounder())) ServerProvider::removeUsingServerName($sign->getFounder());
                            }
                            $block->setText(new SignText($sign->nextFormatIndex()));
                            $block->getPosition()->getWorld()->setBlock($block->getPosition(), $block, false);
                        } else {
                            if (ServerProvider::isUsingServerName($sign->getFounder())) ServerProvider::removeUsingServerName($sign->getFounder());
                            $sign->setFounder(null);
                        }
                    } else {
                        $freeServer = $this->getFreeServer($sign->getGroupName());
                        if ($freeServer !== "" && $freeServer != null) {
                            ServerProvider::addUsingServerName($freeServer, $sign);
                            $sign->setFounder($freeServer);
                        }
                        $block->setText(new SignText($sign->nextFormatIndex()));
                        $block->getPosition()->getWorld()->setBlock($block->getPosition(), $block, false);
                    }
                }
            }
        }
    }

    private function getFreeServer(string $groupName): ?string {
        $server = "";
        foreach (CloudBridge::$gameServer as $serverName) {
            if (!str_starts_with($serverName->getName(), $groupName)) continue;
            if (!isset(CloudBridge::$gameServer[$serverName->getName()])) continue;
            if (CloudBridge::$gameServer[$serverName->getName()]->getState() == GameServerState::INGAME || CloudBridge::$gameServer[$serverName->getName()]->getState() == GameServerState::NOT_REGISTERED) continue;
            if (!ServerProvider::isUsingServerName($serverName->getName())) {
                $server = $serverName->getName();
            }
        }
        return $server;
    }
}