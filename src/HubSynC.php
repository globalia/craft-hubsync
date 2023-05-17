<?php

namespace globalia\hubsync;

use Craft;
use craft\base\Model;
use craft\base\Plugin;
use craft\console\Application;
use craft\events\RegisterUrlRulesEvent;
use craft\web\UrlManager;
use globalia\hubsync\models\Settings;
use globalia\hubsync\services\ContactsService;
use globalia\hubsync\services\EventsService;
use globalia\hubsync\services\HubspotApiService;
use globalia\hubsync\services\ProductsService;
use globalia\hubsync\services\DealsService;
use yii\base\Event;
use craft\commerce\Plugin as Commerce;
use globalia\hubsync\services\LineItemsService;

/**
 * HubSync plugin
 *
 * @method static HubSynC getInstance()
 * @method Settings getSettings()
 * @author Globalia <cms@globalia.ca>
 * @copyright Globalia
 * @license https://craftcms.github.io/license/ Craft License
 * @property-read HubspotApiService $hubspotApi
 * @property-read EventsService $eventsService
 * @property-read ContactsService $contactsService
 * @property-read ProductsService $productsService
 * @property-read DealsService $dealsService
 */
class HubSynC extends Plugin
{
    /**
     * @var HubSynC
     */
    public static HubSynC $plugin;

    public string $schemaVersion = '1.0.0';
    public bool $hasCpSettings = true;

    public static function config(): array
    {
        return [
            'components' => [
                'hubspotApi' => HubspotApiService::class,
                'events' => EventsService::class,
                'contacts' => ContactsService::class,
                'products' => ProductsService::class,
                'deals' => DealsService::class,
                'lineItems' => LineItemsService::class,
            ],
        ];
    }

    public function init()
    {
        parent::init();

        self::$plugin = $this;

        // Defer most setup tasks until Craft is fully initialized
        Craft::$app->onInit(function () {
            $this->attachEventHandlers();
        });
    }


    protected function createSettingsModel(): ?Model
    {
        return Craft::createObject(Settings::class);
    }

    private function attachEventHandlers(): void
    {
        // Add Commerce service to console application
        if (Craft::$app instanceof Application) {
            Event::on(
                Application::class,
                Application::EVENT_BEFORE_REQUEST,
                function() {
                    Craft::$app->set('commerce', Commerce::getInstance());
                }
            );
        }

        Event::on(
            UrlManager::class,
            UrlManager::EVENT_REGISTER_CP_URL_RULES,
            function (RegisterUrlRulesEvent $event) {
                $event->rules = array_merge([
                    'settings/plugins/hubsync' => 'hubsync/settings/edit'
                ], $event->rules);
            }
        );

        /*
         * See globalia\hubsync\services EventsService
         */
        $this->events->attachPluginEvents();
    }
}
