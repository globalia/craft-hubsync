<?php

namespace globalia\hubsync\models\hubspot;

use Craft;
use HubSpot\Client\Crm\LineItems\Model\SimplePublicObjectInput;
use globalia\hubsync\HubSynC;

class LineItem extends Model
{

    public function rules()
    {
        return [
            [['craftId', 'hubspotId'], 'safe'],
        ];
    }

    public static function tableName()
    {
        return '{{%chs_hubspot_lineitems}}';
    }

    public function getProperties(): SimplePublicObjectInput
    {
        $lineItem = Craft::$app->commerce->getLineItems()->getLineItemById($this->craftId);

        $extraProperties = [];

        $product = Product::find()
            ->where(['craftId' => $lineItem->purchasable->product->id])
            ->one();

        if ($product) {
            $extraProperties = [
                'hs_product_id' => $product->hubspotId
            ];
        }

        $properties = new SimplePublicObjectInput();
        $properties->setProperties(array_filter(array_merge([
            'name' => $lineItem->description,
            'quantity' => $lineItem->qty,
            'price' => $lineItem->salePrice,
        ], $extraProperties)));

        return $properties;
    }

    public function createAssociationWithDeal()
    {
        $lineItem = Craft::$app->commerce->getLineItems()->getLineItemById($this->craftId);

        $deal = Deal::find()
            ->where(['craftId' => $lineItem->order->id])
            ->one();
            
        if ($deal) {
            $plugin = HubSynC::getInstance();
            $plugin->hubspotApi->createAssociation('line_item', $this->hubspotId, 'deal', $deal->hubspotId);
        }
    }
}
