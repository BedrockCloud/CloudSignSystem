<?php

namespace SignSystem\utils;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\objects\CloudServerType;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use pocketmine\world\Position;

class Utils{

    /*
     * Designed by r3pt1s (PocketCloudSystem)
     */
    public static array $SignLayout = [
        "reload" => 1,
        "SignFormat" => [
            "Lobby" => [ //lobby
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§aLOBBY§8]", "§eO§7oooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§aLOBBY§8]", "§7o§eO§7ooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§aLOBBY§8]", "§7oo§eO§7oo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§aLOBBY§8]", "§7ooo§eO§7o"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§aLOBBY§8]", "§7oooo§eO§7"]
            ],
            "Full" => [ //full
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cFULL§8]", "§eO§7oooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cFULL§8]", "§7o§eO§7ooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cFULL§8]", "§7oo§eO§7oo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cFULL§8]", "§7ooo§eO§7o"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cFULL§8]", "§7oooo§eO§7"]
            ],
            "Maintenance" => [ //maintenance
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cMAINTENANCE§8]", "§eO§7oooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cMAINTENANCE§8]", "§7o§eO§7ooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cMAINTENANCE§8]", "§7oo§eO§7oo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cMAINTENANCE§8]", "§7ooo§eO§7o"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§cMAINTENANCE§8]", "§7oooo§eO§7"]
            ],
            "Beta" => [ //beta
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§bBETA§8]", "§eO§7oooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§bBETA§8]", "§7o§eO§7ooo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§bBETA§8]", "§7oo§eO§7oo"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§bBETA§8]", "§7ooo§eO§7o"],
                ["§l%server%", "§e%players%§8/§e%max_players%", "§8[§bBETA§8]", "§7oooo§eO§7"]
            ],
            "Offline" => [ //offline
                ["§l%template%", "§cSearching for", "§cfree server...", "§eO§7oooo"],
                ["§l%template%", "§cSearching for", "§cfree server...", "§7o§eO§7ooo"],
                ["§l%template%", "§cSearching for", "§cfree server...", "§7oo§eO§7oo"],
                ["§l%template%", "§cSearching for", "§cfree server...", "§7ooo§eO§7o"],
                ["§l%template%", "§cSearching for", "§cfree server...", "§7oooo§eO§7"]
            ]
        ]
    ];

    public static function PositionToString(Position $position): string{
        return "{$position->getX()}, {$position->getY()}, {$position->getZ()}";
    }

    public static function getAllTemplates(): array {
        $templates = [];
        foreach (CloudAPI::getInstance()->getCloudTemplates() as $template){
            if ($template instanceof CloudTemplate && $template->getType() === CloudServerType::$TYPE_SERVER) {
                if (!in_array($template->getName(), $templates)) {
                    $templates[] = $template->getName();
                }
            }
        }

        return $templates;
    }
}