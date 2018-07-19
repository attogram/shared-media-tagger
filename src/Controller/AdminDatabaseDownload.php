<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Config;
use Attogram\SharedMedia\Tagger\Tools;
use ZipArchive;

/**
 * Class AdminDatabaseDownload
 */
class AdminDatabaseDownload extends ControllerBase
{
    protected function display()
    {
        $file = realpath($this->smt->database->databaseName);

        if (!is_readable($file)) {
            Tools::error500('Database File Not Found: ' . $file);
        }

        $baseName = basename($file);

        $dispositionName = preg_replace(
            '/[^a-z0-9]+/',
            '-',
            strtolower(Config::$siteName)
        ) . gmdate('.Y.m.d.H.i.s.') . $baseName;


        $zip = new ZipArchive();

        $zipFile = Config::$databaseDirectory . '/download.' . gmdate('Y.m.d.H.i.s') . '.zip';

        if (true !== $zip->open($zipFile, ZipArchive::CREATE)) {
            Tools::error500('Zip Archive Failed: ' . $zipFile);
        }

        $zip->addFile($file, $dispositionName . '.sqlite');

        $zip->close();

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($zipFile));
        header('Content-Disposition: attachment; filename="' . $dispositionName . '.zip"');

        readfile($zipFile);

        unlink($zipFile);

        Tools::shutdown();
    }
}
