<?php

namespace SignSystem;

use bedrockcloud\cloudbridge\CloudBridge;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use SignSystem\commands\SignCommand;
use SignSystem\config\SignConfig;
use SignSystem\listener\EventListener;
use SignSystem\provider\SignProvider;

class SignSystem extends PluginBase {

    const PREFIX = "§l§bCloudSigns §r§8× §7";

    private static self $instance;
    public array $ignoredPlayers = [];
    private SignProvider $signProvider;
    public ?string $path = null;

    protected function onEnable(): void {
        self::$instance = $this;

        $this->saveResource("settings.yml");
        $config = new Config($this->getDataFolder() . "settings.yml", Config::YAML);
        if (!$config->exists("default_path")){
            Server::getInstance()->getLogger()->warning("§cNo default provided.");
            Server::getInstance()->shutdown();
            return;
        }

        if ($config->get("default_path") == "default"){
            $path = $this->getDataFolder();
        } else {
            $path = $config->get("default_path");
        }

        if (!is_dir($path)){
            $path = $this->getDataFolder();
            Server::getInstance()->getLogger()->error("§cProvided path isn't a directory, setting to default.");
        }

        if ($path == null){
            Server::getInstance()->getLogger()->warning("§cNo path provided.");
            Server::getInstance()->shutdown();
            return;
        }

        @mkdir($path);
        $this->path = $path;
        $this->signProvider = new SignProvider();
        $this->getServer()->getPluginManager()->registerEvents(new EventListener(), $this);
        $this->getServer()->getCommandMap()->register("signSystem", new SignCommand("sign", "Main SignSystem Command", "", ["signsystem"]));

        foreach (scandir($this->getServer()->getDataPath() . "worlds/") as $file) {
            if ($file == "." || $file == "..") continue;
            $this->getServer()->getWorldManager()->loadWorld($file);
        }
    }


    public function getSignProvider(): SignProvider {
        return $this->signProvider;
    }

    public static function getInstance(): SignSystem {
        return self::$instance;
    }

    public function getAllServer(): array {
        $services = [];
        foreach (CloudBridge::$gameServer as $gameServer) {
            $services[] = $gameServer->getName();
        }
        return $services;
    }

    public function getAllGroups(): array {
        $arr = [];
        foreach (CloudBridge::$gameServer as $gameServer){
            if (!in_array($gameServer->getCloudGroup()->getName(), $arr)){
                $arr[] = $gameServer->getCloudGroup()->getName();
            }
        }

        return $arr;
    }

    public static function getPrefix(): string {
        return self::PREFIX;
    }
}