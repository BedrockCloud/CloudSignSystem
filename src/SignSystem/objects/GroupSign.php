<?php

namespace SignSystem\objects;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\GameServerState;
use pocketmine\world\Position;
use SignSystem\provider\ServerProvider;
use SignSystem\utils\Utils;

class GroupSign {

    const SEARCH = 0;
    const MAINTENANCE = 1;

    private string $groupName;
    private Position $position;
    private int $state;
    private NULL|String $founder;
    private int $currentFormatIndex = 0;

    public function __construct(string $groupName, Position $position, bool $maintenance=false) {
        $this->groupName = $groupName;
        $this->position = $position;
        $this->state = $maintenance ? self::MAINTENANCE : self::SEARCH;
        $this->founder = null;
    }

    public function nextFormatIndex(): array {
        $format = $this->getFormat();
        $formatIndex = [];
        if (isset($format[$this->currentFormatIndex])) {
            $formatIndex = $this->replaceFormatIndex($format[$this->currentFormatIndex]);
            $this->currentFormatIndex++;
        } else {
            $this->currentFormatIndex = 0;
            if (isset($format[$this->currentFormatIndex])) {
                $formatIndex = $this->replaceFormatIndex($format[$this->currentFormatIndex]);
                $this->currentFormatIndex++;
            }
        }
        return $formatIndex;
    }

    public function getFormat()
    {
        $format = Utils::$SignLayout["SignFormat"]["Lobby"];

        if (is_null($this->getFounder())) {
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        if (!isset(CloudBridge::$gameServer[$this->getFounder()])) {
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        if (CloudBridge::$gameServer[$this->getFounder()]->getState() == GameServerState::INGAME || CloudBridge::$gameServer[$this->getFounder()]->getState() == GameServerState::NOT_REGISTERED){
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        if (!isset(CloudBridge::$gameServer[$this->getFounder()])) {
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        $full = (CloudBridge::$gameServer[$this->getFounder()]->getPlayerCount() >= CloudBridge::$gameServer[$this->getFounder()]->getCloudGroup()->getMaxPlayer());
        if ($full){
            return Utils::$SignLayout["SignFormat"]["Full"];
        }

        if (CloudBridge::$gameServer[$this->getFounder()]->getCloudGroup()->isMaintenance()){
            return Utils::$SignLayout["SignFormat"]["Maintenance"];
        }

        if (CloudBridge::$gameServer[$this->getFounder()]->getCloudGroup()->isBeta()){
            return Utils::$SignLayout["SignFormat"]["Beta"];
        }
        return $format;
    }

    private function replaceFormatIndex(array $formatIndex): array {
        $newFormatIndex = [];
        foreach ($formatIndex as $str) {
            if (is_array($str)) continue;
            $newFormatIndex[] = str_replace(
                ["&", "%server%", "%players%", "%max_players%", "%template%"],
                [
                    "§",
                    ($this->getFounder() ?? $this->getGroupName()),
                    ($this->getFounder() !== null ? CloudBridge::$gameServer[$this->getFounder()]->getPlayerCount() : 0),
                    ($this->getFounder() !== null ? CloudBridge::$gameServer[$this->getFounder()]->getCloudGroup()->getMaxPlayer() : 0),
                    ($this->getGroupName()),
                ],
                $str
            );
        }
        return $newFormatIndex;
    }

    /**
     * @return int
     */
    public function getCurrentFormatIndex(): int {
        return $this->currentFormatIndex;
    }

    /**
     * @return string
     */
    public function getGroupName(): string {
        return $this->groupName;
    }

    /**
     * @return Position
     */
    public function getPosition(): Position {
        return $this->position;
    }

    /**
     * @return int
     */
    public function getState(): int {
        return $this->state;
    }

    /**
     * @return String|null
     */
    public function getFounder(): ?string {
        return $this->founder;
    }

    /**
     * @param String|null $founder
     */
    public function setFounder(?string $founder): void
    {
        $this->founder = $founder;
    }
}