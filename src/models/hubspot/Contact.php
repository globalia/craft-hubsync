<?php

namespace globalia\hubsync\models\hubspot;

use Craft;
use craft\elements\User;
use HubSpot\Client\Crm\Contacts\Model\SimplePublicObjectInput;
use globalia\hubsync\HubSynC;

class Contact extends Model
{

    public function rules()
    {
        return [
            [['craftId', 'hubspotId'], 'safe'],
        ];
    }

    public function fields()
    {
        return [
            'craftId',
            'hubspotId',
        ];
    }

    public static function tableName()
    {
        return '{{%chs_hubspot_contacts}}';
    }

    public function getProperties(): SimplePublicObjectInput
    {
        $user = Craft::$app->users->getUserById($this->craftId);

        $extraFields = [];

        foreach (HubSynC::$plugin->settings->customContactFields as $field) {
            $extraFields[$field['hubspotField']] = $user->{$field['craftField']};
        }

        $properties = new SimplePublicObjectInput();
        $properties->setProperties(array_merge([
            'email' => $user->email
        ], $extraFields));

        return $properties;
    }
}
