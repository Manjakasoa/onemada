<?php
$sql = array();

$sql[] = 'DROP TABLE `'._DB_PREFIX_.'seoblock`';
foreach ($sql as $query) {

    if (Db::getInstance()->execute($query) == false) {

        return false;

    }

}

?>