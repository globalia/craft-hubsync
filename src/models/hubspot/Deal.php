<?php

namespace globalia\hubsync\models\hubspot;

use Craft;
use HubSpot\Client\Crm\Deals\Model\SimplePublicObjectInput;
use globalia\hubsync\HubSynC;

class Deal extends Model
{

    public function rules()
    {
        return [
            [['craftId', 'hubspotId'], 'safe'],
        ];
    }

    public static function tableName()
    {
        return '{{%chs_hubspot_deals}}';
    }

    public function getProperties(): SimplePublicObjectInput
    {
        $order = Craft::$app->commerce->getOrders()->getOrderById($this->craftId);

        $extraFields = [];

        foreach(HubSynC::$plugin->settings->customDealFields as $field) {
            $extraFields[$field['hubspotField']] = $order->{$field['craftField']};
        }

        if(HubSynC::$plugin->settings->dealOwnerId) {
            $extraFields['hubspot_owner_id'] = HubSynC::$plugin->settings->dealOwnerId;
        }
        
        if($order->isCompleted) {
            $extraFields['dealstage'] = HubSynC::$plugin->settings->dealStageCompleted;
            $extraFields['closedate'] = $order->dateOrdered->format('c');
        } else {
            $extraFields['dealstage'] = HubSynC::$plugin->settings->dealStageDefault;
        }


        $properties = new SimplePublicObjectInput();
        $properties->setProperties(array_merge([
            'amount' => $order->totalPrice,
            'dealname' => $order->number,
            'pipeline' => HubSynC::$plugin->settings->dealPipeline,
        ], $extraFields));

        return $properties;
    }
}
