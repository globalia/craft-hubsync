<?php

namespace globalia\hubsync\services;

use Craft;
use yii\base\Component;
use globalia\hubsync\HubSynC;
use globalia\hubsync\models\hubspot\Deal;
use HubSpot\Client\Crm\Deals\Model\Filter;
use HubSpot\Client\Crm\Deals\Model\FilterGroup;
use HubSpot\Client\Crm\Deals\Model\PublicObjectSearchRequest;

/**
 * Deals Service service
 */
class DealsService extends Component
{
    private $api;

    public function init()
    {
        $plugin = HubSynC::getInstance();
        $this->api = $plugin->hubspotApi->getFactory()->crm()->deals();
    }

    public function createOrUpdateFromOrder($order)
    {
        $deal = Deal::find()
            ->where(['craftId' => $order->id])
            ->one();
       
           

        if (!$deal) {
            $deal = new Deal();

            $deal->setAttribute('craftId', $order->id);

            if ($hubspotObject = $this->getByOrderNumber($order->number)) {
                $deal->hubspotId = $hubspotObject->getId();
                $deal->save();
            }
        }

        return $this->createOrUpdate($deal);
    }

    public function createOrUpdate(Deal $deal)
    {
        if (!$deal->hubspotId) {
            return $this->create($deal);
        }

        return $this->update($deal);
    }

    public function create(Deal $deal)
    {
        try {
            
            $hubspotObject = $this->api->basicApi()->create($deal->getProperties());

            $deal->hubspotId = $hubspotObject->getId();
            $deal->save();

            return $deal;
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }

        return null;
    }

    public function update(Deal $deal)
    {
        try {
            $this->api->basicApi()->update($deal->hubspotId, $deal->getProperties());
        } catch (\Exception $e) {
            Craft::error($e->getMessage(), 'hubsync');
        }

        return null;
    }

    public function getByOrderNumber($orderNumber)
    {
        $filter = new Filter();
        $filter->setPropertyName('dealname');
        $filter->setOperator('EQ');
        $filter->setValue($orderNumber);

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
