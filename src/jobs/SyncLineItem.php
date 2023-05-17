<?php

namespace globalia\hubsync\jobs;

use Craft;
use craft\commerce\elements\Order;
use craft\elements\User;
use craft\queue\BaseJob;
use yii\queue\RetryableJobInterface;

/**
 * Sync Line Item queue job
 */
class SyncLineItem extends BaseJob implements RetryableJobInterface
{
    public $itemId;

    public function execute($queue): void
    {
        $lineItem = Craft::$app->commerce->getLineItems()->getLineItemById($this->itemId);
        if ($lineItem) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->lineItems
                ->createOrUpdateFromLineItem($lineItem);
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Syncing line item {$this->itemId} to Hubspot";
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
