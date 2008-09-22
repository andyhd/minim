<?php
require_once realpath(dirname(__FILE__)).'/../config.php';

if ($argc and in_array('--verbose', $argv))
{
    minim()->debug = TRUE;
}

foreach (minim('orm')->_available_models() as $name => $file)
{
    $model = minim('orm')->{$name};
    $fields = array();
    foreach ($model->_fields as $name => $field)
    {
        $not_null = $field->getAttribute('not_null') ? 'NOT NULL' : '';
        $auto_increment = $field->getAttribute('autoincrement') ? ' AUTO_INCREMENT' : '';
        $primary_key = $name == 'id' ? ' PRIMARY KEY' : '';
        $type = '';
        switch ($field->_type)
        {
            case 'int':
                $type = 'INTEGER';
                break;
            case 'text':
            case 'slug':
                $type = 'TEXT';
                if ($max_length = $field->getAttribute('maxlength'))
                {
                    $type = "VARCHAR($max_length)";
                }
                break;
            case 'timestamp':
                $type = 'DATETIME';
                break;
            default:
                die("Unknown field type ({$field->_type})");
        }
        $fields[] = "`$name` $type $not_null $auto_increment $primary_key";
    }
    $fields = join(', ', $fields);
    $sql = <<<SQL
CREATE TABLE `{$model->_table}` ($fields) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
    echo trim(preg_replace('/\s+/', ' ', $sql)), "\n";
}
