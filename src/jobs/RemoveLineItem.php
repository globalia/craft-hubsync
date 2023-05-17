<?php

namespace globalia\hubsync\jobs;

use Craft;
use craft\queue\BaseJob;
use globalia\hubsync\models\hubspot\LineItem;
use yii\queue\RetryableJobInterface;

/**
 * Remove Line Item queue job
 */
class RemoveLineItem extends BaseJob implements RetryableJobInterface
{
    public $itemId;

    public function execute($queue): void
    {
        $lineItem = LineItem::findOne($this->itemId);
        if ($lineItem) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->lineItems
                ->delete($lineItem);
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Removing line item {$this->itemId} to Hubspot";
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
