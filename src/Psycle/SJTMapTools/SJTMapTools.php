<?php

namespace Psycle\SJTMapTools;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;

/**
 * Main plugin class
 */
class SJTMapTools extends PluginBase {

    public static $instance;
    
    private $regionManager;
    private $dataFolder;
    
    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        self::$instance = $this;
        $this->getLogger()->info('Plugin Enabled');
        $this->initDataFolder();
        $this->regionManager = new RegionManager($this->dataFolder . 'regions/');
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
        switch (strtolower($command->getName())) {
            case 'startregion':
                $this->getLogger()->info($sender->getName() . ' called startregion');
                return $this->startRegion($sender, $args);
            case 'cancelregion':
                $this->getLogger()->info($sender->getName() . ' called cancelregion');
                return $this->cancelRegion($sender, $args);
            case 'endregion':
                $this->getLogger()->info($sender->getName() . ' called endregion');
                return $this->endRegion($sender, $args);
            case 'tptoregion':
                $this->getLogger()->info($sender->getName() . ' called tptoregion');
                return $this->tpToRegion($sender, $args);
            case 'deleteregion':
                $this->getLogger()->info($sender->getName() . ' called deleteregion');
                return true;
        }
        
        return false;
    }

    /**
     * Start defining the region from the player's current location.
     * 
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean True if successful
    */
    private function startRegion(CommandSender $sender, array $args) {
        $player = $this->getServer()->getPlayer($sender->getName());
        
        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist.  Are you trying to run startregion from the console?');
            return false;
        }

        $result = $this->regionManager->startRegion($sender->getName(), $player->x, $player->z, $player->y);

        switch ($result) {
            case RegionManager::ERROR_REGION_ALREADY_STARTED:
                $sender->sendMessage('You have already started defining a region, use cancelregion to abandon it');
                $this->getLogger()->info('startregion failed, ' . $sender->getName() . ' has already started a region');
                return false;
        }
        
        return true;
    }

    /**
     * Cancel defining the already started region
     * @param CommandSender $sender The command sender object
     * @param array $args
     * @return boolean True if successful
     */
    private function cancelRegion(CommandSender $sender, array $args) {
        $result = $this->regionManager->cancelRegion($sender->getName());
        
        switch ($result) {
            case RegionManager::ERROR_REGION_NOT_STARTED:
                $sender->sendMessage('You have not started defining a region, nothing to cancel');
                $this->getLogger()->info('cancelregion failed, ' . $sender->getName() . ' has not started defining a region');
                return false;
        }
        
        return true;
    }
    
    /**
     * Finish defining a region from the player's current location.
     * 
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean True if successful
     */
    private function endRegion(CommandSender $sender, array $args) {
        $player = $this->getServer()->getPlayer($sender->getName());
        
        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist.  Are you trying to run endregion from the console?');
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('endregion failed, ' . $sender->getName() . ' did not specify a region name');
            return false;
        }
        
        
        $result = $this->regionManager->endRegion($sender->getName(), $args[0], $player->x, $player->z, $player->y);
        
        switch ($result) {
            case RegionManager::ERROR_REGION_EXISTS:
                $sender->sendMessage('The region "' . $args[0] . '" already exists.  If you no longer need this region, delete it using deleteregion');
                $this->getLogger()->info('endregion failed, ' . $sender->getName() . ' attempted to create a region ' . $args[0] . ' which already exists');
                return false;
            case RegionManager::ERROR_REGION_END_WITHOUT_START:
                $sender->sendMessage('You haven\'t started defining a region yet, use startregion first');
                $this->getLogger()->info('endregion failed, ' . $sender->getName() . ' has not started a region');
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
    private function tptoregion(CommandSender $sender, $args) {
        $player = $this->getServer()->getPlayer($sender->getName());
        
        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist.  Are you trying to run tptoregion from the console?');
            return false;
        }

        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('tptoregion failed, ' . $sender->getName() . ' did not specify a region name');
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
