<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Gold;
use pocketmine\math\Vector3;
use pocketmine\Server;

/**
 * Handles the drawing of a 'rubber band' outline, without wiping out the world
 * as it is redrawn
 */
class RubberBander {
    /**
     * The bounds of the rubber band region
     * @var int
     */
    private $currentX1, $currentY1, $currentZ1, $currentX2, $currentY2, $currentZ2 = null;

    /**
     * Storage for the previous blocks before the rubber band was drawn
     * @var array
     */
    private $previousBlocks = null;


    /**
     * Constructor, set the start position.
     *
     * @param double $x1 The x coordinate of the start position
     * @param double $y1 The y coordinate of the start position
     * @param double $z1 The z coordinate of the start position
     */
    public function __construct($x1, $y1, $z1) {
        $this->currentX1 = (int)$x1;
        $this->currentY1 = (int)$y1;
        $this->currentZ1 = (int)$z1;
    }

    /**
     * Plot the rubber band using the given end position
     *
     * @param double $x2 The x coordinate of the end position
     * @param double $y2 The y coordinate of the end position
     * @param double $z2 The z coordinate of the end position
     */
    public function plot($x2, $y2, $z2) {
        $x2 = (int)$x2; $y2 = (int)$y2; $z2 = (int)$z2;
        if ($x2 == $this->currentX2 && $y2 == $this->currentY2 && $z2 == $this->currentZ2) { return; }

        $this->restore();
        $this->currentX2 = $x2;
        $this->currentY2 = $y2;
        $this->currentZ2 = $z2;
        $this->draw();
    }

    /**
     * Clear down the rubber band and state
     */
    public function stop() {
        $this->restore();
        $this->previousBlocks = null;
        $this->currentX1 = $this->currentY1 = $this->currentZ1 = $this->currentX2 = $this->currentY2 = $this->currentZ2 = null;
    }

    /**
     * Draw the rubber band, storing the blocks beneath the rubber band region
     */
    private function draw() {
        $this->previousBlocks = array();

        $level = Server::getInstance()->getDefaultLevel();
        $xmin = min($this->currentX1, $this->currentX2);
        $xmax = max($this->currentX1, $this->currentX2);
        $ymin = min($this->currentY1, $this->currentY2);
        $ymax = max($this->currentY1, $this->currentY2);
        $zmin = min($this->currentZ1, $this->currentZ2);
        $zmax = max($this->currentZ1, $this->currentZ2);

        for ($x = $xmin; $x <= $xmax; $x++) {
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymin, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymin, $zmax));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymax, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymax, $zmax));

            $level->setBlock(new Vector3($x, $ymin, $zmin), new Gold(), false, false);
            $level->setBlock(new Vector3($x, $ymin, $zmax), new Gold(), false, false);
            $level->setBlock(new Vector3($x, $ymax, $zmin), new Gold(), false, false);
            $level->setBlock(new Vector3($x, $ymax, $zmax), new Gold(), false, false);
        }
        for ($y = $ymin + 1; $y < $ymax; $y++) {
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $y, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $y, $zmax));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $y, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $y, $zmax));

            $level->setBlock(new Vector3($xmin, $y, $zmin), new Gold(), false, false);
            $level->setBlock(new Vector3($xmin, $y, $zmax), new Gold(), false, false);
            $level->setBlock(new Vector3($xmax, $y, $zmin), new Gold(), false, false);
            $level->setBlock(new Vector3($xmax, $y, $zmax), new Gold(), false, false);
        }
        for ($z = $zmin + 1; $z < $zmax; $z++) {
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $ymin, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $ymax, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $ymin, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $ymax, $z));

            $level->setBlock(new Vector3($xmin, $ymin, $z), new Gold(), false, false);
            $level->setBlock(new Vector3($xmin, $ymax, $z), new Gold(), false, false);
            $level->setBlock(new Vector3($xmax, $ymin, $z), new Gold(), false, false);
            $level->setBlock(new Vector3($xmax, $ymax, $z), new Gold(), false, false);
        }
    }

    /**
     * Restore the rubber band lines to the previous state before the band was drawn
     */
    private function restore() {
        if (is_null($this->previousBlocks)) { return; }

        $level = Server::getInstance()->getDefaultLevel();
        $xmin = min($this->currentX1, $this->currentX2);
        $xmax = max($this->currentX1, $this->currentX2);
        $ymin = min($this->currentY1, $this->currentY2);
        $ymax = max($this->currentY1, $this->currentY2);
        $zmin = min($this->currentZ1, $this->currentZ2);
        $zmax = max($this->currentZ1, $this->currentZ2);

        $previousBlocksIndex = 0;

        for ($x = $xmin; $x <= $xmax; $x++) {
            $level->setBlock(new Vector3($x, $ymin, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymin, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymax, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymax, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
        for ($y = $ymin + 1; $y < $ymax; $y++) {
            $level->setBlock(new Vector3($xmin, $y, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmin, $y, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $y, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $y, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
        for ($z = $zmin + 1; $z < $zmax; $z++) {
            $level->setBlock(new Vector3($xmin, $ymin, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmin, $ymax, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $ymin, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $ymax, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
    }
}
