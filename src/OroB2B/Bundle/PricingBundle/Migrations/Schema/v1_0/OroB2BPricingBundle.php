<?php

namespace OroB2B\Bundle\PricingBundle\Migrations\Schema\v1_0;

use Doctrine\DBAL\Schema\Schema;

use Oro\Bundle\MigrationBundle\Migration\Migration;
use Oro\Bundle\MigrationBundle\Migration\QueryBag;

class OroB2BPricingBundle implements Migration
{
    /**
     * {@inheritdoc}
     */
    public function up(Schema $schema, QueryBag $queries)
    {
        /** Tables generation **/
        $this->createOroB2BPriceListCurrencyTable($schema);
        $this->createOroB2BPriceListTable($schema);
        $this->createOroB2BPriceProductTable($schema);

        /** Foreign keys generation **/
        $this->addOroB2BPriceListCurrencyForeignKeys($schema);
        $this->addOroB2BPriceProductForeignKeys($schema);
    }

    /**
     * Create orob2b_price_list_currency table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceListCurrencyTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_list_currency');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('price_list_id', 'integer', []);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->setPrimaryKey(['id']);
        $table->addIndex(['price_list_id'], 'idx_f468ecaa5688ded7', []);
    }

    /**
     * Create orob2b_price_list table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceListTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_list');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('name', 'string', ['length' => 255]);
        $table->addColumn('is_default', 'boolean', []);
        $table->addColumn('created_at', 'datetime', []);
        $table->addColumn('updated_at', 'datetime', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Create orob2b_price_product table
     *
     * @param Schema $schema
     */
    protected function createOroB2BPriceProductTable(Schema $schema)
    {
        $table = $schema->createTable('orob2b_price_product');
        $table->addColumn('id', 'integer', ['autoincrement' => true]);
        $table->addColumn('price_list_id', 'integer', []);
        $table->addColumn('product_id', 'integer', []);
        $table->addColumn('unit_code', 'string', ['length' => 255]);
        $table->addColumn('product_sku', 'string', ['length' => 255]);
        $table->addColumn('quantity', 'float', []);
        $table->addColumn('unit_code', 'string', ['length' => 255]);
        $table->addColumn('value', 'float', []);
        $table->addColumn('currency', 'string', ['length' => 3]);
        $table->addUniqueIndex(
            ['product_id', 'price_list_id', 'quantity', 'unit_code', 'currency'],
            'orob2b_pricing_price_list_uidx'
        );
        $table->addIndex(['price_list_id'], 'idx_bcde766d5688ded7', []);
        $table->addIndex(['product_id'], 'idx_bcde766d4584665a', []);
        $table->addIndex(['unit_code'], 'idx_bcde766dfbd3d1c2', []);
        $table->setPrimaryKey(['id']);
    }

    /**
     * Add orob2b_price_list_currency foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BPriceListCurrencyForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_price_list_currency');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_list'),
            ['price_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }

    /**
     * Add orob2b_price_product foreign keys.
     *
     * @param Schema $schema
     */
    protected function addOroB2BPriceProductForeignKeys(Schema $schema)
    {
        $table = $schema->getTable('orob2b_price_product');
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_price_list'),
            ['price_list_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product'),
            ['product_id'],
            ['id'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
        $table->addForeignKeyConstraint(
            $schema->getTable('orob2b_product_unit'),
            ['unit_code'],
            ['code'],
            ['onUpdate' => null, 'onDelete' => 'CASCADE']
        );
    }
}
