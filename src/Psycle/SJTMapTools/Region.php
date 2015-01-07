<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Gold;
use pocketmine\level\Level;
use pocketmine\math\Vector3;
use pocketmine\Server;

/**
 * A container for a region of data in the map
 */
class Region {
    /**
     * The name of the region
     * @var type string
     */
    public $name;
    /**
     * The coordinates of the start and end points defining the region
     * @var type int
     */
    public $x1, $y1, $z1, $x2, $y2, $z2;
    /**
     * The path to the regions data folder
     * @var type string
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
        $level->setBlockIdAt((int)$this->x1, (int)$this->y1 + 1, (int)$this->z1, 1);
        $level->setBlock(new Vector3($this->x2, $this->y1, $this->z1), new Gold(), true);
        $level->setBlock(new Vector3($this->x1, $this->y2, $this->z1), new Gold(), true);
        $level->setBlock(new Vector3($this->x2, $this->y2, $this->z1), new Gold(), true);
        $level->setBlock(new Vector3($this->x1, $this->y1, $this->z2), new Gold(), true);
        $level->setBlock(new Vector3($this->x2, $this->y1, $this->z2), new Gold(), true);
        $level->setBlock(new Vector3($this->x1, $this->y2, $this->z2), new Gold(), true);
        $level->setBlock(new Vector3($this->x2, $this->y2, $this->z2), new Gold(), true);
    }
}
