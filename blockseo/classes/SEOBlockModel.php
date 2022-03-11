<?php

class SEOBlockModel extends ObjectModel {

    public $id_block = null;
   
    public $id_category= null; 

    public $seo_text_top = null;
    
    public $seo_text_bottom = null;

    public static $definition = [
        'table' => 'seoblock',
        'primary' => 'id_block',
        'fields' => [
            'id_block' => ['type' => self::TYPE_INT, 'validate' => 'isNullOrUnsignedId', 'copy_post' => false],
            'id_category' => ['type' => self::TYPE_INT, 'validate' => 'isUnsignedInt', 'required' => false],
            'seo_text_top' => ['type' => self::TYPE_HTML,'validate' => 'isCleanHtml','required' => false],
            'seo_text_bottom' => ['type' => self::TYPE_HTML,'validate' => 'isCleanHtml','required' => false],
            
        ]
    ];

}
