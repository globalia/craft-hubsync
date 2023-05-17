<?php

namespace globalia\hubsync\services;

use Craft;
use yii\base\Component;
use globalia\hubsync\HubSynC;
use globalia\hubsync\models\hubspot\Product;
use craft\commerce\elements\Product as CommerceProduct;
use HubSpot\Client\Crm\Products\Model\Filter;
use HubSpot\Client\Crm\Products\Model\FilterGroup;
use HubSpot\Client\Crm\Products\Model\PublicObjectSearchRequest;

/**
 * Products Service service
 */
class ProductsService extends Component
{
    public $productSkuField;

    private $api;

    public function init()
    {
        $plugin = HubSynC::getInstance();
        $this->api = $plugin->hubspotApi->getFactory()->crm()->products();
        $this->productSkuField = $plugin->settings->productSkuField;
    }

    public function createOrUpdateFromProduct(CommerceProduct $commerceProduct)
    {
        $product = Product::find()
            ->where(['craftId' => $commerceProduct->id])
            ->one();

        if (!$product) {
            $product = new Product();

            $product->setAttribute('craftId', $commerceProduct->id);

            if ($hubspotObject = $this->getBySku($commerceProduct->defaultSku)) {
                $product->hubspotId = $hubspotObject->getId();
                $product->save();
            }
        }

        return $this->createOrUpdate($product);
    }

    public function createOrUpdate(Product $product)
    {
        if (!$product->hubspotId) {
            return $this->create($product);
        }

        return $this->update($product);
    }

    public function create(Product $product)
    {
        try {
            $hubspotObject = $this->api->basicApi()->create($product->getProperties());
            $product->hubspotId = $hubspotObject->getId();
            $product->save();
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }

        return $product;
    }

    public function update(Product $product)
    {
        try {
            $this->api->basicApi()->update($product->hubspotId, $product->getProperties());
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }

        return $product;
    }

    public function getBySku($sku)
    {
        $filter = new Filter();
        $filter->setOperator('EQ')
        ->setPropertyName($this->productSkuField)
        ->setValue($sku);

        $filterGroup = new FilterGroup();
        $filterGroup->setFilters([$filter]);

        $searchRequest = new PublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        $results = $this->api->searchApi()->doSearch($searchRequest)->getResults();

        if (!count($results)) {
            return null;
        }

        return current($results);
    }
}
