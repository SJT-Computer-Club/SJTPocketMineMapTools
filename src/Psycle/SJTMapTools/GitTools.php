<?php

namespace Psycle\SJTMapTools;

/**
 * A wrapper for some simple Git operations
 */
class GitTools {
    /**
     * Clone a Git repository
     * 
     * @param type $filePath The local file path to clone to
     * @param type $gitPath The remote git repository URL (.git)
     */
    public static function gitClone($filePath, $gitPath) {
        $command = 'git clone ' . $gitPath . ' ' . $filePath;
        exec($command);
    }
}
