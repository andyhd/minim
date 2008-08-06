<?php
$root = realpath(dirname(__FILE__));
require_once $root.'/../lib/minim.php';
require_once minim()->lib('breve-refactor');

if ($argc and in_array('--verbose', $argv))
{
    minim()->debug = TRUE;
}

$models = array();
$dh = opendir(minim()->root."/models");
if (!$dh)
{
    die('Could not open '.minim()->root.'/models directory');
}
while ($dl = readdir($dh))
{
    if (substr($dl, strlen($dl)-4) == '.php')
    {
        $file = minim()->root."/models/$dl";
        require_once $file;
    }
}

minim()->log("Loaded models:".print_r(array_keys(breve()->_managers), TRUE));

foreach (breve()->_managers as $name => $model)
{
    $fields = array();
    foreach ($model->_fields as $name => $field)
    {
        $not_null = $field->getAttribute('not_null') ? 'NOT NULL' : '';
        $auto_increment = $field->getAttribute('autoincrement') ? ' AUTO_INCREMENT' : '';
        $primary_key = $name == 'id' ? $primary_key = ' PRIMARY KEY' : '';
        $type = '';
        $class = get_class($field);
        switch ($class)
        {
            case 'BreveInt':
                $type = 'INTEGER';
                break;
            case 'BreveText':
            case 'BreveSlug':
                $type = 'TEXT';
                if ($max_length = $field->getAttribute('maxlength'))
                {
                    $type = "VARCHAR($max_length)";
                }
                break;
            case 'BreveTimestamp':
                $type = 'DATETIME';
                break;
            default:
                die("Unknown field type ($class)");
        }
        $fields[] = "`$name` $type $not_null $auto_increment $primary_key";
    }
    $fields = join(', ', $fields);
    $sql = <<<SQL
CREATE TABLE `{$model->_table}` ($fields) ENGINE=InnoDB DEFAULT CHARSET=utf8;
SQL;
    minim()->log(trim(preg_replace('/\s+/', ' ', $sql)));
}

foreach (minim()->log_msgs as $msg)
{
    echo $msg, "\n";
}
