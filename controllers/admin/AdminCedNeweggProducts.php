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
            'sync_status' => array(
                'text' => ('Sync Newegg Status'),
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
            'update_inv_price' => array(
                'text' => ('Update Price Inventory'),
                'icon' => 'icon-refresh',
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
        $this->_select .= 'cbprofile.`newegg_product_status` AS `newegg_product_status`, ';
        $this->_select .= 'cbprof.`profile_name` AS `profile_name`, ';
        $this->_select .= 'cbprofile.`newegg_validation_error` AS `error_message`, ';
        $this->_select .= 'cbprofile.`newegg_feed_error` AS `newegg_feed_error`, ';
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

        $this->fields_list['newegg_product_status'] = array(
            'title' => ('Newegg Status'),
            'align' => 'text-center',
            // 'callback' => 'validationData'
        );

        $this->fields_list['newegg_feed_error'] = array(
            'title' => $this->l('Uploading error'),
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

    public function processBulkUpdateInvPrice() {
        $page = (int) Tools::getValue('page');
        if (!$page) {
            $page = (int) Tools::getValue('submitFilter' . $this->table);
        }
        $link = new LinkCore();

        $ids = $this->boxes;
        if(isset($this->profile_select)) {
            $neweggProfile = (int)$this->profile_select;
        }
        $db = Db::getInstance();
        try {
            $CedNeweggProduct = new CedNeweggProduct();
            $accountId = $db->executeS("SELECT `account_id` FROM `" . _DB_PREFIX_ . "newegg_profile` WHERE `id` = ".$neweggProfile)[0]['account_id'];
            $accountDetail = $this->getAccountDetails($accountId);
            $location = $accountDetail['warehouse_location'];
            

            if ($location == 'CAN') { //for canada account
                $response = $CedNeweggProduct->updateInvPriceCanada($ids , $accountId);
                $this->confirmations[] = $response;
                return true;
            }
            $CedNeweggProduct->updatePriceOnNewegg($ids, $accountId);
            $key = 0;
            $temp = 0;
            $item = [];
            foreach ($ids as $key => $id) {
                   $product = new Product($id);
                   $qty = StockAvailable::getQuantityAvailableByProduct($id);
                   if ($qty < 0) {
                       $qty = 0;
                   }

                $qty = round($qty, 0);
                array_push($item, array(
                    "SellerPartNumber" => $product->reference,
                    "WarehouseLocation" => "$location",
                    "Inventory" => "$qty"
                ));
            }
            $invArray = [
                'NeweggEnvelope' => ['-xmlns:xsi' => 'http://www.w3.org/2001/XMLSchema-instance',
                    '-xsi:noNamespaceSchemaLocation' => 'BatchInventoryUpdate.xsd',
                    'Header' => ['DocumentVersion' => "2.0"],
                    'MessageType' => 'Inventory',
                    'Message' => [
                        'Inventory' => ['Item' => $item]
                    ]
                ]
            ];
            $params['body'] = $body = json_encode($invArray);
            $params['invurl'] = '&requesttype=INVENTORY_DATA';
            $action = 'datafeedmgmt/feeds/submitfeed';
            // print_r($invArray); die(__FILE__);
            $serverOutput = $CedNeweggProduct->postRequest($action, $accountDetail, $params);
            // die($serverOutput);
            $this->confirmations[] = "Inventory& Price Updated Successfully";
        return true;
        }catch(\Exception $e) {
            die("Error while updating inventory& Price at line no ".$e->getLine(). "in file ".$e->getFile());
        }
    }

    public function processBulkSyncStatus() {
        $page = (int) Tools::getValue('page');
        if (!$page) {
            $page = (int) Tools::getValue('submitFilter' . $this->table);
        }
        $link = new LinkCore();

        $ids = $this->boxes;
        if(isset($this->profile_select)) {
            $neweggProfile = (int)$this->profile_select;
        }
        $db = Db::getInstance();
        
        foreach($ids as $id) {
            $request_data = $db->executeS("SELECT `newegg_queue_id`,`account_id` FROM `" . _DB_PREFIX_ . "newegg_profile_product` WHERE `profile_id` = ".$neweggProfile." and `product_id` = ".$id)[0];
            $accountDetail = $this->getAccountDetails($request_data['account_id']);
            $product = new Product($id);
            $action = 'datafeedmgmt/feeds/result/' . $request_data['newegg_queue_id'];
            $response = $this->getRequest($action, $accountDetail);
            
            if(!isset($response['NeweggEnvelope']['Message']['ProcessingReport'])){
                $this->errors[] = 'Product '.$id.' under process';
                continue;    
            }

            if(isset($response['NeweggEnvelope']['Message']['ProcessingReport']['Result'])) {
                if (!isset($response['NeweggEnvelope']['Message']['ProcessingReport']['Result'][0])) {
                    $newresponse[0] = $response['NeweggEnvelope']['Message']['ProcessingReport']['Result'];
                } else {
                    $newresponse = $response['NeweggEnvelope']['Message']['ProcessingReport']['Result'];
                }   
            } else {
                $db->execute("UPDATE " . _DB_PREFIX_ . "newegg_profile_product SET `newegg_feed_error` = '', `newegg_product_status` = 'Uploaded' where `profile_id`=".$neweggProfile." and  `product_id`= ".$id);   
                continue;
            }
            foreach($newresponse as $val) {
                if (isset($val['AdditionalInfo']['SellerPartNumber']) || isset($val['SellerPartNumber'])) {                
                    if($product->reference == $val['AdditionalInfo']['SellerPartNumber']) {
                        // die($product->reference);
                        $key_c = 0; 
                        $erro = '';
                        foreach($val['ErrorList']['ErrorDescription'] as $err){
                            if($key_c > 0){
                                $erro .= $err."<br/>";
                            }
                            $key_c += 1;
                        }   

                        $db->execute("UPDATE " . _DB_PREFIX_ . "newegg_profile_product SET `newegg_feed_error` = '".$erro."', `newegg_product_status` = 'upload error' where `profile_id`=".$neweggProfile." and  `product_id`= ".$id);
                        $this->errors[] = "Product upload request with invalid data";
                    } else {
                        $db->execute("UPDATE " . _DB_PREFIX_ . "newegg_profile_product SET `newegg_feed_error` = '', `newegg_product_status` = 'Uploaded' where `profile_id`=".$neweggProfile." and  `product_id`= ".$id);
                    }                    
                }
            }
        }
    }

    public function getAccountDetails($id) {
        $db = Db::getInstance();
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_accounts` where id=".$id;
        $result = $db->executeS($sql);
        return $result[0];
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
                if(empty($message)) {
                    $this->errors[] = "Product not uploaded!!";
                } else {
                    $this->confirmations[] = "Product move in queue, Sync status to check product is uploaded or not!!";
                }
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

    public function getRequest($url, $currentAccount, $params = [])
    {  
        if (!isset($params['append'])) {
            $params['append'] = '';
        }
        $country = $currentAccount['account_location'];
        switch ($country) {
                case 0:
                    $mainUrl = "https://api.newegg.com/marketplace/";
                    break;
                case 1:
                    $mainUrl = "https://api.newegg.com/marketplace/can/";
                    break;
                default:
                    $mainUrl = "https://api.newegg.com/marketplace/b2b/";
                    break;

            }
        $currentAccountDetail = $currentAccount;
        if (is_array($currentAccountDetail) && !empty($currentAccountDetail)) {

            $url = $mainUrl . $url . "?sellerid=" . $currentAccountDetail['seller_id'] . $params['append'];
            // print_r($url); die(__FILE__);
            $headers = array();
            if (isset($params['json'])) {
                $headers[] = "Content-Type: application/json";
            }
            $headers[] = "Accept: application/json";
            $headers[] = "Authorization: " . $currentAccountDetail['authorization_key'];
            $headers[] = "SecretKey: " . $currentAccountDetail['secret_key'];
            if (isset($params['body'])) {
                $putString = stripslashes($params['body']);
                $putData = tmpfile();
                fwrite($putData, $putString);
                fseek($putData, 0);
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
            if (isset($params['body'])) {
                curl_setopt($ch, CURLOPT_PUT, true);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $params['body']);
                curl_setopt($ch, CURLOPT_INFILE, $putData);
                curl_setopt($ch, CURLOPT_INFILESIZE, strlen($putString));
            }
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            $serverOutput = curl_exec($ch);
            curl_close($ch);
            return json_decode($this->formatJson($serverOutput), true);
        }
    }

    function formatJson($json_data)
    {
        for ($i = 0; $i <= 31; ++$i) {
            $json_data = str_replace(chr($i), "", $json_data);
        }
        $json_data = str_replace(chr(127), "", $json_data);
        if (0 === strpos(bin2hex($json_data), 'efbbbf')) {
            $json_data = substr($json_data, 3);
        }
        return $json_data;
    }
}