<?php

namespace Psycle\SJTMapTools;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\block\BlockPlaceEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerInteractEvent;
use pocketmine\math\Vector3;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextWrapper;

/**
 * Main plugin class
 */
class SJTMapTools extends PluginBase implements Listener {

    /**
     * A static reference to this plugin instance
     * @var type SJTMapTools
     */
    private static $instance;

    /**
     * Our RegionManager instance
     * @var type RegionManager
     */
    private $regionManager;

    /**
     * Called when the plugin is enabled
     */
    public function onEnable() {
        self::$instance = $this;
        $this->getLogger()->info('Plugin Enabled');
        $this->initDataFolder();
        $this->regionManager = new RegionManager($this->getDataFolder() . 'regions/');
        $this->getServer()->getScheduler()->scheduleRepeatingTask(new EveryMinuteTask($this), 60 * 20);
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
    }

    /**
     * Called when the plugin is disabled
     */
    public function onDisable() {
        $this->getLogger()->info('Plugin Disabled');
    }

    /**
     * Returns the plugin instance
     * @return SJTMapTools The plugin instance
     */
    public static function getInstance() {
        return self::$instance;
    }

    /* Data handling */

    /**
     * Create the data folder structure
     */
    private function initDataFolder() {
        $dataFolder = $this->getDataFolder();
        if (!is_dir($dataFolder)) {
            $this->getLogger()->info('Data folder not found, creating at: ' . $dataFolder);
            mkdir($dataFolder, 0755, true);
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
     * @return boolean true if successful
     */
    public function onCommand(CommandSender $sender, Command $command, $label, array $args) {
        switch (strtolower($command->getName())) {
            case 'listregions':
                $this->getLogger()->info($sender->getName() . ' called listregions');
                return $this->listRegions($sender, $args);
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
            case 'saveregion':
                $this->getLogger()->info($sender->getName() . ' called saveregion');
                return $this->saveRegion($sender, $args);
            case 'revertregion':
                $this->getLogger()->info($sender->getName() . ' called revertregion');
                return $this->revertRegion($sender, $args);
            case 'requestpermit':
                $this->getLogger()->info($sender->getName() . ' called requestpermit');
                return $this->requestPermit($sender, $args);
            case 'releasepermit':
                $this->getLogger()->info($sender->getName() . ' called releasepermit');
                return $this->releasePermit($sender, $args);
        }

        return false;
    }

    /**
     * List all currently defined regions.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function listRegions(CommandSender $sender, array $args) {
        $sender->sendMessage($this->regionManager->listRegions($sender));

        return true;
    }

    /**
     * Start defining the region from the player's current location.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function startRegion(CommandSender $sender, array $args) {
        $player = $this->getServer()->getPlayer($sender->getName());

        if (!$player) {
            $sender->sendMessage('The player "' . $sender->getName() . '" doesn\'t exist.  Are you trying to run startregion from the console?');
            return false;
        }

        $result = $this->regionManager->startRegion($sender->getName(), $player->x, $player->y, $player->z);

        switch ($result) {
            case RegionManager::ERROR_REGION_ALREADY_STARTED:
                $sender->sendMessage('You have already started defining a region, use cancelregion to abandon it');
                $this->getLogger()->info('startregion failed, ' . $sender->getName() . ' has already started a region');
                return false;
        }

        $sender->sendMessage('Region definition started');
        $this->getLogger()->info('Region definition started');

        return true;
    }

    /**
     * Cancel defining the already started region
     * @param CommandSender $sender The command sender object
     * @param array $args
     * @return boolean true if successful
     */
    private function cancelRegion(CommandSender $sender, array $args) {
        $result = $this->regionManager->cancelRegion($sender->getName());

        switch ($result) {
            case RegionManager::ERROR_REGION_NOT_STARTED:
                $sender->sendMessage('You have not started defining a region, nothing to cancel');
                $this->getLogger()->info('cancelregion failed, ' . $sender->getName() . ' has not started defining a region');
                return false;
        }

        $sender->sendMessage('Region definition cancelled');
        $this->getLogger()->info('Region definition cancelled');

        return true;
    }

    /**
     * Finish defining a region from the player's current location.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
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

        $result = $this->regionManager->endRegion($sender->getName(), $args[0], $player->x, $player->y, $player->z);

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

        $sender->sendMessage('Region "' . $args[0] . '" has been defined');
        $this->getLogger()->info('Region ' . $args[0] . ' has been defined');

        return true;
    }

    /**
     * Teleport the player to a region.  Takes one argument - the region name.
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
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
        $region = $this->regionManager->getRegion($regionName);

        if (is_null($region)) {
            $sender->sendMessage('The region "' . $regionName . '" doesn\'t exist');
            return false;
        }

        $player->teleport(new Vector3($region->x1, $region->y2, $region->z1));
        $this->getLogger()->info('Teleported to region: ' . $regionName . ' at location: [' . $region->x1 . ', ' . $region->y2 . ', ' . $region->z1 . ']');

        return true;
    }

    /**
     * Save a region, creating a revision in the Git repo
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function saveRegion(CommandSender $sender, $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('saveregion failed, ' . $sender->getName() . ' did not specify a region name');
            return false;
        }

        $regionName = $args[0];
        $region = $this->regionManager->getRegion($regionName);

        if (is_null($region)) {
            $sender->sendMessage('The region "' . $regionName . '" doesn\'t exist');
            return false;
        }

        $region->write($sender->getName());
        $this->getLogger()->info('Saved region: ' . $regionName);

        return true;
    }

    /**
     * Revert a region's content to the last saved state
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function revertRegion(CommandSender $sender, $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('revertregion failed, ' . $sender->getName() . ' did not specify a region name');
            return false;
        }

        $regionName = $args[0];
        $region = $this->regionManager->getRegion($regionName);

        if (is_null($region)) {
            $sender->sendMessage('The region "' . $regionName . '" doesn\'t exist');
            return false;
        }

        $region->revert($sender->getName());
        $this->getLogger()->info('Reverted region: ' . $regionName);

        return true;
    }

    /**
     * Request a permit to edit a region
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function requestPermit(CommandSender $sender, $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('requestpermit failed, ' . $sender->getName() . ' did not specify a region name');
            return false;
        }

        $result = $this->regionManager->requestPermit($sender->getName(), $args[0]);

        switch ($result) {
            case RegionManager::ERROR_REGION_DOESNT_EXIST:
                $sender->sendMessage('The region "' . $args[0] . '" doesn\'t exist');
                $this->getLogger()->info('requestpermit failed, ' . $sender->getName() . ' requested a region ' . $args[0] . ' which doesn\'t exist');
                return false;
            case RegionManager::ERROR_PERMIT_ALREADY_ISSUED:
                $sender->sendMessage('A permit for the region "' . $args[0] . '" has already been issued');
                $this->getLogger()->info('requestpermit failed, a permit for ' . $args[0] . ' has already been issued');
                return false;
        }

        $sender->sendMessage('Permit for region "' . $args[0] . '" has been issued');
        $this->getLogger()->info('Permit for region ' . $args[0] . ' has been issued');

        return true;
    }

    /**
     * Release a permit for editing a region
     *
     * @param CommandSender $sender The command sender object
     * @param array $args The arguments passed to the command
     * @return boolean true if successful
     */
    private function releasePermit(CommandSender $sender, $args) {
        if (!isset($args[0])) {
            $sender->sendMessage('Please supply a region name');
            $this->getLogger()->info('releasepermit failed, ' . $sender->getName() . ' did not specify a region name');
            return false;
        }

        $result = $this->regionManager->releasePermit($sender->getName(), $args[0]);

        switch ($result) {
            case RegionManager::ERROR_REGION_DOESNT_EXIST:
                $sender->sendMessage('The region "' . $args[0] . '" doesn\'t exist');
                $this->getLogger()->info('releasepermit failed, ' . $sender->getName() . ' requested a region ' . $args[0] . ' which doesn\'t exist');
                return false;
            case RegionManager::ERROR_PERMIT_NOT_ASSIGNED_TO_USER:
                $sender->sendMessage('Another user has the permit for region "' . $args[0] . '"');
                $this->getLogger()->info('releasepermit failed, another user has the permit for region ' . $args[0]);
                return false;
        }

        $sender->sendMessage('Permit for region "' . $args[0] . '" has been released');
        $this->getLogger()->info('Permit for region ' . $args[0] . ' has been released');

        return true;
    }

    // EVENTS

    /**
     * Handle BlockBreakEvent.
     *
     * @param BlockBreakEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        $player = $event->getPlayer();

        if (false) {
            return true;
        } else {
            $event->setCancelled();
        }
    }

    /**
     * Handle BlockPlaceEvent.
     *
     * @param BlockPlaceEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onBlockPlace(BlockPlaceEvent $event) {
        $player = $event->getPlayer();

        if (false) {
            return true;
        } else {
            $event->setCancelled();
        }
    }

    /**
     * Handle PlayerInteractEvent.
     *
     * @param BlockBreakEvent $event The event
     *
     * @priority       NORMAL
     * @ignoreCanceled false
     */
    public function onPlayerInteract(PlayerInteractEvent $event) {
        $player = $event->getPlayer();

        if (false) {
            return true;
        } else {
            $event->setCancelled();
        }
    }

}
