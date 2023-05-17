# HubSynC for Craft CMS

HubSynC lets you connect your Craft Commerce website to Hubspot CRM and synchronize products, users and orders from Craft to Hubspot.

## Features

- Synchronize Commerce Products and Hubspot Products automatically whenever a product is updated.
- Synchronize Craft Users to Hubspot Contacts automatically whenever a user is updated.
- Synchronize Commerce Orders to Hubspot deals automatically whenever an order is updated.
- Synchronize Commerce Carts to update Line Items on Hubspot whenever a product is added or removed from a cart.
- Add Custom Fields to Products, Users and Orders synchronizations.
- Manually Synchronize Users, Orders and Products from the CP via custom actions in Elements lists.
- Visualize Synchronization status in Elements List via a custom column.

#### Data Synchronization

Synchonize data between Craft Commerce and Hubspot CRM automatically or manually.



## Requirements

This plugin requires Craft CMS 4.3 or later, and PHP 8.0 or later. It also requires the [Commerce](https://plugins.craftcms.com/commerce) plugin.

## Installation

You can install this plugin from the Plugin Store in Craft Control Panel or with Composer.

#### With Composer

Open your terminal and run the following commands:

```bash
# go to the project directory
cd /path/to/my-project

# tell Composer to load the plugin
composer require globalia/craft-hubsync

# tell Craft to install the plugin
./craft plugin/install hubsync
```

## Configuration

#### Hubspot Authentication

In order to use this plugin, you need to create a *Hubspot Private app* and generate an *Access Token*.
[Hubspot Documentation](https://developers.hubspot.com/docs/api/private-apps)