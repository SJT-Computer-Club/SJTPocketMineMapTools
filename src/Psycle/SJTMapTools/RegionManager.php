<?php

namespace Psycle\SJTMapTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;


/**
 * Manages editable regions or a world.  Allows allocation of a permit to edit a
 * region to a user, storing of data and loading of data.
 */
class RegionManager {
    /**
     * The location of the Git repository containing region data
     * @var type string
     */
    private static $gitRepo = 'https://github.com/SJT-Computer-Club/SJTPocketMineMapRegions.git';
    /**
     * The data folder for storing regions
     * @var type string
     */
    private $dataFolder;
    /**
     * An associative array of all Region objects
     * @var type array of Regions
     */
    private $regions = array();
    /**
     * An associative array containing all regions that are in the process of being defined by users. key=username, value=array of start coordinates
     * @var type array
     */
    private $underway = array();
    
    /**
     *  Error codes for return values
     */
    const NO_ERROR = 0,
          ERROR_REGION_END_WITHOUT_START = -1,
          ERROR_REGION_EXISTS = -2,
          ERROR_REGION_ALREADY_STARTED = -3,
          ERROR_REGION_NOT_STARTED = -4;
    
    /**
     * Constructor
     * 
     * @param string $dataFolder The regions data folder
     */
    function __construct($dataFolder) {
        $this->dataFolder = $dataFolder;
        $this->parseDataFolder();
    }
    
    /**
     * Parse the contents of the data folder.  Loads all found regions.
     */
    private function parseDataFolder() {
        if (!is_dir($this->dataFolder)) {
            SJTMapTools::$instance->getLogger()->info('The regions folder doesn\'t exist.  Cloning from ' . self::$gitRepo . '…');
            GitTools::gitClone($this->dataFolder, self::$gitRepo);
        }
        
        // TODO load all regions
    }

    /**
     * Start defining a region for a user
     * 
     * @param string $userName The user's name
     * @param int $x The x coordinate of the region start
     * @param int $y The y coordinate of the region start
     * @param int $z The z coordinate of the region start
     * @return int NO_ERROR or an error code
     */
    public function startRegion($userName, $x, $y, $z) {
        if (array_key_exists($userName, $this->underway)) {
            return self::ERROR_REGION_ALREADY_STARTED;
        }
        
        $this->underway[$userName] = [$x, $y, $z];
        return self::NO_ERROR;
    }
    
    /**
     * Cancel the definition of a region
     * 
     * @param string $userName The user's name
     * @return int NO_ERROR or an error code
     */
    public function cancelRegion($userName) {
        if (!array_key_exists($userName, $this->underway)) {
            return self::ERROR_REGION_NOT_STARTED;
        }
        
        unset($this->underway[$userName]);
        return self::NO_ERROR;
    }
    
    /**
     * Finish defining a region for a user
     * 
     * @param string $userName The user's name
     * @param string $regionName The region's name
     * @param int $x The x coordinate of the region start
     * @param int $y The y coordinate of the region start
     * @param int $z The z coordinate of the region start
     * @return int NO_ERROR or an error code
     */
    public function endRegion($userName, $regionName, $x, $y, $z) {
        if (!array_key_exists($userName, $this->underway)) {
            return self::ERROR_REGION_END_WITHOUT_START;
        }
        if (array_key_exists($regionName, $this->regions)) {
            return self::ERROR_REGION_EXISTS;
        }

        $startData = $this->underway[$userName];
        unset($this->underway[$userName]);
        
        $this->regions[$regionName] = new Region($regionName, $userName, $startData[0], $startData[1], $startData[2], $x, $y, $z, $this->dataFolder);
        $this->regions[$regionName]->write(true);
        return self::NO_ERROR;
    }
    
    public function loadRegion($regionName) {
        return null;
    }
}
