<?php

include_once  _PS_MODULE_DIR_ . 'cednewegg/classes/CedNeweggProduct.php';

class AdminCedNeweggProductsController extends ModuleAdminController {

    public function __construct()
    {
        $this->context = Context::getContext();
        $this->bootstrap = true;
        $this->table = 'product';
        $this->className = 'Product';
        $this->lang = false;
        $this->list_no_link = true;
        $this->explicitSelect = true;
        $this->bulk_actions = array(
            'upload_product' => array(
                'text' => ('Upload Product'),
                'icon' => 'icon-upload',
            ),
            'sync' => array(
                'text' => ('Sync Quantity'),
                'icon' => 'icon-refresh',
            ),
            'remove' => array(
                'text' => ('Delete From Newegg'),
                'icon' => 'icon-trash',
            ),

            'assign_profile' => array(
                'text' => ('Assign Profile'),
                'icon' => 'icon-link',
            ),
            'remove_profile' => array(
                'text' => ('Remove Profile'),
                'icon' => 'icon-unlink',
            ),

            'include' => array(
                'text' => ('Include Item(s)'),
                'icon' => 'icon-check',
            ),
            'exclude' => array(
                'text' => ('Exclude Item(s)'),
                'icon' => 'icon-remove',
            )
        );

        $this->profile_array = array();
        $dbp = Db::getInstance();
        $sql = 'SELECT `id`,`profile_name` FROM `' . _DB_PREFIX_ . 'newegg_profile`';
        $res = $dbp->executeS($sql);
        if (is_array($res) & count($res) > 0) {
            foreach ($res as $r) {
                $this->profile_array[$r['id']] = $r['profile_name'];
            }
        }
        if(!isset($account_id)){
            $account_id = '';
        }
        parent::__construct();

        $this->_join .= '
            LEFT JOIN `' . _DB_PREFIX_ . 'stock_available` sav ON (sav.`id_product` = a.`id_product` 
            AND sav.`id_product_attribute` = 0
            ' . StockAvailable::addSqlShopRestriction(null, null, 'sav') . ') ';

        $alias = 'sa';
        $alias_image = 'image_shop';

        $id_shop = Shop::isFeatureActive() && Shop::getContext() == Shop::CONTEXT_SHOP ?
            (int)$this->context->shop->id : 'a.id_shop_default';
        $this->_join .= ' JOIN `' . _DB_PREFIX_ . 'product_shop` sa ON (a.`id_product` = sa.`id_product` 
            AND sa.id_shop = ' . $id_shop . ')

            LEFT JOIN `' . _DB_PREFIX_ . 'product_lang` b 
            ON (a.`id_product` = b.id_product AND b.id_shop = ' . $id_shop . ' 
            AND b.`id_lang`="' . (int)$this->context->language->id . '")

            LEFT JOIN `' . _DB_PREFIX_ . 'category_lang` cl 
            ON (' . $alias . '.`id_category_default` = cl.`id_category` 
            AND b.`id_lang` = cl.`id_lang` AND cl.id_shop = ' . $id_shop . ')

            LEFT JOIN `' . _DB_PREFIX_ . 'shop` shop ON (shop.id_shop = ' . $id_shop . ')

            LEFT JOIN `' . _DB_PREFIX_ . 'newegg_profile_product` cbprofile ON (cbprofile.`product_id` = a.`id_product`)
            LEFT JOIN `' . _DB_PREFIX_ . 'newegg_profile` cbprof ON (cbprof.`id` = cbprofile.`profile_id`)
            LEFT JOIN `' . _DB_PREFIX_ . 'image_shop` image_shop 
            ON (image_shop.`id_product` = a.`id_product` 
            AND image_shop.`cover` = 1 AND image_shop.id_shop = ' . $id_shop . ')

            LEFT JOIN `' . _DB_PREFIX_ . 'image` i ON (i.`id_image` = image_shop.`id_image`)
            
            LEFT JOIN `' . _DB_PREFIX_ . 'product_download` pd ON (pd.`id_product` = a.`id_product` AND pd.`active` = 1)';
        $this->_select .= 'shop.`name` AS `shopname`, a.`id_shop_default`, ';
        $this->_select .= 'cbprofile.`profile_id` AS `profile_id`, ';
        $this->_select .= 'cbprof.`profile_name` AS `profile_name`, ';
        $this->_select .= 'cbprofile.`newegg_validation_error` AS `error_message`, ';
        $this->_select .= $alias_image . '.`id_image` AS `id_image`, a.`id_product` as `id_temp`,
        cl.`name` AS `name_category`, '
            . $alias . '.`price` AS `price_final`, a.`is_virtual`, pd.`nb_downloadable`, 
        sav.`quantity` AS `sav_quantity`, '
            . $alias . '.`active`, IF(sav.`quantity`<=0, 1, 0) AS `badge_danger`';

        $this->_group = 'GROUP BY ' . $alias . '.id_product';

        $this->fields_list = array();
        $this->fields_list['id_product'] = array(
            'title' => ('ID'),
            'align' => 'text-center',
            'class' => 'fixed-width-xs',
            'type' => 'int'
        );
        $this->fields_list['image'] = array(
            'title' => ('Image'),
            'align' => 'center',
            'image' => 'p',
            'orderby' => false,
            'filter' => false,
            'search' => false
        );

        $this->fields_list['profile_name'] = array(
            'title' => ('Profile'),
            'align' => 'text-center',
            'filter_key' => 'profile_id',
            'type' => 'select',
            'list' => $this->profile_array,
            'filter_type' => 'int',
            'callback' => 'neweggProfileFilter'
        );

        $this->fields_list['name'] = array(
            'title' => ('Title'),
            'filter_key' => 'b!name',
            'align' => 'text-center',
        );

        $this->fields_list['reference'] = array(
            'title' => ('SKU'),
            'align' => 'text-center',
        );

        $this->fields_list['price_final'] = array(
            'title' => ('Final Price'),
            'type' => 'price',
            'align' => 'text-center',
            'havingFilter' => true,
            'orderby' => false,
            'search' => false
        );

        $this->fields_list['active'] = array(
            'title' => ('PrestaShop Status'),
            'active' => 'status',
            'filter_key' => $alias . '!active',
            'align' => 'text-center',
            'type' => 'bool',
            'class' => 'fixed-width-sm',
            'orderby' => false
        );

        $this->fields_list['error_message'] = array(
            'title' => $this->l('Validity'),
            'align' => 'text-center',
            'search' => false,
            'class' => 'fixed-width-sm',
            'callback' => 'validationData'
        );

        if ($profile_select = Tools::getValue('profile_select')) {
            $this->profile_select = $profile_select;
            $this->context->cookie->profile_select = $profile_select;
        } elseif ($this->context->cookie->profile_select) {
            $this->profile_select = $this->context->cookie->profile_select;
        }
        // Any action performed w/o selecting product
        if (Tools::getIsset('productSelectError') && Tools::getValue('productSelectError')) {
            $this->errors[] = "Please Select Product";
        }

        // Save Product
        if (Tools::getIsset('productSaveSuccess') && Tools::getValue('productSaveSuccess')) {
            $this->confirmations[] = "Product Data Saved Successfully";
        }

        if (Tools::getIsset('productSaveError') && Tools::getValue('productSaveError')) {
            $this->errors[] = "Some error while saving Product Data";
        }

        // Upload Product
        if (Tools::getIsset('productUploadSuccess') && Tools::getValue('productUploadSuccess')) {
            if (Tools::getIsset('msg') && Tools::getValue('msg')) {
                $this->confirmations[] = json_decode(Tools::getValue('msg'), true);
            } else {
                $this->confirmations[] = 'Product Uploaded Successfully!';
            }
        }

        if (Tools::getIsset('productUploadError') && Tools::getValue('productUploadError')) {
            if (Tools::getIsset('msg') && Tools::getValue('msg')) {
                $this->errors[] = json_decode(Tools::getValue('msg'), true);
            } else {
                $this->errors[] = 'Failed to upload Product';
            }
        }
        // Remove Product Category
        if (Tools::getIsset('productRemoveProfileSuccess') && Tools::getValue('productRemoveProfileSuccess')) {
            $this->confirmations[] = "Profile Removed Successfully";
        }

        // Assign Product Category
        if (Tools::getIsset('productAssignProfileSuccess') && Tools::getValue('productAssignProfileSuccess')) {
            $this->confirmations[] = "Profile Assinged Successfully";
        }

        // Category not selected for assign product category
        if (Tools::getIsset('productAssignProfileError') && Tools::getValue('productAssignProfileError')) {
            $this->errors[] = "No profile selected.";
        }
       
    }

    public function initContent()
    {
        $page = (int) Tools::getValue('page');
        //echo '<pre>'; print_r(Tools::getAllValues()); die('<br>aaaa');
        if (isset($this->profile_select)) {
            self::$currentIndex .= '&profile_select=' . $this->profile_select . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
        }
        parent::initContent();
    }

    public function neweggProfileFilter($data)
    { 
        // echo '<pre>'; print_r($data); die('<br>aaaa');
        if (isset($this->profile_array[$data])) {
            return $this->profile_array[$data];
        }
    }

    public function validationData($data, $rowData)
    {   
        $productName = isset($rowData['name']) ? $rowData['name'] : '';
        $this->context->smarty->assign(
            array(
                'validationData' => $data,
                'validationJson' => $data,
                'productName' => $productName
            )
        );
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/product/list/validation.tpl'
        );
    }

    public function renderList()
    {
        $this->addRowAction('view');
        $this->addRowAction('edit');
        $db = Db::getInstance();
        $profiles = Db::getInstance()->executeS("SELECT `id`,`profile_name`,`account_id` FROM `" . _DB_PREFIX_ . "newegg_profile` WHERE `profile_status`='1'");
        $sql = "SELECT pp.`id`,`profile_name`,`account_id`,`account_code` FROM `" . _DB_PREFIX_ . "newegg_profile` pp
                    JOIN `" . _DB_PREFIX_ . "newegg_accounts` p ON (p.`id` = pp.`account_id`)";
        $allProfiles = $db->executeS($sql);
        $reurl = $this->context->link->getAdminLink('AdminCedneweggProducts');
        
        $parent = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/product/product_list.tpl'
        );
        // die(Tools::getValue('profile_select'));
        $this->context->smarty->assign(array(
            'controllerUrl' => $reurl,
            'currentToken' => Tools::getAdminTokenLite('AdminCedNeweggProducts'),
            'allProfiles' => $allProfiles,
            'idCurrentProfile' => Tools::getValue('profile_select')
        ));
        $r = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/product/profile_selector.tpl');
        return $parent.$r.parent::renderList();
    }

    /**
     * renderForm contains all necessary initialization needed for all tabs
     *
     * @return string|void
     * @throws PrestaShopException
     */
    public function renderForm()
    {   
        // die(Tools::getValue('idCurrentProfile'));
        // $this->context->smarty->assign(array('profile_id' => Tools::getValue('profile_select')));
        // parent::renderForm();
    }

    public function processBulkAssignProfile() {

        $page = (int) Tools::getValue('page');
        if (!$page) {
            $page = (int) Tools::getValue('submitFilter' . $this->table);
        }
        $link = new LinkCore();

        $ids = $this->boxes;
        if(isset($this->profile_select)) {
            $neweggProfile = (int)$this->profile_select;
        }
        
        if (!empty($neweggProfile)) {
            if (!empty($ids)) {
                $CedNeweggProduct = new CedNeweggProduct();
                $result = $CedNeweggProduct->assignProfile($ids, $neweggProfile);
                if ($result == 1) {
                    $this->confirmations[] = "Profile Assigned Successfully";
                    $controller_link = $link->getAdminLink('AdminCedNeweggProducts') . '&productAssignProfileSuccess=1' . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
                    Tools::redirectAdmin($controller_link);
                }
            } else {
                $this->errors[] = "Please select Product(s)";
                $controller_link = $link->getAdminLink('AdminCedNeweggProducts') . '&productSelectError=1' . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
                Tools::redirectAdmin($controller_link);
            }
        } else {
            $this->errors[] = "No profile selected.";
            $controller_link = $link->getAdminLink('AdminCedNeweggProducts') . '&productAssignProfileError=1' . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
            Tools::redirectAdmin($controller_link);
        }
        $this->context->cookie->profile_select = '';
    }

    public function processBulkUploadProduct() {
        $page = (int) Tools::getValue('page');
        if (!$page) {
            $page = (int) Tools::getValue('submitFilter' . $this->table);
        }
        $link = new LinkCore();

        $ids = $this->boxes;
        if(isset($this->profile_select)) {
            $profile_id = (int)$this->profile_select;
        }

        if (!empty($profile_id)) {
            if (!empty($ids)) { 
                $CedNeweggProduct = new CedNeweggProduct();
                $message = $CedNeweggProduct->prepareData($ids,$profile_id);
                $messages['success'] = " Product successfully uploaded";
            } else {
                $this->errors[] = "Please select Product(s)";
                $controller_link = $link->getAdminLink('AdminCedNeweggProducts') . '&productSelectError=1' . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
                Tools::redirectAdmin($controller_link);
            }
        } else {
            $this->errors[] = "please select profile !!.";
            $controller_link = $link->getAdminLink('AdminCedNeweggProducts') . '&productAssignProfileError=1' . ($page > 1 ? '&submitFilter' . $this->table . '=' . (int)$page : '');
            Tools::redirectAdmin($controller_link);
        }
        $this->context->cookie->profile_select = '';
        
    }

    public function setMedia($isNewTheme = false)
    {   
        parent::setMedia($isNewTheme);
        $this->addJquery();
        $this->addJS(_PS_MODULE_DIR_.'cednewegg/views/js/admin/product/product.js');
    }
}