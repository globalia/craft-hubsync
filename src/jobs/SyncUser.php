<?php

namespace globalia\hubsync\jobs;

use Craft;
use craft\elements\User;
use craft\queue\BaseJob;
use yii\queue\RetryableJobInterface;

/**
 * Sync User queue job
 */
class SyncUser extends BaseJob implements RetryableJobInterface
{
    public $userId;

    public function execute($queue): void
    {
        $user = User::find()->id($this->userId)->one();
        if ($user) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->contacts
                ->createOrUpdateFromUser($user);
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Syncing user {$this->userId} to Hubspot";
    }

    public function getTtr()
    {
        return 60;
    }

    public function canRetry($attempt, $error)
    {
        return ($attempt < 5) && $error->getCode() === 429;
    }
}
