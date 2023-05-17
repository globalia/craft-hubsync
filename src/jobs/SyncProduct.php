<?php

namespace globalia\hubsync\jobs;

use Craft;
use craft\commerce\elements\Product;
use craft\queue\BaseJob;
use yii\queue\RetryableJobInterface;

/**
 * Sync Product queue job
 */
class SyncProduct extends BaseJob implements RetryableJobInterface
{
    public $productId;

    public function execute($queue): void
    {
        $product = Product::find()->id($this->productId)->one();
        if ($product) {
            Craft::$app
                ->getPlugins()
                ->getPlugin('hubsync')
                ->products
                ->createOrUpdateFromProduct($product);
        }
    }

    protected function defaultDescription(): ?string
    {
        return "Syncing product {$this->productId} to Hubspot";
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
