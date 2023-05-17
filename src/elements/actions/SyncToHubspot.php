<?php

namespace globalia\hubsync\elements\actions;

use Craft;
use craft\base\ElementAction;
use globalia\hubsync\jobs\SyncUser;
use globalia\hubsync\jobs\SyncProduct;
use globalia\hubsync\jobs\SyncOrder;
use craft\helpers\Queue;

/**
 * Sync To Hubspot element action
 */
class SyncToHubspot extends ElementAction
{
    public static function displayName(): string
    {
        return Craft::t('hubsync', 'Sync To Hubspot');
    }

    public function getTriggerHtml(): ?string
    {
        Craft::$app->getView()->registerJsWithVars(fn ($type) => <<<JS
            (() => {
                new Craft.ElementActionTrigger({
                    type: $type,

                    // Whether this action should be available when multiple elements are selected
                    bulk: true,

                    // Return whether the action should be available depending on which elements are selected
                    validateSelection: (selectedItems) {
                      return true;
                    },

                    // Uncomment if the action should be handled by JavaScript:
                    // activate: () => {
                    //   Craft.elementIndex.setIndexBusy();
                    //   const ids = Craft.elementIndex.getSelectedElementIds();
                    //   // ...
                    //   Craft.elementIndex.setIndexAvailable();
                    // },
                });
            })();
        JS, [static::class]);

        return null;
    }

    public function performAction(Craft\elements\db\ElementQueryInterface $query): bool
    {
        $elements = $query->all();

        foreach ($elements as $element) {
            $type = get_class($element);

            switch($type) {
                case 'craft\\commerce\\elements\\Order':
                    $this->syncOrder($element);
                    break;
                case 'craft\\commerce\\elements\\Product':
                    $this->syncProduct($element);
                    break;
                case 'craft\\elements\\User':
                    $this->syncUser($element);
                    break;
                default:
                    break;
            }

            $this->setMessage(Craft::t('hubsync', 'Synced to Hubspot'));
        }

        return true;
    }

    public function syncUser($user)
    {
        Queue::push(new SyncUser([
            'userId' => $user->id,
        ]));
    }

    public function syncProduct($product)
    {
        Queue::push(new SyncProduct([
            'productId' => $product->id,
        ]));
    }

    public function syncOrder($order)
    {
        Queue::push(new SyncOrder([
            'orderId' => $order->id,
        ]));
    }
}
