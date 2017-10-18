<?php

namespace CodeIgniter\BaseModel;

use Composer\Script\Event;
use Composer\Installer\PackageEvent;

class Composer
{
    public static function postAutoloadDump(Event $event)
    {
        self::_runFileCopy($event);
    }

    public static function postPackageUninstall(PackageEvent $event)
    {
        self::_runFileRemove($event);
    }

    private static function _runFileCopy(Event $event)
    {
        if (!self::_isTargetDirExists()) {
            $event->getIO()->write("Target dir do not exists.");
            exit(1);
        }

        if (!self::_isTargetDirWritable()) {
            $event->getIO()->write("Target dir is not writable.");
            exit(2);
        }

        if (!self::_isSourceExists()) {
            $event->getIO()->write("Source file do not exists.");
            exit(3);
        }

        $sourceFile = self::_getSourceFile();
        $targetFile = self::_getTargetDir() . self::_getSourceFileName();

        if (function_exists('symlink') && symlink($sourceFile, $targetFile)) {
            $event->getIO()->write("Symlink successfully created.");
            exit(0);
        }

        if (copy($sourceFile, $targetFile)) {
            $event->getIO()->write("Copy successfully done.");
            exit(0);
        }

        $event->getIO()->write("Problem occur when copy or symlink file.");
        exit(4);
    }

    private static function _runFileRemove(PackageEvent $event)
    {
        $file = self::_getTargetDir() . self::_getSourceFileName();

        if (!is_file($file))
            exit(0);

        if (!unlink($file)) {
            $event->getIO()->write("Problem occur when remove file.");
            exit(1);
        }

        exit(0);
    }

    private static function _getSourceFile()
    {
        return realpath(__DIR__ . '/../') . '/core/' . self::_getSourceFileName();
    }

    private static function _getSourceFileName()
    {
        return 'MY_Model.php';
    }

    private static function _isSourceExists()
    {
        return is_file(self::_getSourceFile());
    }

    private static function _getTargetDir()
    {
        return realpath(__DIR__ . '/../../') . '/core/';
    }

    private static function _isTargetDirExists()
    {
        return is_dir(self::_getTargetDir());
    }

    private static function _isTargetDirWritable()
    {
        return is_writable(self::_getTargetDir());
    }
}
