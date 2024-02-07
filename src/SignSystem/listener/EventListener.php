<?php

namespace SignSystem\listener;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use bedrockcloud\cloudbridge\objects\CloudServer;
use bedrockcloud\cloudbridge\objects\CloudServerState;
use pocketmine\block\BaseSign;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\event\player\PlayerMoveEvent;
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
                if ($sign->getFounder() == null || !array_key_exists($sign->getFounder(), CloudBridge::$cloudServer)) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                    return;
                }

                if (!CloudBridge::$cloudServer[$sign->getFounder()] instanceof CloudServer) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                    return;
                }

                $full = (CloudBridge::$cloudServer[$sign->getFounder()]->getPlayerCount() >= CloudBridge::$cloudServer[$sign->getFounder()]->getTemplate()->getMaxPlayer());
                if (CloudBridge::$cloudServer[$sign->getFounder()]->getState() == CloudServerState::NOT_REGISTERED) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                } elseif ($full) {
                    $player->sendMessage(SignSystem::getPrefix() . "§cThis server is full.");
                } elseif (CloudBridge::$cloudServer[$sign->getFounder()]->getTemplate()->isMaintenance()) {
                    if (!$player->hasPermission("cloud.maintenance.join")) {
                        $player->sendMessage(SignSystem::getPrefix() . "§cThis server is in maintenance.");
                    } else {
                        $this->transfer($player->getName(), $sign->getFounder());
                    }
                } else {
                    if (CloudBridge::$cloudServer[$sign->getFounder()]->getState() == CloudServerState::LOBBY){
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

    public function onMove(PlayerMoveEvent $event){
        $player = $event->getPlayer();
        if (!$player->isClosed() && $player->isAlive() && $player->isOnline()){
            $block = $player->getTargetBlock(5);
            $sign = SignSystem::getInstance()->getSignProvider()->getSignByPosition($block->getPosition());
            if ($sign instanceof GroupSign) {
                if ($block instanceof BaseSign) {
                    if ($sign->getFounder() == null || !array_key_exists($sign->getFounder(), CloudBridge::$cloudServer) || $sign->getState() ===  CloudServerState::NOT_REGISTERED) {
                        $player->sendTitle("§e" . $sign->getGroupName(), "§4No server found");
                    } elseif ($sign->getState() === CloudServerState::INGAME) {
                        $player->sendTitle("§e" . $sign->getFounder(), "§cThis server is ingame");
                    } elseif ($sign->getState() === CloudServerState::FULL) {
                        $player->sendTitle("§e" . $sign->getFounder(), "§6This server is full");
                    }
                }
            }
        }
    }
}