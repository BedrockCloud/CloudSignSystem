<?php

namespace SignSystem\objects;

use bedrockcloud\cloudbridge\CloudBridge;
use bedrockcloud\cloudbridge\objects\CloudServer;
use bedrockcloud\cloudbridge\objects\CloudServerState;
use pocketmine\world\Position;
use SignSystem\provider\ServerProvider;
use SignSystem\utils\Utils;

class GroupSign {

    const SEARCH = 0;
    const MAINTENANCE = 1;

    private string $groupName;
    private Position $position;
    private null|string $founder;
    private int $currentFormatIndex = 0;

    public function __construct(string $groupName, Position $position) {
        $this->groupName = $groupName;
        $this->position = $position;
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

        if (!isset(CloudBridge::$cloudServer[$this->getFounder()])) {
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        if (!CloudBridge::$cloudServer[$this->getFounder()] instanceof CloudServer) {
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        if (CloudBridge::$cloudServer[$this->getFounder()]->getState() == CloudServerState::INGAME || CloudBridge::$cloudServer[$this->getFounder()]->getState() == CloudServerState::NOT_REGISTERED){
            if (ServerProvider::isUsingServerName($this->getFounder())) ServerProvider::removeUsingServerName($this->getFounder());
            $this->setFounder(null);
            return Utils::$SignLayout["SignFormat"]["Offline"];
        }

        $full = (CloudBridge::$cloudServer[$this->getFounder()]->getPlayerCount() >= CloudBridge::$cloudServer[$this->getFounder()]->getTemplate()->getMaxPlayer());
        if ($full){
            return Utils::$SignLayout["SignFormat"]["Full"];
        }

        if (CloudBridge::$cloudServer[$this->getFounder()]->getTemplate()->isMaintenance()){
            return Utils::$SignLayout["SignFormat"]["Maintenance"];
        }

        if (CloudBridge::$cloudServer[$this->getFounder()]->getTemplate()->isBeta()){
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
                    ($this->getFounder() !== null ? CloudBridge::$cloudServer[$this->getFounder()]->getPlayerCount() : 0),
                    ($this->getFounder() !== null ? CloudBridge::$cloudServer[$this->getFounder()]->getTemplate()->getMaxPlayer() : 0),
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
        return CloudBridge::$cloudServer[$this->getFounder()]->getState();
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