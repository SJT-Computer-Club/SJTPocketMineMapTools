<?php

namespace Psycle\SJTMapTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Manages editable regions or a world.  Allows allocation of a permit to edit a
 * region to a user, storing of data and loading of data.
 */
class RegionManager {
    private static $gitRepo = 'https://github.com/SJT-Computer-Club/SJTPocketMineMapRegions.git';
    private $plugin;
    private $dataFolder;
    private $regions = array();
    
    /**
     * Constructor
     * 
     * @param SJTMapTools $plugin The parent plugin
     * @param string $dataFolder The plugin's data folder
     */
    function __construct(SJTMapTools $plugin, $dataFolder) {
        $this->plugin = $plugin;
        
        $this->dataFolder = $dataFolder;
        $this->parseDataFolder();
    }
    
    /**
     * Parse the contents of the data folder.  Loads all found regions.
     */
    private function parseDataFolder() {
        if (!is_dir($this->dataFolder)) {
            $this->plugin->getLogger()->info('The regions folder doesn\'t exist.  Cloning from ' . self::$gitRepo . '…');
            GitTools::gitClone($this->plugin, $this->dataFolder, self::$gitRepo);
        }
    }

    public function createRegion($x1, $z1, $y1, $x2, $z2, $y2, $name) {
        if (array_key_exists($name, $this->regions)) {
            return false;
        }
    }
    
    public function loadRegion($name) {
        return null;
    }
}
