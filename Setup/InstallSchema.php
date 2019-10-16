<?php
namespace Sp\Orderattachment\Setup;

use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Adapter\AdapterInterface;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Installs DB schema for a module
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @return void
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $installer = $setup;
        $installer->startSetup();

        /**
          * Create table 'sp_orderattachment'
          */
         $table = $installer->getConnection()->newTable(
                 $installer->getTable('sp_orderattachment')
             )->addColumn(
                 'attachment_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 ['identity' => true, 'nullable' => false, 'primary' => true],
                 'Attachment ID'
             )->addColumn(
                 'quote_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 ['unsigned' => true, 'nullable' => true],
                 'Quote ID'
             )->addColumn(
                 'order_id',
                 \Magento\Framework\DB\Ddl\Table::TYPE_INTEGER,
                 null,
                 ['unsigned' => true, 'nullable' => true, 'default'  => NULL],
                 'Order ID'
             )->addColumn(
                'path',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false],
                'Path'
             )->addColumn(
                'comment',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                null,
                ['nullable' => false, 'default'  => ''],
                'Comment'
            )->addColumn(
                'hash',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'length' => 32],
                'Hash'
            )->addColumn(
                'type',
                \Magento\Framework\DB\Ddl\Table::TYPE_TEXT,
                32,
                ['nullable' => false, 'length' => 32],
                'File Type'
            )->addColumn(
                'uploaded_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                [ 'nullable' => false],
                'Uploaded at'
            )->addColumn(
                'modified_at',
                \Magento\Framework\DB\Ddl\Table::TYPE_DATETIME,
                null,
                ['nullable' => false],
                'Modified at'
             )->addIndex(
                 $installer->getIdxName('sp_orderattachment', ['quote_id']),
                 ['quote_id']
             )->addIndex(
                 $installer->getIdxName('sp_orderattachment', ['order_id']),
                 ['order_id']
             )->addIndex(
                 $installer->getIdxName('sp_orderattachment', ['hash']),
                 ['hash']
             )->addForeignKey(
                 $installer->getFkName('sp_orderattachment', 'order_id', 'sales_order', 'entity_id'),
                 'order_id',
                 $installer->getTable('sales_order'),
                 'entity_id',
                 \Magento\Framework\DB\Ddl\Table::ACTION_CASCADE
             )->setComment(
                 'Sp Orderattachment Table'
             );

         $installer->getConnection()->createTable($table);

        $installer->endSetup();
    }
}
