<?php 
$sql = array();



$sql[] = 'CREATE TABLE IF NOT EXISTS '._DB_PREFIX_.'seoblock ( 
    `id_block` INT NOT NULL AUTO_INCREMENT , 
    `id_category` INT DEFAULT 0,
    `seo_text_top` TEXT NOT NULL ,
    `seo_text_bottom` TEXT NOT NULL,
    PRIMARY KEY (`id_block`)
) ENGINE = InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1;';

$sql[] = "INSERT INTO `seoblock` (`id_block`, `id_category`,`seo_text`) VALUES (NULL, 0, '',0);";
foreach ($sql as $query) {

    if (Db::getInstance()->execute($query) == false) {

        return false;

    }

}
