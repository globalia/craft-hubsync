<?php

namespace globalia\hubsync\models;

use Craft;
use craft\base\Model;

/**
 * HubSynC settings
 */
class Settings extends Model
{
    /**
     * The Hubspot API Token
     *
     * @var string|null
     */
    public ?string $apiToken = null;

    /**
     * Whether add to cart events should be automatically sent
     *
     * @var bool
     */
    public bool $autoSendAddToCart = true;

    /**
     * Whether remove from cart events should be automatically sent
     *
     * @var bool
     */
    public bool $autoSendRemoveFromCart = true;

    /**
     * Whether purchase complete events should be automatically sent
     *
     * @var bool
     */
    public bool $autoSendPurchaseComplete = true;

    /**
     * List of custom properties to send to Hubspot Contacts
     *
     * @var array
     */
    public array $customContactFields = [
        [
            'hubspotField' => 'firstname',
            'craftField' => 'firstName'
        ],
        [
            'hubspotField' => 'lastname',
            'craftField' => 'lastName'
        ]
    ];

    /**
     * Wether to sync users on save
     *
     * @var bool
     */
    public bool $syncUsersOnSave = true;

    /**
     * List of custom properties to sent to Hubspot Products
     * 
     * @var array
     */
    public array $customProductFields = [
        [
            'hubspotField' => 'name',
            'craftField' => 'title'
        ],
        [
            'hubspotField' => 'price',
            'craftField' => 'price'
        ],
    ];

    /**
     * Wether to sync products on save
     * 
     * @var bool
     */
    public bool $syncProductsOnSave = true;

    /**
     * The product field to use as the product SKU
     * 
     * @var string
     */
     public string $productSkuField = 'hs_sku';

    /**
     * List of custom properties sent to Hubspot Deals
     * 
     * @var array
     */
    public array $customDealFields = [
        [
            'hubspotField' => 'dealname',
            'craftField' => 'title'
        ],
        [
            'hubspotField' => 'amount',
            'craftField' => 'totalPrice'
        ],
     ];

    /**
    * Wether to sync deals on order complete
    * 
    * @var bool
    */
    public bool $syncDealsOnOrderComplete = true;

    /**
     * The pipeline to use when creating deals
     * 
     * @var string
     */
    public string $dealPipeline = 'default';

    /**
     * The stage to use when creating deals
     * 
     * @var string
     */
    public string $dealStageDefault = 'qualifiedtobuy';

    /**
     * The stage to use when completing deals
     * 
     * @var string
     */
    public string $dealStageCompleted = 'closedwon';

    /**
     * The Hubspot ID of the deal owner
     * 
     * @var string|null
     */
    public ?string $dealOwnerId = null;

    /**
     * Wether to sync Line Items when syncing orders
     * 
     * @var bool
     */
    public bool $syncLineItemsWithOrders = true;

    /**
     * Wether to sync Line Items on Cart events
     * 
     * @var bool
     */
    public bool $syncLineItemsOnCartEvents = true;


    /**
     * @return array
     */
    public function rules(): array
    {
        return [
            [['apiToken'], 'trim'],
            [['apiToken'], 'default', 'value' => null],
            [
                [
                    'autoSendAddToCart',
                    'autoSendRemoveFromCart',
                    'autoSendPurchaseComplete',
                ],
                'boolean',
            ],
        ];
    }
}
