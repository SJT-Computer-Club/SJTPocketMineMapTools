<?php

namespace Psycle\SJTMapTools;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Main plugin class
 */
class SJTMapTools extends PluginBase {

    private $regionManager;
    private $dataFolder;
    
    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        $this->getLogger()->info('Plugin Enabled');
        $this->initDataFolder();
        $this->regionManager = new RegionManager($this, $this->dataFolder . 'regions/');
    }

    /**
     * Called when the plugin is disabled
     */
    public function onDisable() {
        $this->getLogger()->info('Plugin Disabled');
    }


    /* Data handling */
    private function initDataFolder() {
        $this->dataFolder = $this->getDataFolder() . 'data/';
        if (!is_dir($this->dataFolder)) {
            $this->getLogger()->info('Data folder not found, creating at: ' . $this->dataFolder);
            mkdir($this->dataFolder, 0755, true);
        }
    }
    
    /* Command handling */
    
    /**
     * Handle a command from a player
     * 
     * @param CommandSender $sender The command sender object
     * @param Command $command The command object
     * @param type $label
     * @param array $args The command arguments
     * @return boolean
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if (strtolower($command->getName()) === 'startregion') {
            $this->getLogger()->info('Starting a region');
            return $this->startRegion($sender, $args);
        } elseif (strtolower($command->getName()) === 'endregion') {
            $this->getLogger()->info('Ending a region');
            return $this->endRegion($sender, $args);
        } elseif (strtolower($command->getName()) === 'tptoregion') {
            $this->getLogger()->info('Teleporting to a region');
            return $this->teleportToRegion($sender, $args);
        } elseif (strtolower($command->getName()) === 'deleteregion') {
            $this->getLogger()->info('Deleting a region');
            return true;
        }
        
        return false;
    }

    /**
     * Start defining the region from the player's current location.
     * 
     * @param CommandSender $sender The command sender object
     */
    private function startRegion(CommandSender $sender, array $args) {
        return true;
    }

    /**
     * Finish defining a region from the player's current location.
     * 
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     */
    private function endRegion(CommandSender $sender, array $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            return false;
        }

        $regionName = $args[0];
        
        $region = $this->regionManager->loadRegion($regionName);
        
        if (!is_null($region)) {
            $sender->sendMessage('The region "' . $regionName . '" already exists.  If you no longer need this region, please delete it using the "deleteregion" command');
            return false;
        }

        return true;
    }
    
    /**
     * Teleport the player to a region.  Takes one argument - the region name.
     * 
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     */
    private function teleportToRegion(CommandSender $sender, $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            return false;
        }
        
        $regionName = $args[0];
        
        // TODO Attempt to load the region and parse the location
        $permitX = 100;
        $permitY = 100;
        $permitZ = 100;
        $region = $this->regionManager->loadRegion($regionName);
        
        if (is_null($region)) {
            $sender->sendMessage('The region "' . $regionName . '" doesn\'t exist');
            return false;
        }
        
        $sender->sendMessage('Teleported to region: ' . $regionName . ' at location: [' . $permitX . ', ' . $permitZ . ', ' . $permitY . ']');
        //$this->api->player->tppos($args[0], 100, 100, 100);
        
        return true;
    }
}
