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

    const DATA_ITEM_SEP = " ";
    const DATA_LINE_SEP = "\n";
    const DATA_HEADER_SEP = "----------";

    function __construct($name, $userName, $x1, $y1, $z1, $x2, $y2, $z2, $regionsDataFolder) {
        $this->name = $name;
        $this->x1 = $x1;
        $this->y1 = $y1;
        $this->z1 = $z1;
        $this->x2 = $x2;
        $this->y2 = $y2;
        $this->z2 = $z2;
        $this->dataFolder = $regionsDataFolder . $name . "/";

        if (!is_dir($this->dataFolder)) {
            mkdir($this->dataFolder, 0755, true);
        }

        $this->write();
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
     * the Minecraft data from the map.  It will add the data file to Git if
     * it's not already tracked, and will commit a revision.
     *
     * @param string $data The data to write
     * @return boolean true if successful
     */
    private function write() {
        $data = $this->captureCurrentState();
        $filePath = $this->dataFolder . "data.txt";

        if (!is_file($filePath)) {
            $needsAdd = true;
        }

        file_put_contents($filePath, $data);

        if ($needsAdd) {
            GitTools::gitAdd($filePath);
            GitTools::gitCommit($filePath, "Initial commit of region '" . $this->name . "'");
        } else{
            GitTools::gitCommit($filePath, "Update to region '" . $this->name . "'");
        }

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

    /**
     * Collect the current state of the region from the server.
     *
     * @return string The data, starting with a header containing the region
     * name and bounding coordinates, followed by the data.
     */
    private function captureCurrentState() {
        $level = Server::getInstance()->getDefaultLevel();

        $data = $this->name . self::DATA_LINE_SEP;
        $data .= $this->x1 . self::DATA_ITEM_SEP . $this->y1 . self::DATA_ITEM_SEP . $this->z1 . self::DATA_ITEM_SEP . $this->x2 . self::DATA_ITEM_SEP . $this->y2 . self::DATA_ITEM_SEP . $this->z2 . self::DATA_LINE_SEP;
        $data .= self::DATA_HEADER_SEP . self::DATA_LINE_SEP;

        for ($y = min($this->y1, $this->y2); $y <= max($this->y1, $this->y2); $y++) {
            for ($x = min($this->x1, $this->x2); $x <= max($this->x1, $this->x2); $x++) {
                for ($z = min($this->z1, $this->z2); $z <= max($this->z1, $this->z2); $z++) {
                    $data .= $level->getBlock(new Vector3($x, $y, $z))->getId() . self::DATA_ITEM_SEP;
                }
                $data .= self::DATA_LINE_SEP;
            }
            $data .= self::DATA_LINE_SEP;
        }
        $data .= self::DATA_LINE_SEP;

        return $data;
    }
}
