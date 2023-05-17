<?php

namespace globalia\hubsync\jobs;

use Craft;
use craft\commerce\elements\Order;
use craft\elements\User;
use craft\queue\BaseJob;
use yii\queue\RetryableJobInterface;
use globalia\hubsync\HubSynC;
use craft\helpers\Queue;

/**
 * Sync Order queue job
 */
class SyncOrder extends BaseJob implements RetryableJobInterface
{
    public $orderId;

    public function execute($queue): void
    {
        $order = Order::find()->id($this->orderId)->one();
        if ($order) {

            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->deals
                ->createOrUpdateFromOrder($order);

            $settings = HubSynC::$plugin->settings;
            if($settings->syncLineItemsWithOrders) {
                foreach ($order->lineItems as $lineItem) {
                    if($lineItem->id) {
                        Queue::push(new SyncLineItem([
                            'itemId' => $lineItem->id,
                        ]));
                    }

                }
            }
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Syncing order {$this->orderId} to Hubspot";
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
