<?php
namespace Craft;

class m151207_000000_plainForm_add_ip_column extends BaseMigration
{
    public function safeUp()
    {
        $tableName = 'plainform_entries';

        if (!craft()->db->columnExists($tableName, 'ip')) {
            $this->addColumn($tableName, 'ip', array(
                'column'  => ColumnType::Varchar,
                'null'    => true,
                'default' => null,
            ));
        }
    }
}