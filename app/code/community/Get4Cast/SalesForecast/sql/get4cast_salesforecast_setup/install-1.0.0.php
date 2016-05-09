<?php

$this->startSetup();

########################################################################

$table = new Varien_Db_Ddl_Table();

$table->setName($this->getTable('get4cast_salesforecast/config'));
$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$table->addColumn(
    'entity_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, 
    10, 
    array(
        'auto_increment' => true,
        'unsigned' => true,
        'nullable'=> false,
        'primary' => true
    )
);
$table->addColumn(
    'created_at', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'updated_at', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => true,
    )
);
$table->addColumn(
    'config', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'value', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);

$this->getConnection()->createTable($table);

########################################################################

$table = new Varien_Db_Ddl_Table();

$table->setName($this->getTable('get4cast_salesforecast/forecast'));
$table->setOption('type', 'InnoDB');
$table->setOption('charset', 'utf8');

$table->addColumn(
    'entity_id',
    Varien_Db_Ddl_Table::TYPE_INTEGER, 
    10, 
    array(
        'auto_increment' => true,
        'unsigned' => true,
        'nullable'=> false,
        'primary' => true
    )
);
$table->addColumn(
    'report_key', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'created_at', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'updated_at', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => true,
    )
);
$table->addColumn(
    'store_group_id', 
    Varien_Db_Ddl_Table::TYPE_INTEGER, 
    10,
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'store_group_name', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'email', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'period_start', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'period_end', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'forecast_date_start', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'forecast_date_end', 
    Varien_Db_Ddl_Table::TYPE_DATETIME, 
    null, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'price', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'payment_status', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'url', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);
$table->addColumn(
    'status', 
    Varien_Db_Ddl_Table::TYPE_VARCHAR, 
    255, 
    array(
        'nullable' => false,
    )
);

$this->getConnection()->createTable($table);

########################################################################

$this->endSetup();
