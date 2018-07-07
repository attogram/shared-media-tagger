<?php
declare(strict_types = 1);

namespace Attogram\SharedMedia\Tagger\Controller;

use Attogram\SharedMedia\Tagger\Tools;

/**
 * Class Tag
 */
class Tag extends ControllerBase
{
    protected function display()
    {
        $mediaId = isset($_GET['m']) ? $_GET['m'] : false;
        $tagId = isset($_GET['t']) ? $_GET['t'] : false;

        if (!$tagId || !Tools::isPositiveNumber($tagId)) {
            $this->redirect('Tag ID invalid');
        }

        if (!$mediaId || !Tools::isPositiveNumber($mediaId)) {
            $this->redirect('Media ID invalid');
        }

        // Get user, or create new user
        if (!$this->smt->database->getUser(true)) {
            $this->redirect('User invalid');
        }

        // Tag exists?
        if (!$this->smt->database->queryAsBool(
            'SELECT id FROM tag WHERE id = :tag_id',
            [':tag_id' => $tagId]
        )
        ) {
            $this->redirect('Tag Not Found');
        }

        // Media exists?
        if (!$this->smt->database->queryAsBool(
            'SELECT pageid FROM media WHERE pageid = :media_id',
            [':media_id' => $mediaId]
        )
        ) {
            $this->redirect('Media Not Found');
        }

        // has user already rated this file?
        $existingRating = $this->smt->database->queryAsArray(
            'SELECT tag_id
            FROM tagging
            WHERE user_id = :user_id
            AND media_pageid = :media_id',
            [
                ':media_id' => $mediaId,
                ':user_id'  => $this->smt->database->userId,
            ]
        );
        if ($existingRating) {
            $oldTag = $existingRating[0]['tag_id'];
            if ($oldTag == $tagId) { // user NOT changing tag, do nothing
                $this->redirect('OK: user confirmed existing rating');
            }
            // Switch old tag to new tag
            $this->smt->database->queryAsBool(
                'UPDATE tagging
                    SET tag_id = :tag_id
                    WHERE user_id = :user_id
                    AND media_pageid = :media_id',
                [
                    ':media_id' => $mediaId,
                    ':tag_id'   => $tagId,
                    ':user_id'  => $this->smt->database->userId,
                ]
            );
            $this->redirect('OK: user changed existing rating');
        }

        // insert new tag
        $this->smt->database->queryAsBool(
            'INSERT INTO tagging (tag_id, media_pageid, user_id) 
                VALUES (:tag_id, :media_id, :user_id)',
            [
                ':media_id' => $mediaId,
                ':tag_id'   => $tagId,
                ':user_id'  => $this->smt->database->userId,
            ]
        );
        $this->redirect('OK: user added rating');
    }

    /**
     * Redirect to a random media file
     *
     * @param string $message
     */
    private function redirect($message = '')
    {
        //Tools::debug($message); exit;
        $next = $this->smt->database->getRandomMedia();
        $location = './';
        if (isset($next[0]['pageid'])) {
            $location = Tools::url('info') . '/' . $next[0]['pageid'];
        }
        header('Location: ' . $location);
        Tools::shutdown();
    }
}
