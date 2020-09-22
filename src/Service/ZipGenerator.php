<?php
/**
 * Created by PhpStorm.
 * User: Jakub
 * Date: 14.09.2020
 * Time: 23:01
 */

namespace JoomLavel\Rad\Service;


class ZipGenerator
{
    public function zipDirectory(string $name, string $directoryToZip)
    {
        $zipFile = new \PhpZip\ZipFile();
        try {
            $zipFile
                ->addDirRecursive($directoryToZip)
                ->saveAsFile($name. '.zip');
            $allInfo = $zipFile->getAllInfo();
            $zipFile->close();
        } catch (\PhpZip\Exception\ZipException $e) {
            // handle exception
        } finally {
            $zipFile->close();
        }
        if (!empty($allInfo)) {
            return $allInfo;
        }else{
            return false;
        }
    }
}