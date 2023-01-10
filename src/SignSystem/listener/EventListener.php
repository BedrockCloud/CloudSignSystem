<?php

namespace SignSystem\listener;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\objects\GameServerState;
use pocketmine\block\BaseSign;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;
use SignSystem\objects\GroupSign;
use SignSystem\SignSystem;

class EventListener implements Listener {

    public function onInteract(PlayerInteractEvent $event)
    {
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $sign = SignSystem::getInstance()->getSignProvider()->getSignByPosition($block->getPosition());
        if ($sign instanceof GroupSign) {
            if ($block instanceof BaseSign) {
                $line = $block->getText()->getLine(2);
                if ($sign->getFounder() == null || !isset(CloudBridge::$gameServer[$sign->getFounder()])) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                    return;
                }

                $full = (CloudBridge::$gameServer[$sign->getFounder()]->getPlayerCount() >= CloudBridge::$gameServer[$sign->getFounder()]->getCloudGroup()->getMaxPlayer());
                if (CloudBridge::$gameServer[$sign->getFounder()]->getState() == GameServerState::NOT_REGISTERED) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                } elseif ($full) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cThis server is full.");
                } elseif (CloudBridge::$gameServer[$sign->getFounder()]->getCloudGroup()->isMaintenance()) {
                    if (!$player->hasPermission("cloud.maintenance.join")) {
                        $player->sendMessage(SignSystem::getPrefix() . "§cThis server is in maintenance.");
                    } else {
                        $this->transfer($player->getName(), $sign->getFounder());
                    }
                } else {
                    if (CloudBridge::$gameServer[$sign->getFounder()]->getState() == GameServerState::LOBBY){
                        $this->transfer($player->getName(), $sign->getFounder());
                    }
                }
            }
        }
    }

    public function transfer(string $name, string $toserver){
        $pk = new PlayerMovePacket();
        $pk->playerName = $name;
        $pk->toServer = $toserver;
        $pk->sendPacket();
    }
}