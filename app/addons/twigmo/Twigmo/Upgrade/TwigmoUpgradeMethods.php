<?php

namespace Twigmo\Upgrade;

use Twigmo\Upgrade\TwigmoUpgrade;

class TwigmoUpgradeMethods
{
    final public static function getBackupDir()
    {
        $_version = TwigmoUpgrade::getNextVersionInfo();
        $version = $_version['next_version'];
        if (!$version) {
            return false;
        }

        return TWIGMO_UPGRADE_DIR . $version . '/';
    }

    final public static function removeDirectoryContent($path)
    {
        if (is_array($path)) {
            foreach ($path as $dir) {
                self::removeDirectoryContent($dir);
            }
            return true;
        }
        self::removeFiles(fn_get_dir_contents($path, true, true, '', $path));

        return true;
    }

    final private static function removeFiles($source)
    {
        if (is_array($source)) {
            foreach ($source as $src) {
                self::removeFiles($src);
            }
        } else {
            fn_uc_rm($source);
        }

        return;
    }
}
