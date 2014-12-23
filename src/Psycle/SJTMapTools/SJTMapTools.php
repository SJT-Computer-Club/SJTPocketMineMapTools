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
    
    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        $this->getLogger()->info('Plugin Enabled');
        $this->initDataFolder();
        $this->regionManager = new RegionManager($this, $dataFolder . 'regions/');
    }

    /**
     * Called when the plugin is disabled
     */
    public function onDisable() {
        $this->getLogger()->info('Plugin Disabled');
    }


    /* Data handling */
    private function initDataFolder() {
        $dataFolder = $this->getDataFolder() . 'data/';
        if (!is_dir($dataFolder)) {
            $this->getLogger()->info('Data folder not found, creating at: ' . $dataFolder);
            mkdir($dataFolder, 0755, true);
        }
    }
    
    /* Command handling */
    
    /**
     * Handle a command from a player
     * 
     * @param \Psycle\SJTMapTools\CommandSender $sender The command sender object
     * @param \Psycle\SJTMapTools\Command $command The command object
     * @param type $label 
     * @param array $args The command arguments
     * @return boolean
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        if (strtolower($command->getName()) === 'startpermit') {
            $this->getLogger()->info('Starting a permit region');
            
            return true;
        } elseif (strtolower($command->getName()) === 'endpermit') {
            $this->getLogger()->info('Ending a permit region');
            
            return true;
        } elseif (strtolower($command->getName()) === 'teleporttopermit') {
            $this->getLogger()->info('Teleporting to a permit region');
            
            return true;
        }
        
        return false;
    }

    /**
     * Start defining the region for a permit from the player's current location.  No arguments.
     * 
     * @param type $cmd The command
     * @param type $args The command arguments
     * @param type $issuer The issuer of the command
     */
    public function startPermitRegion($cmd, $args, $issuer) {
        $player = $this->api->player->get($issuer->username);
        console($issuer->username . ': ' . $args[0] . ': ' . $player->entity->x . ' ' . $player->entity->y . ' ' . $player->entity->z . ' ' . $player->entity->yaw . ' ' . $player->entity->pitch);
        $this->config['locations'][$args[0]] = Array('x' => (int) $player->entity->x, 'y' => (int) $player->entity->y, 'z' => (int) $player->entity->z);
        $this->api->plugin->writeYAML($this->path . 'config.yml', $this->config);
    }

    /**
     * Teleport the player to a permit region.  Takes one argument - the permit name.
     * 
     * @param type $cmd The command
     * @param type $args The command arguments
     * @param type $issuer The issuer of the command
     */
    public function teleportToPermit($cmd, $args, $issuer) {
        if (!isset($args[1])) {
            console('Please supply a permit name');
            return;
        }
        
        $permitName = $args[1];
        
        // TODO Attempt to load the permit and parse the location
        $permitX = 100;
        $permitY = 100;
        $permitZ = 100;
        console('Teleported to permit: ' . $permitName . ' at location: [' . $permitX . ', ' . $permitZ . ', ' . $permitY . ']');
        $this->api->player->tppos($args[0], 100, 100, 100);
    }
    
}
