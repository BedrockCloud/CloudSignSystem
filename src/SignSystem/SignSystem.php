<?php

namespace SignSystem;

use bedrockcloud\cloudbridge\api\CloudAPI;
use bedrockcloud\cloudbridge\objects\CloudServerType;
use bedrockcloud\cloudbridge\objects\CloudTemplate;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\Config;
use pocketmine\utils\SingletonTrait;
use SignSystem\commands\SignCommand;
use SignSystem\listener\EventListener;
use SignSystem\provider\SignProvider;

class SignSystem extends PluginBase {
    use SingletonTrait{
        reset as private;
        setInstance as private;
    }

    const PREFIX = "§l§bCloudSigns §r§8× §7";

    private SignProvider $signProvider;
    private ?string $path = null;

    protected function onEnable(): void {
        self::setInstance($this);

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

        if (is_null($path)){
            Server::getInstance()->getLogger()->warning("§cNo path provided.");
            Server::getInstance()->shutdown();
            return;
        }

        Server::getInstance()->getLogger()->warning("§aSet path to §e{$path}§7.");

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

    public static function getPrefix(): string {
        return self::PREFIX;
    }

    /**
     * @return string|null
     */
    public function getPath(): ?string
    {
        return $this->path;
    }
}