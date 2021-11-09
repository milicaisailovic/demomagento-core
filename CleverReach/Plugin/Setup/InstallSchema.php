<?php

namespace CleverReach\Plugin\Setup;

use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\Setup\InstallSchemaInterface;
use Magento\Framework\Setup\ModuleContextInterface;
use Magento\Framework\Setup\SchemaSetupInterface;
use Zend_Db_Exception;

class InstallSchema implements InstallSchemaInterface
{
    /**
     * Create cleverreach entity database table
     *
     * @param SchemaSetupInterface $setup
     * @param ModuleContextInterface $context
     * @throws Zend_Db_Exception
     */
    public function install(SchemaSetupInterface $setup, ModuleContextInterface $context)
    {
        $setup->startSetup();
        $tableName = $setup->getTable('cleverreach_entity');
        if ($setup->getConnection()->isTableExists($tableName) !== true) {
            $table = $setup->getConnection()
                ->newTable($tableName)
                ->addColumn(
                    'id',
                    Table::TYPE_INTEGER,
                    null,
                    [
                        'identity' => true,
                        'unsigned' => true,
                        'nullable' => false,
                        'primary' => true,
                        'auto_increment' => true,
                    ],
                    'Id'
                )
                ->addColumn(
                    'type',
                    Table::TYPE_TEXT,
                    128,
                    ['nullable' => false],
                    'Type'
                )
                ->addColumn(
                    'index_1',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index1'
                )
                ->addColumn(
                    'index_2',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index2'
                )
                ->addColumn(
                    'index_3',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index3'
                )
                ->addColumn(
                    'index_4',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index4'
                )
                ->addColumn(
                    'index_5',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index5'
                )
                ->addColumn(
                    'index_6',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index6'
                )
                ->addColumn(
                    'index_7',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index7'
                )
                ->addColumn(
                    'index_8',
                    Table::TYPE_TEXT,
                    255,
                    ['default' => null, 'nullable' => true],
                    'Index8'
                )
                ->addColumn(
                    'data',
                    Table::TYPE_TEXT,
                    Table::MAX_TEXT_SIZE,
                    ['nullable' => false],
                    'Data'
                );

            $setup->getConnection()->createTable($table);
        }

        $setup->endSetup();
    }
}
