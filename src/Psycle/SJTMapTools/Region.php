<?php

namespace Psycle\SJTMapTools;

/**
 * A container for a region of data in the map
 */
class Region {
    public $name;
    public $x1, $z1, $y1, $x2, $z2, $y2;
    
    private $dataFolder;
    private $data = array();
    
    function __construct($name, $userName, $x1, $z1, $y1, $x2, $z2, $y2, $dataFolder) {
        print('Created region: ' . $name . ' ' . $userName . ' ' .  $x1 . ' ' .  $z1 . ' ' .  $y1 . ' ' .  $x2 . ' ' .  $z2 . ' ' .  $y2);
        $this->name = $name;
        $this->x1 = $x1;
        $this->z1 = $z1;
        $this->y1 = $y1;
        $this->x2 = $x2;
        $this->z2 = $z2;
        $this->y2 = $y2;
        $this->dataFolder = $dataFolder;
        // TODO store initial data in object
    }
    
    /**
     * Write the region data to disk
     * 
     * @param boolean $createRevision If true, also push to Git
     * @return boolean true if successful
     */
    public function write($createRevision) {
        return true;
    }
    
    /**
     * Read region data from disk
     * 
     * @return boolean
     */
    public function read() {
        return true;
    }
}
