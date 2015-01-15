<?php

namespace Psycle\SJTMapTools;

use pocketmine\scheduler\PluginTask;

/**
 * Background tasks
 */
class EverySecondTask extends PluginTask {
    private $running = false;

    /**
     * Called every time the task is triggered
     *
     * @param int $currentTick The value of the current tick (1 tick = 1/20th s)
     */
    function onRun($currentTick) {
        $plugin = SJTMapTools::getInstance();

        if ($this->running) {
            $plugin->getLogger()->info('Skipped EverySecondTask');
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
        SJTMapTools::getInstance()->getRegionManager()->tick();
    }
}
