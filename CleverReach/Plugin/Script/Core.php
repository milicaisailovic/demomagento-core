<?php

namespace CleverReach\Plugin\Script;

use Composer\Script\Event;

class Core
{
    public static function postUpdate(Event $event)
    {
        self::fixAndCopyDirectory('src', 'IntegrationCore');
    }

    private static function fixAndCopyDirectory($from, $to)
    {
        self::copyDirectory(__DIR__ . '/../vendor/cleverreach/integration-core/' . $from, __DIR__ . '/../tmp');
        self::renameNamespaces(__DIR__ . '/../tmp');
        self::copyDirectory(__DIR__ . '/../tmp', __DIR__ . '/../' . $to);
        self::removeDirectory(__DIR__ . '/../tmp');
        self::removeUnnecessaryFiles();
    }

    private static function copyDirectory($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    self::copyDirectory($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }

    private static function renameNamespaces($directory)
    {
        $iterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $fileToChange = file_get_contents($file->getRealPath());
                file_put_contents($file->getRealPath(), str_replace("CleverReach\\", "CleverReach\\Plugin\\IntegrationCore\\", $fileToChange));
            }
        }
        foreach ($files as $file) {
            if (!$file->isDir()) {
                $fileToChange = file_get_contents($file->getRealPath());
                file_put_contents($file->getRealPath(), str_replace("Logeecom\\", "CleverReach\\Plugin\\IntegrationCore\\", $fileToChange));
            }
        }
    }

    private static function removeUnnecessaryFiles()
    {
        unlink(__DIR__ . '/../IntegrationCore/Infrastructure/Serializer/Concrete/NativeSerializer.php');
    }

    private static function removeDirectory($directory)
    {
        $iterator = new \RecursiveDirectoryIterator($directory, \RecursiveDirectoryIterator::SKIP_DOTS);
        $files = new \RecursiveIteratorIterator($iterator, \RecursiveIteratorIterator::CHILD_FIRST);
        foreach ($files as $file) {
            if ($file->isDir()) {
                rmdir($file->getRealPath());
            } else {
                unlink($file->getRealPath());
            }
        }
        rmdir($directory);
    }
}
