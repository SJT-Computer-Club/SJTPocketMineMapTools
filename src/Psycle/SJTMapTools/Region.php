<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Block;
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
    /**
     * The username of the user who last edited the region
     * @var string
     */
    private $lastEditUsername;

    const DATA_ITEM_SEP = " ";
    const DATA_LINE_SEP = "\n";
    const DATA_HEADER_SEP = "----------";

    /**
     * Constructor.
     *
     * @param type $name The region name
     * @param type $regionsDataFolder The folder where regions are stored
     */
    protected function __construct($name, $regionsDataFolder) {
        $this->name = $name;
        $this->dataFolder = $regionsDataFolder . $name . "/";

        if (!is_dir($this->dataFolder)) {
            mkdir($this->dataFolder, 0755, true);
        }
    }

    /**
     * Create a Region instance using data from the world.  This is typically
     * used when a user creates a new region.  Take a snapshot of the current
     * state of the world and write them to file, creating a Git revision.
     *
     * @param type $name The region name
     * @param type $regionsDataFolder The folder where regions are stored
     * @param type $userName The user name
     * @param int $x1 The x coordinate of the region start
     * @param int $y1 The y coordinate of the region start
     * @param int $z1 The z coordinate of the region start
     * @param int $x2 The x coordinate of the region end
     * @param int $y2 The y coordinate of the region end
     * @param int $z2 The z coordinate of the region end
     * @return \self
     */
    static function fromWorld($name, $regionsDataFolder, $userName, $x1, $y1, $z1, $x2, $y2, $z2) {
        $instance = new self($name, $regionsDataFolder);

        $instance->x1 = $x1;
        $instance->y1 = $y1;
        $instance->z1 = $z1;
        $instance->x2 = $x2;
        $instance->y2 = $y2;
        $instance->z2 = $z2;

        $instance->write($userName);

        return $instance;
    }

    /**
     * Create a Region instance using data from file.  This is typically used
     * when loading regions on initial startup.
     *
     * @param type $name The region name
     * @param type $regionsDataFolder The folder where regions are stored
     * @return \self
     */
    static function fromFolder($name, $regionsDataFolder) {
        $instance = new self($name, $regionsDataFolder);

        $instance->read();

        return $instance;
    }

    /**
     * Convert to a string
     * 
     * @return string
     */
    function __toString() {
        return "Name: " . TextFormat::LIGHT_PURPLE . " " . $this->name .
                "\n    " . TextFormat::WHITE . "1st corner: [" . TextFormat::YELLOW . (int)$this->x1 . ", " . (int)$this->y1 . ", " . (int)$this->z1 . TextFormat::WHITE . "]" .
                "\n    " . TextFormat::WHITE . "2nd corner: [" . TextFormat::YELLOW . (int)$this->x2 . ", " . (int)$this->y2 . ", " . (int)$this->z2 . TextFormat::WHITE . "]";
    }

    /**
     * Read the region data from file
     */
    private function read() {
        $filePath = $this->dataFolder . "data.txt";
        if (!is_file($filePath)) { return; }

        $data = file_get_contents($filePath);

        $this->restoreCurrentState($data, true);
    }

    /**
     * Write our region data to file.  This writes both region metadata and
     * the Minecraft data from the map.  It will add the data file to Git if
     * it's not already tracked, and will commit a revision.
     *
     * @param string $userName The user name
     * @return boolean true if successful
     */
    public function write($userName) {
        $this->lastEditUsername = $userName;

        $data = $this->captureCurrentState();
        $filePath = $this->dataFolder . "data.txt";
        $needsAdd = !is_file($filePath);

        file_put_contents($filePath, $data);

        if ($needsAdd) {
            GitTools::gitAdd($filePath);
            GitTools::gitCommit($filePath, "Initial commit of region '" . $this->name . "' by " . $this->lastEditUsername);
        } else{
            GitTools::gitCommit($filePath, "Update to region '" . $this->name . "' by " . $this->lastEditUsername);
        }

        return true;
    }

    /**
     * Revert the region in game to the last saved state.
     *
     * @param string $userName The user name
     * @return boolean true if successful
     */
    public function revert($userName) {
        $this->lastEditUsername = $userName;

        $filePath = $this->dataFolder . "data.txt";
        $data = file_get_contents($filePath);

        $this->restoreCurrentState($data);

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
     * Collect the current state of the region from the world.
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

    /**
     * Restore the current state of the region from the passed data.
     *
     * @param string $data The data, starting with a header containing the region
     * @param boolean $metadataOnly If true, only load the metadata, not blocks
     * name and bounding coordinates, followed by the data.
     */
    private function restoreCurrentState($data, $metadataOnly = false) {
        $level = Server::getInstance()->getDefaultLevel();

        $lines = explode(self::DATA_LINE_SEP, $data);
        $currentLine = 0;

        $currentLine++; // Skip name line, we don't load that
        $coords = explode(self::DATA_ITEM_SEP, $lines[$currentLine]);
        $this->x1 = $coords[0]; $this->y1 = $coords[1]; $this->z1 = $coords[2];
        $this->x2 = $coords[3]; $this->y2 = $coords[4]; $this->z2 = $coords[5];

        if ($metadataOnly) { return; }

        $currentLine++;
        $currentLine++; // Skip header separator line

        for ($y = min($this->y1, $this->y2); $y <= max($this->y1, $this->y2); $y++) {
            for ($x = min($this->x1, $this->x2); $x <= max($this->x1, $this->x2); $x++) {
                $lineData = explode(self::DATA_ITEM_SEP, $lines[$currentLine]);
                $currentItem = 0;
                for ($z = min($this->z1, $this->z2); $z <= max($this->z1, $this->z2); $z++) {
                    $level->setBlock(new Vector3($x, $y, $z), new Block($lineData[$currentItem]));
                    $currentItem++;
                }
                $currentLine++;
            }
            $currentLine++;
        }
    }
}
