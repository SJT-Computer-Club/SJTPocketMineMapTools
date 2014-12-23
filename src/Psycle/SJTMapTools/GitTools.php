<?php

namespace Psycle\SJTMapTools;

/**
 * A wrapper for some simple Git operations
 */
class GitTools {
    public static function gitClone($plugin, $filePath, $gitPath) {
        $command = 'git clone ' . $gitPath . ' ' . $filePath;
        $plugin->getLogger()->info('GitTools: ' . $command);
        exec($command);
    }
}
