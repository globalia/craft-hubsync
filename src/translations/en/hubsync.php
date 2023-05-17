<?php


return [
    'tab.general' => 'General',
    'tab.contacts' => 'Contacts',
    'tab.products' => 'Products',
    'tab.deals' => 'Deals',
    'tab.line-items' => 'Line Items',

    'table.add-field' => 'Add a custom field',
    'table.craft-field.heading' => 'Craft Field',
    'table.craft-field.placeholder' => 'Field Handle',
    'table.hubspot-field.heading' => 'Hubspot Property',
    'table.hubspot-field.placeholder' => 'Property Internal Name',

    'token.invalid' => "Token is invalid!",
    'token.missing-scopes.title' => "Missing Scopes",
    'token.missing-scopes.description' => "You must give your private app access to the following scopes in order to use this plugin",

    'settings.apiToken.label' => 'Private App Access Token',
    'settings.apiToken.instructions' => 'To connect this plugin to your Hubspot account. (Create a [Private App](https://developers.hubspot.com/docs/api/private-apps).)',

    'settings.syncUsersOnSave.label' => 'Sync Craft CMS Users to Hubspot CRM Contacts',
    'settings.syncUsersOnSave.instructions' => 'Sync users to Hubspot when they are saved.',
    'settings.customContactFields.label' => 'Custom Contact Fields',
    'settings.customContactFields.instructions' => 'Email & Name are synced by default, add any additional fields you want to sync.',

    'settings.syncProductsOnSave.label' => 'Sync Commerce Products to Hubspot CRM Products',
    'settings.syncProductsOnSave.instructions' => 'Whether to sync on products save.',
    'settings.productSkuField.label' => 'Hubspot SKU Property',
    'settings.productSkuField.instructions' => 'Internal value of the property used to match the Craft `sku`, default is `hs_sku`.',
    'settings.customProductFields.label' => 'Custom Product Fields',
    'settings.customProductFields.instructions' => '...',

    'settings.syncDealsOnOrderComplete.label' => 'Sync Commerce Orders to Hubspot CRM Deals',
    'settings.syncDealsOnOrderComplete.instructions' => 'Whether to sync on order complete.',
    'settings.dealPipeline.label' => 'Hubspot Deal Pipeline',
    'settings.dealPipeline.instructions' => 'Determines which pipeline is used when creating deals.',
    'settings.dealStageDefault.label' => 'Default Stage to Use',
    'settings.dealStageDefault.instructions' => '"Determines which stage is used when creating deals.',
    'settings.dealStageCompleted.label' => 'Pipeline Stage to Use when Order is Completed',
    'settings.dealStageCompleted.instructions' => 'Determines which pipeline stage is used when completing an order.',
    'settings.dealOwnerId.label' => 'Deal Owner',
    'settings.dealOwnerId.instructions' => 'Determines which user is assigned to the deal.',
    'settings.customDealFields.label' => 'Custom Deal Fields',
    'settings.customDealFields.instructions' => '...',

    'settings.syncLineItemsWithOrders.label' => 'Sync Line Items with Hubspot Deals',
    'settings.syncLineItemsWithOrders.instructions' => 'Whether to sync line items when syncing Orders.',
    'settings.syncLineItemsOnCartEvents.label' => 'Sync Line Items on Cart events',
    'settings.syncLineItemsOnCartEvents.instructions' => '"Whether to sync line items on `add` and `remove` events on the cart',
];
