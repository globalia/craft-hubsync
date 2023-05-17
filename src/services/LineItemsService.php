<?php

namespace globalia\hubsync\services;

use Craft;
use craft\commerce\models\LineItem;
use globalia\hubsync\models\hubspot\LineItem as Item;
use Yii\base\Component;
use globalia\hubsync\HubSynC;
use globalia\hubsync\models\hubspot\Deal;
use globalia\hubsync\models\hubspot\Product;
use HubSpot\Client\Crm\LineItems\Model\Filter;
use HubSpot\Client\Crm\LineItems\Model\FilterGroup;
use HubSpot\Client\Crm\LineItems\Model\PublicObjectSearchRequest;

/**
 * Line Items Service service
 */
class LineItemsService extends Component
{
    private $api;

    public function init()
    {
        $plugin = HubSynC::getInstance();
        $this->api = $plugin->hubspotApi->getFactory()->crm()->lineItems();
    }

    public function createOrUpdateFromLineItem(LineItem $lineItem)
    {
        $item = Item::find()
            ->where(['craftId' => $lineItem->id])
            ->one();

        if (!$item) {
            $item = new Item();

            $item->setAttribute('craftId', $lineItem->id);

            if ($hubspotObject = $this->getByCraftLineItem($lineItem)) {
                $item->hubspotId = $hubspotObject->getId();
                $item->save();
            }
        }

        return $this->createOrUpdate($item);
    }

    public function createOrUpdate(Item $item)
    {
        if (!$item->hubspotId) {
            return $this->create($item);
        }
        

        return $this->update($item);
    }

    public function create(Item $item)
    {
        try {
            
            $hubspotObject = $this->api->basicApi()->create($item->getProperties());

            $item->hubspotId = $hubspotObject->getId();

            $item->save();

            $item->createAssociationWithDeal();

            return $item;
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }
    }

    public function update(Item $item)
    {
       
        try {
            $this->api->basicApi()->update($item->hubspotId, $item->getProperties());
           
            return $item;
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }
    }

    public function delete(Item $item)
    {
        try {
            $this->api->basicApi()->archive($item->hubspotId);
            $item->delete();
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }
    }

    public function getByCraftLineItem($lineItem)
    {
        $deal = Deal::find()
            ->where(['craftId' => $lineItem->order->id])
            ->one();

        $dealAssociation = new Filter();
        $dealAssociation->setPropertyName('associations.deal');
        $dealAssociation->setOperator('EQ');
        $dealAssociation->setValue($deal->hubspotId);


        $product = Product::find()
            ->where(['craftId' => $lineItem->purchasable->product->id])
            ->one();

        $skuFilter = new Filter();
        $skuFilter->setPropertyName('hs_product_id');
        $skuFilter->setOperator('EQ');
        $skuFilter->setValue($product->hubspotId);

        $filterGroup = new FilterGroup();
        $filterGroup->setFilters([$dealAssociation, $skuFilter]);

        $searchRequest = new PublicObjectSearchRequest();
        $searchRequest->setFilterGroups([$filterGroup]);

        $results = $this->api->searchApi()->doSearch($searchRequest)->getResults();

        if (!count($results)) {
            return null;
        }

        return current($results);
    }
}
