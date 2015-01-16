<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Block;
use pocketmine\Server;


/**
 * Manages editable regions or a world.  Allows allocation of a permit to edit a
 * region to a user, storing of data and loading of data.
 */
class RegionManager {
    /**
     * The location of the Git repository containing region data
     * @var string
     */
    private $gitRepo;
    /**
     * The data folder for storing regions
     * @var string
     */
    private $dataFolder;
    /**
     * An associative array of all Region objects. key=regionname, value=Region
     * @var array of Regions
     */
    private $regions = array();
    /**
     * An associative array containing all regions that are in the process of
     * being defined by users. key=username, value=array of start coordinates
     * @var array
     */
    private $underway = array();

    /**
     *  Error codes for return values
     */
    const NO_ERROR = 0,
          ERROR_REGION_END_WITHOUT_START = -1,
          ERROR_REGION_EXISTS = -2,
          ERROR_REGION_ALREADY_STARTED = -3,
          ERROR_REGION_NOT_STARTED = -4,
          ERROR_REGION_DOESNT_EXIST = -5,
          ERROR_PERMIT_ALREADY_ISSUED = -6,
          ERROR_PERMIT_NOT_ASSIGNED_TO_USER = -7;

    /**
     * Constructor
     *
     * @param string $dataFolder The regions data folder
     */
    function __construct($dataFolder, $gitRepo) {
        $this->dataFolder = $dataFolder;
        $this->gitRepo = $gitRepo;
        $this->parseDataFolder();
    }

    /**
     * Perform regular actions.  Currently redraws any rubber bands for users
     * currently defining regions.
     */
    function tick() {
        foreach ($this->underway as $userName => $data) {
            $player = Server::getInstance()->getPlayer($userName);
            $rubberBander = $data[3];

            if (is_null($player)) {
                $this->cancelRegion($userName);
                continue;
            }

            $rubberBander->plot($player->x, $player->y, $player->z);
        }
    }

    /**
     * Parse the contents of the data folder.  Loads all found regions.
     */
    private function parseDataFolder() {
        if (!is_dir($this->dataFolder)) {
            if ($this->gitRepo == "" ) {
                SJTMapTools::getInstance()->getLogger()->info('The regions folder doesn\'t exist. No git repo configured, creating a local repo…');
                mkdir($this->dataFolder, 0755, true);
                GitTools::gitCreate($this->dataFolder);
            } else {
                SJTMapTools::getInstance()->getLogger()->info('The regions folder doesn\'t exist. Cloning from ' . $this->gitRepo . '…');
                GitTools::gitClone($this->dataFolder, $this->gitRepo);
            }
        }

        $directories = scandir($this->dataFolder);

        foreach ($directories as $directory) {
            if ($directory[0] === '.') { continue; }
            $region = Region::fromFolder($directory, $this->dataFolder);
            $this->regions[$region->name] = $region;
            $region->drawMarkers();
        }
    }

    /**
     * Get a list of all regions as a string
     */
    public function listRegions() {
        $result = "";
        foreach ($this->regions as $region) {
            $result .= (string)$region . "\n";
        }
        return $result;
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

        $this->underway[$userName] = [$x, $y, $z, new RubberBander($x, $y, $z)];
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

        $rubberBander = $this->underway[$userName][3];
        $rubberBander->stop();
        unset($this->underway[$userName]);

        return self::NO_ERROR;
    }

    /**
     * Cancel all regions currently being defined
     */
    public function cancelAllRegions() {
        foreach ($this->underway as $userName => $data) {
            $this->cancelRegion($userName);
        }
    }

    /**
     * Finish defining a region for a user
     *
     * @param string $userName The user's name
     * @param string $regionName The region's name
     * @param int $x The x coordinate of the region end
     * @param int $y The y coordinate of the region end
     * @param int $z The z coordinate of the region end
     * @return int NO_ERROR or an error code
     */
    public function endRegion($userName, $regionName, $x, $y, $z) {
        if (!array_key_exists($userName, $this->underway)) {
            return self::ERROR_REGION_END_WITHOUT_START;
        }
        if (array_key_exists($regionName, $this->regions)) {
            return self::ERROR_REGION_EXISTS;
        }

        $data = $this->underway[$userName];
        $rubberBander = $data[3];
        $rubberBander->stop();
        unset($this->underway[$userName]);

        $region = Region::fromWorld($regionName, $this->dataFolder, $userName, $data[0], $data[1], $data[2], $x, $y, $z);
        $this->regions[$regionName] = $region;
        $region->drawMarkers();

        return self::NO_ERROR;
    }

    /**
     * Get a region by name
     *
     * @param string $regionName The name of the region
     * @return Region the Region, or NULL if no region found
     */
    public function getRegion($regionName) {
        if (!array_key_exists($regionName, $this->regions)) {
            return NULL;
        } else {
            return $this->regions[$regionName];
        }
    }


    /**
     * Request a permit to edit a region
     *
     * @param string $userName The user's name
     * @param string $regionName The region name
     * @return int NO_ERROR or an eror code
     */
    public function requestPermit($userName, $regionName) {
        $region = $this->getRegion($regionName);
        if (is_null($region)) {
            return self::ERROR_REGION_DOESNT_EXIST;
        }
        if (!is_null($region->getPermitUserName())) {
            return self::ERROR_PERMIT_ALREADY_ISSUED;
        }

        $region->requestPermit($userName);
        return self::NO_ERROR;
    }

    /**
     * Request the release of a permit
     *
     * @param string $userName The user's name
     * @param string $regionName The region name
     * @return int NO_ERROR or an error code
     */
    public function releasePermit($userName, $regionName) {
        $region = $this->getRegion($regionName);
        if (is_null($region)) {
            return self::ERROR_REGION_DOESNT_EXIST;
        }

        if (!$region->releasePermit($userName)) {
            return self::ERROR_PERMIT_NOT_ASSIGNED_TO_USER;
        }

        return self::NO_ERROR;
    }

    /**
     * Check whether a user can edit a block
     *
     * @param string $userName The user's name
     * @param Block The block the user is attempting to edit
     * @return boolean true if block can be edited by the user
     */
    public function canEditBlock($userName, $block) {
        foreach ($this->regions as $region) {
            if ($region->getPermitUserName() !== $userName) { continue; }
            if ($region->isBlockInside($block)) { return true; }
        }
        return false;
    }
}
