<?php

namespace SignSystem\listener;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\network\packet\PlayerMovePacket;
use pocketmine\block\BaseSign;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\utils\TextFormat;
use SignSystem\objects\GroupSign;
use SignSystem\SignSystem;

class EventListener implements Listener {

    public function onInteract(PlayerInteractEvent $event){
        $block = $event->getBlock();
        $player = $event->getPlayer();
        $sign = SignSystem::getInstance()->getSignProvider()->getSignByPosition($block->getPosition());
        if ($sign instanceof GroupSign){
            if ($block instanceof BaseSign) {
                if ($sign->getFounder() != null){
                    if (isset(CloudBridge::$gameServer[$sign->getFounder()])){
                        $line = $block->getText()->getLine(2);
                        if (TextFormat::clean($line) == "[FULL]"){
                            $player->sendMessage(SignSystem::getPrefix() . "§cThis server is full.");
                            return;
                        } elseif (TextFormat::clean($line) == "[MAINTENANCE]"){
                            if (!$player->hasPermission("cloud.join.maintenance")){
                                $player->sendMessage(SignSystem::getPrefix() . "§cThis server is in maintenance.");
                            }
                        } else if (TextFormat::clean($line) == "free server..."){
                            $player->sendMessage(SignSystem::getPrefix() . "§cNo free server found.");
                            return;
                        }

                        $pk = new PlayerMovePacket();
                        $pk->playerName = $player->getName();
                        $pk->toServer = $sign->getFounder();
                        $pk->sendPacket();
                        $event->cancel();
                    }
                }
            }
        }
    }
}