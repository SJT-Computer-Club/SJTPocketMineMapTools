<?php

namespace Psycle\SJTMapTools;

/**
 * Manages editable regions or a world.  Allows allocation of a permit to edit a
 * region to a user, storing of data and loading of data.
 */
class RegionManager {
    private static $gitRepo = 'https://github.com/SJT-Computer-Club/SJTPocketMineMapRegions.git';
    private $plugin;
    private $dataFolder;
    
    /**
     * Constructor
     * 
     * @param SJTMapTools $plugin The parent plugin
     * @param String $dataFolder The plugin's data folder
     */
    function __construct($plugin, $dataFolder) {
        $this->plugin = $plugin;
        
        $this->dataFolder = $dataFolder;
        $this->parseDataFolder();
    }
    
    /**
     * Parse the contents of the data folder.  Loads all found regions.
     */
    private function parseDataFolder() {
        if (!is_dir($this->dataFolder)) {
            $this->plugin->getLogger()->info('The regions folder doesn\'t exist.  Cloning from ' . self::$gitRepo . ' …');
            GitTools::gitClone($this->plugin, $this->dataFolder, self::$gitRepo);
        }
    }
}
