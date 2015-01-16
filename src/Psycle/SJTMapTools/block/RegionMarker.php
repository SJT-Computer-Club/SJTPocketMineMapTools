<?php

namespace Psycle\SJTMapTools\block;

use pocketmine\block\Gold;


class RegionMarker extends Gold {

    protected $id = 20000;

    public function __construct() {
        parent::__construct();
        $this->boundingBox = null;
    }

    public function isTransparent(){
        return true;
    }

    public function isSolid(){
        return false;
    }

    public function getBoundingBox(){
        return null;
    }

    public function getHardness(){
        return 0;
    }
}