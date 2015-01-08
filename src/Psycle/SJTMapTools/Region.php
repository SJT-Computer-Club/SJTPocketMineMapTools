<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Gold;
use pocketmine\math\Vector3;
use pocketmine\Server;
use pocketmine\utils\TextFormat;

/**
 * A container for a region of data in the map
 */
class Region {
    /**
     * The name of the region
     * @var string
     */
    public $name;
    /**
     * The coordinates of the start and end points defining the region
     * @var int
     */
    public $x1, $y1, $z1, $x2, $y2, $z2;
    /**
     * The path to the regions data folder
     * @var string
     */
    private $dataFolder;

    function __construct($name, $userName, $x1, $y1, $z1, $x2, $y2, $z2, $dataFolder) {
        print('Created region: ' . $name . ' ' . $userName . ' ' .  $x1 . ' ' .  $y1 . ' ' .  $z1 . ' ' .  $x2 . ' ' .  $y2 . ' ' .  $z2);
        $this->name = $name;
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->z1 = $z1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->z2 = $z2;
        $this->dataFolder = $dataFolder;
        // TODO store initial data in object
    }

    /**
     * Convert to a string
     * @return string
     */
    function __toString() {
        return "Name: " . TextFormat::LIGHT_PURPLE . " " . $this->name .
                "\n    " . TextFormat::WHITE . "1st corner: [" . TextFormat::YELLOW . (int)$this->x1 . ", " . (int)$this->y1 . ", " . (int)$this->z1 . TextFormat::WHITE . "]" .
                "\n    " . TextFormat::WHITE . "2nd corner: [" . TextFormat::YELLOW . (int)$this->x2 . ", " . (int)$this->y2 . ", " . (int)$this->z2 . TextFormat::WHITE . "]";
    }

    /**
     * Write our region data to disk.  This writes both region metadata and
     * the Minecraft data from the map.
     *
     * @param boolean $createRevision If true, also push to Git
     * @return boolean true if successful
     */
    public function write($createRevision) {
        // TODO write region metadata
        // TODO write region Minecraft data
        return true;
    }

    /**
     * Read our region data from disk.  This reads the region metadata and
     * the Minecraft data into the map.
     *
     * @return boolean true if successful
     */
    public function read() {
        // TODO read region metadata
        // TODO read region Minecraft data
        return true;
    }

    /**
     * Draw marker blocks at the limits of the region
     */
    public function drawMarkers() {
        $level = Server::getInstance()->getDefaultLevel();
        $level->setBlock(new Vector3($this->x1, $this->y1, $this->z1), new Gold());
        $level->setBlock(new Vector3($this->x2, $this->y1, $this->z1), new Gold());
        $level->setBlock(new Vector3($this->x1, $this->y2, $this->z1), new Gold());
        $level->setBlock(new Vector3($this->x2, $this->y2, $this->z1), new Gold());
        $level->setBlock(new Vector3($this->x1, $this->y1, $this->z2), new Gold());
        $level->setBlock(new Vector3($this->x2, $this->y1, $this->z2), new Gold());
        $level->setBlock(new Vector3($this->x1, $this->y2, $this->z2), new Gold());
        $level->setBlock(new Vector3($this->x2, $this->y2, $this->z2), new Gold());
    }
}
