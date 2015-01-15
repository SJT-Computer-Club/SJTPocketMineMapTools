<?php

namespace Psycle\SJTMapTools;

use pocketmine\level\Level;
use pocketmine\scheduler\PluginTask;
use pocketmine\Server;

/**
 * Background tasks
 */
class EveryMinuteTask extends PluginTask {
    private $running = false;

    /**
     * Called every time the task is triggered
     *
     * @param int $currentTick The value of the current tick (1 tick = 1/20th s)
     */
    function onRun($currentTick) {
        $plugin = SJTMapTools::getInstance();

        if ($this->running) {
            $plugin->getLogger()->info('Skipped EveryMinuteTask');
            return;
        }

        $this->running = true;
        $this->doTasks();
        $this->running = false;
    }

    /**
     * Perform the tasks
     */
    private function doTasks() {
        $level = Server::getInstance()->getDefaultLevel();

        // Keep it day all the time
        $level->setTime(Level::TIME_DAY);
    }
}
