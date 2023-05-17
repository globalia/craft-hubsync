<?php

namespace globalia\hubsync\console\controllers;

use Craft;
use craft\commerce\elements\Order;
use craft\console\Controller;
use yii\console\ExitCode;

/**
 * Sync Controller
 */
class SyncController extends Controller
{
    public $defaultAction = 'order';

    public function options($actionID): array
    {
        return parent::options($actionID);
    }

    /**
     * hubsync/sync-order command
     * @param int $orderId
     */
    public function actionOrder(int $orderId): int
    {
        $order = Order::find()->id($orderId)->one();
        
        if ($order) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->deals
                ->createOrUpdateFromOrder($order);
        }

        return ExitCode::OK;
    }

    /**
     * hubsync/sync-line-item command
     * @param int $lineItemId
     */
    public function actionLineItem(int $lineItemId): int
    {
        $lineItem = Craft::$app->commerce->getLineItems()->getLineItemById($lineItemId);
        if ($lineItem) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->lineItems
                ->createOrUpdateFromLineItem($lineItem);
        }
        
        return ExitCode::OK;
    }
}
