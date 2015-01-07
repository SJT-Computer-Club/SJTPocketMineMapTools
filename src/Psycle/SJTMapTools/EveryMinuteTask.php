<?php

namespace Psycle\SJTMapTools;

use pocketmine\level\Level;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

/**
 * Background tasks
 */
class EveryMinuteTask extends PluginTask {
    /**
     * Called every time the task is triggered
     * @param int $currentTick The value of the current tick (1 tick = 1/20th s)
     */
    function onRun($currentTick) {
        $level = Server::getInstance()->getDefaultLevel();

        // Keep it day all the time
        $level->setTime(Level::TIME_DAY);
    }
}
