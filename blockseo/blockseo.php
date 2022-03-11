<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

require_once(_PS_MODULE_DIR_ . 'blockseo/classes/SEOBlockModel.php');
require_once(_PS_MODULE_DIR_ . 'blockseo/classes/docx_reader.php');

class blockseo extends Module {
    /*
     * SYSTEM
     */


    public function __construct() {
        $this->name = 'blockseo';
        $this->tab = 'seo';
        $this->version = '0.1';
        $this->author = 'Cahri';
        $this->need_instance = 0;
        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
        $this->bootstrap = true;
        $this->displayName = $this->l('Block SEO');
        $this->description = $this->l('A module to manage seo blocks');
   
        $this->confirmUninstall = $this->l('Are you sure you would like to uninstall? All of the current settings will be reset.');


        parent::__construct();
    }

    public function install() {
        include(dirname(__FILE__).'/install/install.php');

        return parent::install() &&
        $this->registerHook('displayHomepageBlockSeoTop')&&
        $this->registerHook('displayHomepageBlockSeoBottom')&&
        $this->registerHook('displayCategoryBlockSeoTop')&&
        $this->registerHook('displayCategoryBlockSeoBottom');
    }

    public function uninstall() {
      
        include(dirname(__FILE__).'/install/uninstall.php');
        return parent::uninstall();
    }


    public function hookDisplayHomepageBlockSeoTop($params){
        $block_seo = $this->block_category_seo(0);
       
        $block_seo_content = "";

        if($block_seo !=null && count($block_seo)>0){
            $block_seo_content = $block_seo[0]["seo_text_top"];
        }

       
      
        $this->smarty->assign('seoblock',$block_seo_content);
       
        $html=  $this->display(__FILE__, 'views/templates/hook/blocks_seo.tpl');

        return $html;

    }

    public function hookDisplayHomepageBlockSeoBottom($params){
        $block_seo = $this->block_category_seo(0);
       
        $block_seo_content = "";

        if($block_seo !=null && count($block_seo)>0){
            $block_seo_content = $block_seo[0]["seo_text_bottom"];
        }

       
      
        $this->smarty->assign('seoblock',$block_seo_content);
       
        $html=  $this->display(__FILE__, 'views/templates/hook/blocks_seo.tpl');

        return $html;

    }

    public function hookDisplayCategoryBlockSeoTop($params){
        $id_category = Tools::getValue( 'id_category' );
       
	    $category = new Category($id_category, (int)$this->context->language->id);
		
		$category_name = $category->name;
		$html='';
		// check if category seo document exists
		if (is_file(_PS_ROOT_DIR_.'/docs/blockseo/top/'.$category_name.'.docx')){
			$filename = _PS_ROOT_DIR_.'/docs/blockseo/top/'.$category_name.'.docx';// or /var/www/html/file.docx  
			$doc = new Docx_reader();
			$doc->setFile($filename);
			
			if(!$doc->get_errors()) {
				$html = $doc->to_html();
			}
		}
        
		$block_seo = $this->block_category_seo($id_category);

        $block_seo_content = "";

        if($html != "")
			$block_seo_content = $html;
		else if($block_seo !=null && count($block_seo)>0){
            $block_seo_content = $block_seo[0]["seo_text_top"];
        }
       
        $this->smarty->assign('seoblock',$block_seo_content);
       
        $html=  $this->display(__FILE__, 'views/templates/hook/blocks_seo.tpl');

        return $html;
    }

    public function hookDisplayCategoryBlockSeoBottom($params){
        $id_category = Tools::getValue( 'id_category' );
       
	    $category = new Category($id_category, (int)$this->context->language->id);
		
		$category_name = $category->name;
		$html='';
		// check if category seo document exists
		if (is_file(_PS_ROOT_DIR_.'/docs/blockseo/bottom/'.$category_name.'.docx')){
			$filename = _PS_ROOT_DIR_.'/docs/blockseo/bottom/'.$category_name.'.docx';// or /var/www/html/file.docx  
			$doc = new Docx_reader();
			$doc->setFile($filename);
			
			if(!$doc->get_errors()) {
				$html = $doc->to_html();
			}
		}
        
		$block_seo = $this->block_category_seo($id_category);

        $block_seo_content = "";

        if($html != "")
			$block_seo_content = $html;
		else if($block_seo !=null && count($block_seo)>0){
            $block_seo_content = $block_seo[0]["seo_text_bottom"];
        }
       
        $this->smarty->assign('seoblock',$block_seo_content);
       
        $html=  $this->display(__FILE__, 'views/templates/hook/blocks_seo.tpl');

        return $html;
    }

    private function block_category_seo($id_category){
        $sql = 'SELECT * FROM ' . _DB_PREFIX_ . 'seoblock WHERE id_category = ' . $id_category ;
        $finalArray = array();
        $queryResult = Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
        return $queryResult;
    }

    public function url($action = null, $carousel = null, $slide = null, $token = true) {
        $url = $this->context->link->getAdminLink('AdminModules', $token) . '&configure=' . $this->name;
        if (!empty($action)) {
            $url .= '&action=' . $action;
        }
        if (!empty($carousel)) {
            $url .= '&block=' . $carousel;
        }
        if (!empty($slide)) {
            $url .= '&slide=' . $slide;
        }
        return $url;
    }


    /*
     * ADMIN
     */

    public function getContent() {
       
        $action = Tools::isSubmit('action') ? Tools::getValue('action') : false;
        $id_block = Tools::isSubmit('block') ? Tools::getValue('block') : false;


        if(Tools::isSubmit('save_seo_category_block')){
            $category_block = new SEOBlockModel();
            $this->saveCategoryBlock($category_block);
        }
        if(Tools::isSubmit('update_item')){
            $id_block = Tools::getValue("id_block");
            $block_position = Tools::getValue('block_position');
            $item = new SEOBlockModel($id_block);
            $item->block_position = $block_position;
            $this->saveCategoryBlock($item);
        }
        switch ($action) {
            case 'edit_homepageseoblock':
                return $this->editHomePageBlockForm(1,false);
                break;
            case 'edit_block':
                return $this->editBlockForm($id_block,false);
                break;
            case 'add_block':
                return $this->mainBlockForm();
                break;
            case 'delete_block':
                return $this->deleteBlock($block);    
           
            default :
                break;
        }
        return $this->blocksList();
    }


    public function blocksList(){
        $this->context->smarty->assign([
            'link' => $this->context->link,
            'blocks' => $this->seoBlocksList()
        ]);

        return $this->display(__FILE__, 'blocks_list.tpl');
    }

    public function seoBlocksList(){
        $sql = 'SELECT c.*, l.name AS category_name FROM `' . _DB_PREFIX_ . 'seoblock` c';
        $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` l ON (c.`id_category` = l.`id_category`)';
        $sql .= ' WHERE l.id_lang = ' . Context::getContext()->language->id;
        $sql .= ' ORDER BY id_category';
        return Db::getInstance(_PS_USE_SQL_SLAVE_)->executeS($sql);
    }

    /*
     * Blocks
     */

    public function mainBlockForm(){
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT'); 
  
        $fields_form = array();
        $fields_form[0]['form'] = array(
          'legend' => array(
            'title' => $this->l('Nouveau Block Seo')
          ),
          'input' => array(
            
                array(
                    'type' => 'categories',
                    'label' => $this->l('Catégorie'),
                    'name' => 'id_category',
                    'required' => false,
                    'lang' => true,
                    'class'=>'etage_category',
                    'tree' => array(
                        'id' => 'category',
                        'value' => Tools::getValue('id_category', '')
                    )
                ),array (
                    'type' => 'textarea',
                    'label' => $this->l ( 'Block SEO Haut de page:' ),
                    'name' => 'seo_text_top',
                    'autoload_rte' => true,
                    'required' => true,
                    'rows' => 10,
                    'cols' => 100,
                    'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
                )
                ,array (
                    'type' => 'textarea',
                    'label' => $this->l ( 'Block SEO Bas de page:' ),
                    'name' => 'seo_text_bottom',
                    'autoload_rte' => true,
                    'required' => true,
                    'rows' => 10,
                    'cols' => 100,
                    'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
                ),           
                
          ),
          'submit' => array(
            'title' => $this->l('Save'),
                  'class' => 'btn btn-default pull-right'
          )
        );
        $languages = Language::getLanguages();
    
        $helper = new HelperForm();
        
        
          $helper->name_controller = 'HomepageSettings';
          $helper->token = Tools::getAdminTokenLite('AdminModules');
          
          $helper->default_form_language = $default_lang;
          $helper->allow_employee_form_lang = $default_lang;
        $helper->languages = $this->context->controller->getLanguages();
          
          $helper->title = $this->displayName;
          $helper->show_toolbar = true;        // false -> remove toolbar
          $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
          $helper->submit_action = 'save_seo_category_block';
         
        $helper->tpl_vars['fields_value']['id_category'] = Tools::getValue('id_category', '');

        return "<div class='col-lg-8'>".$helper->generateForm($fields_form)."</div>";
    }
    
    public function editHomePageBlockForm($id_block){
        $block_seo = new SEOBlockModel($id_block);
        
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT'); 
  
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'tinymce' => true,
          'legend' => array(
            'title' => $this->l('Modifier Block SEO')
          ),
          'input' => array(
              array(
                  'type' => 'hidden',
                  'name'=>'id_block'
              ),
            array(
                'type' => 'hidden',
                'name' => 'id_category',
                'required' => false,
            )
            ,array (
                'type' => 'textarea',
                'label' => $this->l ( 'Block SEO Haut de page:' ),
                'name' => 'seo_text_top',
                'autoload_rte' => true,
                'required' => true,
                'rows' => 10,
                'cols' => 100,
                'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
            )
            ,array (
                'type' => 'textarea',
                'label' => $this->l ( 'Block SEO Bas de page:' ),
                'name' => 'seo_text_bottom',
                'autoload_rte' => true,
                'required' => true,
                'rows' => 10,
                'cols' => 100,
                'value'=>$block_seo->seo_text_bottom,
                'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
            ),           
           
                
          ),
          'submit' => array(
            'title' => $this->l('Save'),
                  'class' => 'btn btn-default pull-right'
          )
        );
        $languages = Language::getLanguages();
        $helper = new HelperForm();
        
        
          $helper->name_controller = 'HomepageSettings';
          $helper->token = Tools::getAdminTokenLite('AdminModules');
          
          $helper->default_form_language = $default_lang;
          $helper->allow_employee_form_lang = $default_lang;
        $helper->languages = $this->context->controller->getLanguages();
          
          $helper->title = $this->displayName;
          $helper->show_toolbar = true;        // false -> remove toolbar
          $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
          $helper->submit_action = 'update_item';
         
          
        $helper->tpl_vars['fields_value']['id_block'] = $id_block;
        $helper->tpl_vars['fields_value']['id_category'] = 0;
        $helper->tpl_vars['fields_value']['seo_text_top'] = $block_seo->seo_text_top;
        $helper->tpl_vars['fields_value']['seo_text_bottom'] = $block_seo->seo_text_bottom;
        $helper->tpl_vars['fields_value']['block_position'] = $block_seo->block_position;
      
        return "<div class='col-lg-8'>".$helper->generateForm($fields_form)."</div>";

    }
    public function editBlockForm($id_block){
        
        $block_seo = new SEOBlockModel($id_block);
       
        $default_lang = (int)Configuration::get('PS_LANG_DEFAULT'); 
  
        $fields_form = array();
        $fields_form[0]['form'] = array(
            'tinymce' => true,
          'legend' => array(
            'title' => $this->l('Modifier Block SEO')
          ),
          'input' => array(
              array(
                  'type' => 'hidden',
                  'name'=>'id_block'
              ),
            array(
                'type' => 'categories',
                'label' => $this->l('Catégorie'),
                'name' => 'id_category',
                'required' => false,
                'lang' => true,
                'class'=>'etage_category',
                'tree' => array(
                    'id' => 'category',
                    'selected_categories' => array($block_seo->id_category),
                    'value' => Tools::getValue('id_category', '')
                )
            )
            ,array (
                'type' => 'textarea',
                'label' => $this->l ( 'Block SEO Haut de page:' ),
                'name' => 'seo_text_top',
                'autoload_rte' => true,
                'required' => true,
                'rows' => 10,
                'cols' => 100,
                'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
            )
            ,array (
                'type' => 'textarea',
                'label' => $this->l ( 'Block SEO Bas de page:' ),
                'name' => 'seo_text_bottom',
                'autoload_rte' => true,
                'required' => true,
                'rows' => 10,
                'cols' => 100,
                'value'=>$block_seo->seo_text_bottom,
                'hint' => $this->l ( 'Invalid characters:' ).' <>;=#{}'
            ),                
                
           
                
          ),
          'submit' => array(
            'title' => $this->l('Save'),
                  'class' => 'btn btn-default pull-right'
          )
        );
        $languages = Language::getLanguages();
        $helper = new HelperForm();
        
        
          $helper->name_controller = 'HomepageSettings';
          $helper->token = Tools::getAdminTokenLite('AdminModules');
          
          $helper->default_form_language = $default_lang;
          $helper->allow_employee_form_lang = $default_lang;
        $helper->languages = $this->context->controller->getLanguages();
          
          $helper->title = $this->displayName;
          $helper->show_toolbar = true;        // false -> remove toolbar
          $helper->toolbar_scroll = true;      // yes - > Toolbar is always visible on the top of the screen.
          $helper->submit_action = 'update_item';
         
          
          $helper->tpl_vars['fields_value']['id_block'] = $id_block;
        $helper->tpl_vars['fields_value']['id_category'] = $block_seo->id_category;
        $helper->tpl_vars['fields_value']['seo_text_top'] = $block_seo->seo_text_top;
        $helper->tpl_vars['fields_value']['seo_text_bottom'] = $block_seo->seo_text_bottom;
        return "<div class='col-lg-8'>".$helper->generateForm($fields_form)."</div>";
    }
 
    public function deleteBlockSeo($id) {       
        $block_seo = new SEOBlockModel($id);
        $block_seo->delete();
    }
  
    public function saveCategoryBlock(&$category_block){
        if(Tools::getValue('id_category')!=""){
            $category_block->id_category = Tools::getValue('id_category');
            $category_block->seo_text_top = Tools::getValue('seo_text_top');
            $category_block->seo_text_bottom = Tools::getValue('seo_text_bottom');
            if (!$category_block->save()) {
                return 'Undefined error!';
            }
    
            
        }
        Tools::redirectAdmin($this->url());
           
    }
	 function read_file_docx($filePath){  
       $zip = new ZipArchive;
    $dataFile = 'word/document.xml';
    // Open received archive file
    if (true === $zip->open($filePath)) {
        // If done, search for the data file in the archive
        if (($index = $zip->locateName($dataFile)) !== false) {
            // If found, read it to the string
            $data = $zip->getFromIndex($index);
            // Close archive file
            $zip->close();
            // Load XML from a string
            // Skip errors and warnings
            $xml = new DOMDocument("1.0", "utf-8");
            $xml->loadXML($data, LIBXML_NOENT | LIBXML_XINCLUDE | LIBXML_NOERROR | LIBXML_NOWARNING|LIBXML_PARSEHUGE);
            $xml->encoding = "utf-8";
            // Return data without XML formatting tags
            $output =  $xml->saveXML();
            $output = str_replace("w:","",$output);

            return $output;
        }
        $zip->close();
    }
    // In case of failure return empty string
    return "";
 }  
 
   
    
}
