<?php

namespace globalia\hubsync\services;

use Craft;
use craft\elements\User;
use craft\events\ModelEvent;
use craft\helpers\Queue;
use yii\base\Component;
use yii\base\Event;
use globalia\hubsync\jobs\SyncUser;
use craft\commerce\elements\Product;
use globalia\hubsync\jobs\SyncProduct;
use craft\commerce\elements\Order;
use craft\commerce\events\LineItemEvent;
use craft\events\RegisterElementTableAttributesEvent;
use craft\events\SetElementTableAttributeHtmlEvent;
use globalia\hubsync\HubSynC;
use globalia\hubsync\jobs\RemoveLineItem;
use globalia\hubsync\jobs\SyncLineItem;
use globalia\hubsync\jobs\SyncOrder;
use globalia\hubsync\models\hubspot\Contact;
use globalia\hubsync\models\hubspot\Product as HubspotProduct;
use globalia\hubsync\models\hubspot\Deal;
use craft\base\Element;
use craft\events\RegisterElementActionsEvent;
use globalia\hubsync\elements\actions\SyncToHubspot;

/**
 * Events Service service
 */
class EventsService extends Component
{
    public function attachPluginEvents()
    {
        $settings = HubSynC::$plugin->settings;

        $this->registerElementsTableAttributes();
        $this->registerElementActions();

        if($settings->syncUsersOnSave) {
            $this->registerUserEvents();
        }

        if (Craft::$app->plugins->isPluginEnabled('commerce')) {
            if($settings->syncProductsOnSave) {
                $this->registerProductEvents();
            }
            if($settings->syncDealsOnOrderComplete) {
                $this->registerOrderEvents();
            }
            if($settings->syncLineItemsOnCartEvents) {
                $this->registerCartEvents();
            }
        }
    }

    private function registerUserEvents()
    {
        Event::on(
            User::class,
            User::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (($event->sender->enabled && $event->sender->getEnabledForSite())) {
                    Queue::push(new SyncUser([
                        'userId' => $event->sender->id,
                    ]));
                }
            }
        );
    }

    private function registerProductEvents()
    {
        Event::on(
            Product::class,
            Product::EVENT_AFTER_SAVE,
            function (ModelEvent $event) {
                if (($event->sender->enabled)) {
                    Queue::push(new SyncProduct([
                        'productId' => $event->sender->id,
                    ]));
                }
            }
        );
    }

    private function registerOrderEvents()
    {

        Event::on(Order::class, Order::EVENT_AFTER_COMPLETE_ORDER, function (Event $e) {
            $order = $e->sender;

            Queue::push(new SyncOrder([
                'orderId' => $order->id,
            ]));

        });
    }

    public function registerCartEvents()
    {
        Event::on(Order::class, Order::EVENT_AFTER_ADD_LINE_ITEM, function (LineItemEvent $e) {
            $lineItem = $e->lineItem;

            Queue::push(new SyncOrder([
                'orderId' => $lineItem->order->id,
            ]));

            // Queue::push(new SyncLineItem([
            //     'itemId' => $lineItem->id,
            // ]));

        });

        // Check to make sure Order::EVENT_AFTER_REMOVE_LINE_ITEM is defined
        if (defined(Order::class . '::EVENT_AFTER_REMOVE_LINE_ITEM')) {
            Event::on(Order::class, Order::EVENT_AFTER_REMOVE_LINE_ITEM, function (LineItemEvent $e) {
                $lineItem = $e->lineItem;
                Queue::push(new SyncOrder([
                    'orderId' => $lineItem->order->id,
                ]));
                Queue::push(new RemoveLineItem([
                    'itemId' => $lineItem->id,
                ]));
            });
        }
    }

    public function registerElementsTableAttributes()
    {
        Event::on(
            User::class,
            User::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function (RegisterElementTableAttributesEvent $event) {
                $event->tableAttributes['hubspot'] = [
                    'label' => Craft::t(HubSynC::$plugin->handle, 'Hubspot Synced'),
                ];
            }
        );

        Event::on(
            User::class,
            User::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            function (SetElementTableAttributeHtmlEvent $event) {
                if ($event->attribute === 'hubspot') {
                    $contact = Contact::find()
                    ->where(['craftId' => $event->sender->id])
                    ->one();
                    $event->html = $contact ?
                        Craft::t(HubSynC::$plugin->handle, 'Yes') :
                        Craft::t(HubSynC::$plugin->handle, 'No');
                    $event->handled = true;
                }
            }
        );

        Event::on(
            Product::class,
            Product::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function (RegisterElementTableAttributesEvent $event) {
                $event->tableAttributes['hubspot'] = [
                    'label' => Craft::t(HubSynC::$plugin->handle, 'Hubspot Synced'),
                ];
            }
        );

        Event::on(
            Product::class,
            Product::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            function (SetElementTableAttributeHtmlEvent $event) {
                if ($event->attribute === 'hubspot') {
                    $product = HubspotProduct::find()
                    ->where(['craftId' => $event->sender->id])
                    ->one();
                    $event->html = $product ?
                        Craft::t(HubSynC::$plugin->handle, 'Yes') :
                        Craft::t(HubSynC::$plugin->handle, 'No');
                    $event->handled = true;
                }
            }
        );

        Event::on(
            Order::class,
            Order::EVENT_REGISTER_TABLE_ATTRIBUTES,
            function (RegisterElementTableAttributesEvent $event) {
                $event->tableAttributes['hubspot'] = [
                    'label' => Craft::t(HubSynC::$plugin->handle, 'Hubspot Synced'),
                ];
            }
        );

        Event::on(
            Order::class,
            Order::EVENT_SET_TABLE_ATTRIBUTE_HTML,
            function (SetElementTableAttributeHtmlEvent $event) {
                if ($event->attribute === 'hubspot') {
                    $deal = Deal::find()
                    ->where(['craftId' => $event->sender->id])
                    ->one();
                    $event->html = $deal ?
                        Craft::t(HubSynC::$plugin->handle, 'Yes') :
                        Craft::t(HubSynC::$plugin->handle, 'No');
                    $event->handled = true;
                }
            }
        );
    }

    public function registerElementActions()
    {
        Event::on(
            User::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = SyncToHubspot::class;
            }
        );

        Event::on(
            Product::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = SyncToHubspot::class;
            }
        );

        Event::on(
            Order::class,
            Element::EVENT_REGISTER_ACTIONS,
            function (RegisterElementActionsEvent $event) {
                $event->actions[] = SyncToHubspot::class;
            }
        );
    }
}
