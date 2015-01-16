<?php

namespace Psycle\SJTMapTools;

/**
 * A wrapper for some simple Git operations
 */
class GitTools {
    /**
     * Clone a Git repository
     *
     * @param string $filePath The local file path to clone to
     * @param string $gitPath The remote git repository URL (.git)
     */
    public static function gitClone($filePath, $gitPath) {
        $command = 'git clone "' . $gitPath . '" "' . $filePath . '"';
        SJTMapTools::getInstance()->getLogger()->info('Executing: ' . $command);
        exec($command);
    }

    /**
    * Create a local git repository
    *
    * @param string $filePath The local folder path to create the repo in
    */
    public static function gitCreate($filePath) {
        $pathInfo = pathinfo($filePath);
        $command = 'cd "' . $pathInfo['dirname'] . '"; git init; git add .';
        exec($command);
    }

    /**
     * Add a file to the Git repo, forcing in case the file is affected by
     * a .gitignore
     *
     * @param string $filePath The path to the file
     */
    public static function gitAdd($filePath) {
        $pathInfo = pathinfo($filePath);
        $command = 'cd "' . $pathInfo['dirname'] . '"; git add -f -- "' . $pathInfo['basename'] . '"';
        SJTMapTools::getInstance()->getLogger()->info('Executing: ' . $command);
        exec($command);
    }

    /**
     * Commit a file.
     *
     * @param string $filePath The path to the file
     */
    public static function gitCommit($filePath, $message) {
        $pathInfo = pathinfo($filePath);
        $command = 'cd "' . $pathInfo['dirname'] . '"; git commit -m "' . str_replace('"', "'", $message) . '" -- "' . $pathInfo['basename'] . '"';
        SJTMapTools::getInstance()->getLogger()->info('Executing: ' . $command);
        exec($command);
    }

    /**
     * Push a file.
     *
     * @param string $filePath The path to the file
     */
    public static function gitPush($filePath) {
        $pathInfo = pathinfo($filePath);
        $command = 'cd "' . $pathInfo['dirname'] . '"; git push -- "' . $pathInfo['basename'] . '"';
        SJTMapTools::getInstance()->getLogger()->info('Executing: ' . $command);
        exec($command);
    }
}
