<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class AdminDatabaseDownload
 */
class AdminDatabaseDownload extends ControllerBase
{
    protected function display()
    {
        $file = realpath($this->smt->database->databaseName);

        if (!is_readable($file)) {
            Tools::error404('Database File Not Found: ' . $file);
        }

        $baseName = basename($file);
        $dispositionName = gmdate('Y.m.d.H.i.s.') . $baseName;

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $baseName . '"');
        header('Expires: 0');
        header('Cache-Control: must-revalidate');
        header('Pragma: public');
        header('Content-Length: ' . filesize($file));
        header('Content-Disposition: filename=' . $dispositionName);

        readfile($file);

        Tools::shutdown();
    }
}
