<?php

namespace Psycle\SJTMapTools;

use pocketmine\block\Block;
use pocketmine\entity\Entity;
use pocketmine\math\Vector3;
use pocketmine\nbt\tag\Byte;
use pocketmine\nbt\tag\Compound;
use pocketmine\nbt\tag\Double;
use pocketmine\nbt\tag\Enum;
use pocketmine\nbt\tag\Float;
use pocketmine\nbt\tag\Int;
use pocketmine\Server;
use Psycle\SJTMapTools\block\RegionMarker;

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

    //private $entities = null;

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
    public function plot($player) {
        $x2 = (int)$player->x; $y2 = (int)$player->y; $z2 = (int)$player->z;
        if ($x2 == $this->currentX2 && $y2 == $this->currentY2 && $z2 == $this->currentZ2) { return; }

        $this->restore($player);
        $this->currentX2 = $x2;
        $this->currentY2 = $y2;
        $this->currentZ2 = $z2;
        $this->draw($player);
    }

    /**
     * Clear down the rubber band and state
     */
    public function stop() {
        $this->restore();
        $this->previousBlocks = null;
        //$this->entities = null;
        $this->currentX1 = $this->currentY1 = $this->currentZ1 = $this->currentX2 = $this->currentY2 = $this->currentZ2 = null;
    }

    /**
     * Draw the rubber band, storing the blocks beneath the rubber band region
     */
    private function draw($player) {
        $this->previousBlocks = array();
        //$this->entities = array();

        $level = Server::getInstance()->getDefaultLevel();
        $xmin = min($this->currentX1, $this->currentX2);
        $xmax = max($this->currentX1, $this->currentX2);
        $ymin = min($this->currentY1, $this->currentY2);
        $ymax = max($this->currentY1, $this->currentY2);
        $zmin = min($this->currentZ1, $this->currentZ2);
        $zmax = max($this->currentZ1, $this->currentZ2);

        for ($x = $xmin + 1; $x < $xmax; $x+=2) {
            /* This is an attempt to use Entities instead of Blocks to mark the
             * boundaries, so players can walk through them. Current problem is
             * they aren't persistent and also leave behind trails of earth
             * blocks.  Perhaps subclass FallingSand to make an Entity that
             * that doesn't fall or destroy itself?
            $entity = Entity::createEntity("FallingSand", $level->getChunk($x >> 4, $zmin >> 4), new Compound("", [
                        "Pos" => new Enum("Pos", [
                            new Double("", $x),
                            new Double("", $ymin),
                            new Double("", $zmin)
                        ]),
                        "Motion" => new Enum("Motion", [
                            new Double("", 0),
                            new Double("", 0),
                            new Double("", 0)
                        ]),
                        "Rotation" => new Enum("Rotation", [
                            new Float("", 0),
                            new Float("", 0)
                        ]),
                        "TileID" => new Int("TileID", Block::GOLD_BLOCK),
                        "Data" => new Byte("Data", 0),
                    ]));

            $entity->spawnTo($player);
            $this->entities[] = $entity;*/

            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymin, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymin, $zmax));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymax, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($x, $ymax, $zmax));

            $level->setBlock(new Vector3($x, $ymin, $zmin), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($x, $ymin, $zmax), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($x, $ymax, $zmin), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($x, $ymax, $zmax), new RegionMarker(), false, false);
        }
        for ($y = $ymin + 1; $y < $ymax; $y+=2) {
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $y, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $y, $zmax));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $y, $zmin));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $y, $zmax));

            $level->setBlock(new Vector3($xmin, $y, $zmin), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmin, $y, $zmax), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmax, $y, $zmin), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmax, $y, $zmax), new RegionMarker(), false, false);
        }
        for ($z = $zmin + 1; $z < $zmax; $z+=2) {
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $ymin, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmin, $ymax, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $ymin, $z));
            $this->previousBlocks[] = $level->getBlock(new Vector3($xmax, $ymax, $z));

            $level->setBlock(new Vector3($xmin, $ymin, $z), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmin, $ymax, $z), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmax, $ymin, $z), new RegionMarker(), false, false);
            $level->setBlock(new Vector3($xmax, $ymax, $z), new RegionMarker(), false, false);
        }
    }

    /**
     * Restore the rubber band lines to the previous state before the band was drawn
     */
    private function restore($player = null) {
        if (is_null($this->previousBlocks)) { return; }

        $level = Server::getInstance()->getDefaultLevel();
        $xmin = min($this->currentX1, $this->currentX2);
        $xmax = max($this->currentX1, $this->currentX2);
        $ymin = min($this->currentY1, $this->currentY2);
        $ymax = max($this->currentY1, $this->currentY2);
        $zmin = min($this->currentZ1, $this->currentZ2);
        $zmax = max($this->currentZ1, $this->currentZ2);

        $previousBlocksIndex = 0;

        for ($x = $xmin + 1; $x < $xmax; $x+=2) {
            $level->setBlock(new Vector3($x, $ymin, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymin, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymax, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($x, $ymax, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
        for ($y = $ymin + 1; $y < $ymax; $y+=2) {
            $level->setBlock(new Vector3($xmin, $y, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmin, $y, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $y, $zmin), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $y, $zmax), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
        for ($z = $zmin + 1; $z < $zmax; $z+=2) {
            $level->setBlock(new Vector3($xmin, $ymin, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmin, $ymax, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $ymin, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
            $level->setBlock(new Vector3($xmax, $ymax, $z), $this->previousBlocks[$previousBlocksIndex], false, false); $previousBlocksIndex++;
        }
    }
}
