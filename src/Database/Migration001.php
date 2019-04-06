<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Database;

/**
 * Class Migration001
 */
class Migration001
{
    /**
     * @param Database $database
     * @return string
     */
    public function migrate(Database $database)
    {
        $result = "Migrate to Database v1.1:\n";

        $sql = "ALTER TABLE 'category' RENAME TO 'topic'";
        $result .= $sql . "\n";
        if (!$database->queryAsBool($sql)) {
            $result .= 'ERROR: ' . implode(', ', $database->lastError) . "\n\n";
        }

        $sql = "ALTER TABLE 'topic' ADD COLUMN 'primary' BOOLEAN NOT NULL DEFAULT '0'";
        $result .= $sql . "\n";
        if (!$database->queryAsBool($sql)) {
            $result .= 'ERROR: ' . implode(', ', $database->lastError) . "\n\n";
        }

        $sql = "ALTER TABLE 'category2media' RENAME TO 'topic2media'";
        $result .= $sql . "\n";
        if (!$database->queryAsBool($sql)) {
            $result .= 'ERROR: ' . implode(', ', $database->lastError) . "\n\n";
        }

        return $result;
    }
}
