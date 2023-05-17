<?php

namespace globalia\hubsync\models\hubspot;

use Craft;
use craft\db\ActiveRecord;
use globalia\hubsync\HubSynC;

class Model extends ActiveRecord
{
    protected $hubspotObject;
    protected $settings;

    public function __construct()
    {
        parent::__construct();

        $this->settings = HubSynC::$plugin->settings;
        
    }
}