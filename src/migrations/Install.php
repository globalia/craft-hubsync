<?php

namespace globalia\hubsync\migrations;

use Craft;
use craft\db\Migration;
use globalia\hubsync\models\hubspot\Contact;
use globalia\hubsync\models\hubspot\Deal;
use globalia\hubsync\models\hubspot\LineItem;
use globalia\hubsync\models\hubspot\Product;

/**
 * Install migration.
 */
class Install extends Migration
{
    public const SETTINGS_TABLE_NAME = '{{%chs_settings}}';


    /**
     * @inheritdoc
     */
    public function safeUp(): bool
    {
        $this->createTable(self::SETTINGS_TABLE_NAME, [
            'id' => $this->primaryKey(),
            'autoSendAddToCart' => $this->boolean()->defaultValue(false),
            'autoSendRemoveFromCart' => $this->boolean()->defaultValue(false),
            'autoSendPurchaseComplete' => $this->boolean()->defaultValue(false),
            'autoSyncProducts' => $this->boolean()->defaultValue(false),
            'autoSyncUsers' => $this->boolean()->defaultValue(false),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid(),
        ]);

        $this->createTable(Contact::tableName(), [
            'id' => $this->primaryKey(),
            'hubspotId' => $this->string(50),
            'craftId' => $this->string(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(Product::tableName(), [
            'id' => $this->primaryKey(),
            'hubspotId' => $this->string(50),
            'craftId' => $this->string(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(Deal::tableName(), [
            'id' => $this->primaryKey(),
            'hubspotId' => $this->string(50),
            'craftId' => $this->string(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);

        $this->createTable(LineItem::tableName(), [
            'id' => $this->primaryKey(),
            'hubspotId' => $this->string(50),
            'craftId' => $this->string(50),
            'dateCreated' => $this->dateTime()->notNull(),
            'dateUpdated' => $this->dateTime()->notNull(),
            'uid' => $this->uid()
        ]);


        return true;
    }

    /**
     * @inheritdoc
     */
    public function safeDown(): bool
    {
        $this->dropTableIfExists(self::SETTINGS_TABLE_NAME);
        $this->dropTableIfExists(Contact::tableName());
        $this->dropTableIfExists(Product::tableName());
        $this->dropTableIfExists(Deal::tableName());
        $this->dropTableIfExists(LineItem::tableName());

        return true;
    }
}
