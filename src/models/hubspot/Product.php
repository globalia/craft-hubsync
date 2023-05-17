<?php

namespace globalia\hubsync\models\hubspot;

use Craft;
use craft\commerce\elements\Product as ElementsProduct;
use HubSpot\Client\Crm\Products\Model\SimplePublicObjectInput;
use globalia\hubsync\HubSynC;

class Product extends Model
{

    public function rules()
    {
        return [
            [['craftId', 'hubspotId'], 'safe'],
        ];
    }

    public static function tableName()
    {
        return '{{%chs_hubspot_products}}';
    }

    public function getProperties(): SimplePublicObjectInput
    {
        $product = ElementsProduct::find()
            ->id($this->craftId)
            ->one();

        $extraFields = [];

        foreach (HubSynC::$plugin->settings->customProductFields as $field) {
            $extraFields[$field['hubspotField']] = $product->{$field['craftField']};
        }

        $properties = new SimplePublicObjectInput();
        $properties->setProperties(array_merge([
            HubSynC::$plugin->settings->productSkuField => $product->defaultSku,
            'price' => $product->defaultPrice,
        ], $extraFields));

        return $properties;
    }
}
