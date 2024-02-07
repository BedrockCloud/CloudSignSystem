<?php

namespace SignSystem\commands;

use pocketmine\block\BaseSign;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\lang\Translatable;
use pocketmine\player\Player;
use SignSystem\SignSystem;
use SignSystem\utils\Utils;

class SignCommand extends Command {

    public function __construct(string $name, Translatable|string $description = "", Translatable|string|null $usageMessage = null, array $aliases = [])
    {
        parent::__construct($name, $description, $usageMessage, $aliases);
        $this->setPermission("cloudsigns.admin");
    }

    public function execute(CommandSender $sender, string $commandLabel, array $args): bool {
        if ($sender instanceof Player) {
            if ($this->testPermission($sender)) {
                if (isset($args[0])) {
                    if (strtolower($args[0]) == "create") {
                        if (isset($args[1])) {
                            //Note: Checks if the group exists
                            if (in_array($groupName = $args[1], Utils::getAllTemplates())) {
                                /** @var BaseSign $block */
                                if (($block = $sender->getTargetBlock(5)) instanceof BaseSign) {
                                    if (SignSystem::getInstance()->getSignProvider()->getSignByPosition($block->getPosition()) === null) {
                                        try {
                                            SignSystem::getInstance()->getSignProvider()->addSign($groupName, $block->getPosition());
                                        } catch (\JsonException $e) {
                                            SignSystem::getInstance()->getLogger()->logException($e);
                                        }
                                    } else {
                                        $sender->sendMessage(SignSystem::getPrefix() . "§cAt this position is already a sign!");
                                    }
                                } else {
                                    $sender->sendMessage(SignSystem::getPrefix() . "§cPlease look at a sign!");
                                }
                            } else {
                                $sender->sendMessage(SignSystem::getPrefix() . "§cYou cannot create a GroupSign with this group!");
                            }
                        } else {
                            $sender->sendMessage(SignSystem::getPrefix() . "§c/sign create <groupName>");
                        }
                    } else if (strtolower($args[0]) == "remove") {
                        /** @var BaseSign $block */
                        if (($block = $sender->getTargetBlock(5)) instanceof BaseSign) {
                            if (SignSystem::getInstance()->getSignProvider()->getSignByPosition($block->getPosition()) != null) {
                                try {
                                    SignSystem::getInstance()->getSignProvider()->removeSign($block->getPosition());
                                } catch (\JsonException $e) {
                                    SignSystem::getInstance()->getLogger()->logException($e);
                                }
                            } else {
                                $sender->sendMessage(SignSystem::getPrefix() . "§cAt this position is not a sign!");
                            }
                        } else {
                            $sender->sendMessage(SignSystem::getPrefix() . "§cPlease look at a sign!");
                        }
                    } else if (strtolower($args[0]) == "list") {
                        $list = SignSystem::getPrefix() . "§r\n";
                        if (count(SignSystem::getInstance()->getSignProvider()->getSigns()) > 0) {
                            foreach (SignSystem::getInstance()->getSignProvider()->getSigns() as $sign) {
                                if ($sign != null) {
                                    $founder = $sign->getFounder() ?? "§cNo Server";
                                    $position = Utils::PositionToString($sign->getPosition());
                                    $list .= "§fCurrent Server: §a{$founder}§r | §fCurrent Group: §a{$sign->getGroupName()}§r | §fPosition: §a{$position}§r\n";
                                }
                            }
                        } else {
                            $list .= "§cNo Signs registered.";
                        }

                        $sender->sendMessage($list);
                    } else {
                        $sender->sendMessage(SignSystem::getPrefix() . "§c/sign create <groupName>");
                        $sender->sendMessage(SignSystem::getPrefix() . "§c/sign remove");
                        $sender->sendMessage(SignSystem::getPrefix() . "§c/sign list");
                    }
                } else {
                    $sender->sendMessage(SignSystem::getPrefix() . "§c/sign create <groupName>");
                    $sender->sendMessage(SignSystem::getPrefix() . "§c/sign remove");
                    $sender->sendMessage(SignSystem::getPrefix() . "§c/sign list");
                }
            } else {
                $sender->sendMessage(SignSystem::getPrefix() . "§cYou do not have the permission to use this command!");
            }
        }
        return true;
    }
}