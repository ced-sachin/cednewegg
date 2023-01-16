<?php



class AdminCedNeweggProfileController extends ModuleAdminController
{
    
    public function __construct()
    {

        $db = Db::getInstance();
            
        $this->id_lang = Context::getContext()->language->id;
        $this->bootstrap = true;
        $this->table = 'newegg_profile';
        $this->identifier = 'id';
        $this->list_no_link = true;
        $this->addRowAction('edit');
        $this->addRowAction('duplicateProfile');
        $this->addRowAction('deleteProfile');
        
        $this->fields_list = array(
            'id' => array(
                'title' => 'ID',
                'type' => 'text',
                'align' => 'center',
                'class' => 'fixed-width-xs',
            ),
            'profile_name' => array(
                'title' => 'Profile Name',
                'type' => 'text',
                'align' => 'text-center',
            ),
            'profile_status' => array(
                'title' => 'Profile Status',
                'type' => 'bool',
                'align' => 'text-center',
                'class' => 'fixed-width-sm',
                'orderby' => false
            ),
            'product_count' => array(
                'title' => 'Product Count',
                'type' => 'text',
                'align' => 'text-center',
            ),
            );  

            $this->bulk_actions = array(
                'delete_profile' => array(
                    'text' => ('Delete'),
                    'icon' => 'icon-trash',
                )
            );
                if (Tools::isSubmit('submitNeweggProfileSave')) {
                    $this->saveProfile();
                }
            parent::__construct();
    }

    public function postProcess(){
     
        $req = Tools::getAllValues();

        if (Tools::getIsset('submitBulkdelete_accountnewegg_profile') && Tools::getValue('submitBulkdelete_accountnewegg_profile')) {
            $id = Tools::getValue('newegg_profileBox');
        }
        
        parent::postProcess();

    }
     
    public function saveProfile(){
        $db = Db::getInstance();
        $profileData = Tools::getAllValues();
        // echo '<pre>'; print_r($profileData); die;
        $profileId = Tools::getValue('id');
        $error_fields = 0;
            if (!isset($profileData['profileTitle'])) {
                $profileData['profileTitle'] = '';
                $error_fields += 1;
            }

            if (!isset($profileData['accountSelect'])) {
                $profileData['accountSelect'] = '';
                $error_fields += 1;
            }

            if (!isset($profileData['profileCategory'])) {
                $profileData['profileCategory'] = '';
                $error_fields += 1;
            }

            if (!isset($profileData['newegg_attributes'])) {
                $profileData['newegg_attributes'] = '';
                $error_fields += 1;
            }

            if (!isset($profileData['newegg_opt_attributes'])) {
                $profileData['newegg_opt_attributes'] = '';
            }

            if (!isset($profileData['profileStatus'])) {
                $profileData['profileStatus'] = '';
                $error_fields += 1;
            }
            
            if($error_fields>0){
                $this->errors[] = "Please fill required filled !!";
            }else{

            if (!empty($profileId)) {
                $profile_req_opt_attribute = array();
                $profile_req_opt_attribute[0] = $profileData['newegg_attributes'];
                $profile_req_opt_attribute[1] = $profileData['newegg_opt_attributes'];
                $res = $db->update(
                    'newegg_profile',
                    array(
                        'profile_code' => isset($profileData['profileCode']) ? pSQL($profileData['profileCode']):pSQL(''),
                        'profile_status' => pSQL($profileData['profileStatus']),
                        'profile_name' => pSQL($profileData['profileTitle']),
                        'profile_category' => pSQL(($profileData['profileCategory'])),
                        'profile_req_opt_attribute' => pSQL(json_encode($profile_req_opt_attribute)),
                        'account_id' => pSQL($profileData['accountSelect']),
                    ),
                    'id=' . (int)$profileId
                );

                if ($res) {
                    $this->confirmations[] = "Profile updated successfully.";
                }
            } else {
                $a_code = $db->getValue(
                    "SELECT `id` from `" . _DB_PREFIX_ . "newegg_profile` 
                              WHERE `profile_name`='" . pSQL($profileData['profileTitle']) . "'"
                );
                if (!$a_code) {
                    $profile_req_opt_attribute[0] = $profileData['newegg_attributes'];
                    $profile_req_opt_attribute[1] = $profileData['newegg_opt_attributes'];  
                    $res = $db->insert(
                        'newegg_profile',
                        array(
                            'profile_code' => isset($profileData['profileCode']) ? pSQL($profileData['profileCode']):pSQL(''),
                            'profile_status' => pSQL($profileData['profileStatus']),
                            'profile_name' => pSQL($profileData['profileTitle']),
                            'profile_category' => pSQL(($profileData['profileCategory'])),
                            'profile_req_opt_attribute' => pSQL(json_encode($profile_req_opt_attribute)),
                            'account_id' => pSQL($profileData['accountSelect']),
                            // 'store_id' => pSQL($profileData['store_id'])
                        )
                    );

                    if ($res) {
                        $this->confirmations[] = "Profile created successfully";
                    }
                    if ($res) {
                        $link = new LinkCore();
                        $controller_link = $link->getAdminLink('AdminCedNeweggProfile') . '&created=1';
                        Tools::redirectAdmin($controller_link);
                    }
                } else {
                    $this->errors[] = "The profile code must be unique. " . $profileData['profileTitle'] .
                        " is already assigned to profile Id " . $a_code;
                }
            }
        }
    }
    public function initPageHeaderToolbar()
    {
            if (empty($this->display)) {
                $this->page_header_toolbar_btn['new_profile'] = array(
                'href' => self::$currentIndex . '&addnewegg_profile&token=' . $this->token,
                'desc' => $this->l('Create Profile', null, null, false),
                'icon' => 'process-icon-new'
            );
            $this->page_header_toolbar_btn['refresh_profile'] = array(
                'href' => $this->context->link->getAdminLink('AdminCedNeweggUploadall') .
                    '&refresh_profile=redirected',
                'desc' => $this->l('Assign All Product(s) To A Single Profile', null, null, false),
                'icon' => 'process-icon-refresh'
            );
            } elseif ($this->display == 'edit' || $this->display == 'add') {
                $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => self::$currentIndex . '&token=' . $this->token,
                'desc' => $this->l('Back To List', null, null, false),
                'icon' => 'process-icon-back'
            );
            }
        parent::initPageHeaderToolbar();
    }

    /**
     * @return string
     * @throws PrestaShopDatabaseException
     * @throws PrestaShopException
     * @throws SmartyException
     */
    public function renderForm()
    {   $db = Db::getInstance();
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_accounts`";
        $result = $db->executeS($sql);
        if (isset($result[0]) && $result[0] && is_array($result[0])) {
            $datas = $result;
        }
        $accountData = array();
        $index = 0;
        foreach($datas as $data){
            $accountData[$index] = $data;
            $index += 1;
        }
        $storeDefaultAttributes = $this->getSystemAttributes();
        $storeAttributes = $this->getAttributes();
        $requiredsAttributes = $this->getRequiredAttributes();

        $this->context->smarty->assign(array(
            'accounts' => $accountData,
        'currentToken' => Tools::getAdminTokenLite('AdminCedNeweggProfile'),
        'storeDefaultAttributes' => $storeDefaultAttributes,
        'storeAttributes' => $storeAttributes,
        'requiredAttributes' => $requiredsAttributes
        ));
        
        $profileId = Tools::getValue('id');
        if(!empty($profileId)){
        $profileData = array(
                'profile_code' => '',
                'profile_status' => '',
                'profile_name' => '',
                'profile_category' => '',
                'profile_req_opt_attribute' => '',
                'account_id' => '',
                'profileCat'=> ''
        );
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_profile` WHERE `id`=" .$profileId;
        $result = $db->executeS($sql);
        if (isset($result[0]) && $result[0] && is_array($result[0])) {
            $data = $result[0];
        }
        $account_data = $this->getAccountDetails($data['account_id']);
        $root_cat = str_replace(array('[',']','"'),'',$account_data['root_cat']);
        // echo '<pre>'; print_r($account_data); die;
        $sql1 = "SELECT * from `" . _DB_PREFIX_ . "newegg_categories` 
        WHERE `root_cat`='" . pSQL($root_cat) . "'";
        $profileCat = $db->executeS($sql1);
        $subCatId = explode(':',$data['profile_category'])[0]; 
        $sql2 = "SELECT `all_attr` from `" . _DB_PREFIX_ . "newegg_attributes` 
        WHERE `sub_cat_id`='" . pSQL($subCatId) . "'";
        $optAttr = $db->executeS($sql2);
        $optAttr = $optAttr[0]['all_attr'];
        $optAttr = explode(',',$optAttr);
        // echo '<pre>'; print_r($optAttr); die;
        $profileData['profile_code'] = isset($data['account_code']) ? $data['account_code'] : '';
        $profileData['profile_status'] = isset($data['profile_status']) ? $data['profile_status'] : '';
        $profileData['profile_name'] = isset($data['profile_name']) ? $data['profile_name'] : '';
        $profileData['profile_category'] = isset($data['profile_category']) ? $data['profile_category'] : '';
        $profileData['profile_req_opt_attribute'] = isset($data['profile_req_opt_attribute']) ? $data['profile_req_opt_attribute'] : '';
        $profileData['account_id'] = isset($data['account_id']) ? $data['account_id'] : '';
        $profileData['profileCat'] = isset($profileCat) ? $profileCat : '';
        
        
        $this->context->smarty->assign(array(
            'token' => Tools::getAdminTokenLite('AdminCedNeweggProfile'),
            'currentToken' => Tools::getAdminTokenLite('AdminCedNeweggProfile'),
            'controllerUrl' => $this->context->link->getAdminLink('AdminCedNeweggProfile'),
            'profile_code' =>$profileData['profile_code'],
            'profile_status' =>$profileData['profile_status'],
            'profile_name' =>$profileData['profile_name'],
            'profile_category' =>$profileData['profile_category'],
            'profile_req_opt_attribute' =>json_decode($profileData['profile_req_opt_attribute'], true),
            'account_id' =>$profileData['account_id'],
            'profileCat' => $profileCat,
            'storeDefaultAttributes' => $storeDefaultAttributes,
            'storeAttributes' => $storeAttributes,
            'requiredAttributes' => $requiredsAttributes,
            'optionalAttrs' => $optAttr
      ));
    }
        parent::renderForm();
        
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/profile/edit_profile.tpl'
        );
    }

    public function getAttributes()
    {
        $db = Db::getInstance();
        $default_lang = (int) Configuration::get('PS_LANG_DEFAULT');
        $sql = "SELECT `id_attribute_group` as `id_attribute`,`name` 
               FROM `" . _DB_PREFIX_ . "attribute_group_lang` 
               where `id_lang`='" . (int)$default_lang . "'";
        $result = $db->ExecuteS($sql);
        if (is_array($result) && count($result)) {
            return $result;
        } else {
            return array();
        }
    }

    public function getSystemAttributes()
    {
        return array(
            'reference' => 'Reference',
            'name'      => 'Name',
            'description' => 'Description',
            'description_short' => 'Short Description',
            'id_manufacturer' => 'Manufacturer',
            'id_tax_rules_group' => 'Tax Rule',
            'price' => 'Price (Tax Excl.)',
            'base_price' => 'Price (Tax Incl.)',
            'disc_price' => 'Discounted Price (Tax Excl.)',
            'disc_base_price' => 'Discounted Price (Tax Incl.)',
            'upc' => 'UPC',
            'ean13' => 'EAN',
            'isbn' => 'ISBN',
            'quantity' => 'Quantity',
            'weight' => 'Weight',
            'available_date' => 'Restock Date'
        );
    }
    public function getRequiredAttributes() {
        return array(
            'SellerPartNumber',
            'Manufacturer',
            'ManufacturerPartNumberOrISBN',
            'UPC',
            'WebsiteShortTitle',
            'ProductDescription',
            'ItemWeight',
            'PacksOrSets',
            'ItemCondition',
            'ShippingRestriction',
            'Shipping',
        );
    }
    public function setMedia($isNewTheme = false)
    {   
        parent::setMedia($isNewTheme);
        $this->addJquery();
        $this->addJS(_PS_MODULE_DIR_.'cednewegg/views/js/admin/profile/profile.js');
    }
    
    public function ajaxProcessFetchNeweggCategoryDetails() {

        $db = Db::getInstance();
        $accountID = Tools::getValue('newegg_account_id');

        // print_r($accountID);die();
        $accountDetail = $this->getAccountDetails($accountID);
        $rootCategory = $accountDetail['root_cat'];
        $rootCategory = str_replace(array('[',']','"'),'',$rootCategory);
        $root_cat_exist = $db->getValue(
            "SELECT `id` from `" . _DB_PREFIX_ . "newegg_categories` 
                    WHERE `root_cat`='" . pSQL($rootCategory) . "'"
        );
        
        //if root category not exist in db
        if (empty($root_cat_exist)) {
            $categories = $this->getSubCategories($rootCategory, $accountDetail);
            if(isset($categories)) {
            $responses = $categories['ResponseBody']['SubcategoryList'];
            foreach($responses as $response) {
                // print_r($response); die();
                $sub_cat_exist = $db->getValue(
                    "SELECT `sub_cat_Id` from `" . _DB_PREFIX_ . "newegg_categories` 
                            WHERE `sub_cat_Id`='" . pSQL($response['SubcategoryID']) . "'"
                );
                if (!$sub_cat_exist) {
                    $res = $db->insert(
                        'newegg_categories',
                        array(
                        'root_cat' => pSQL($response['IndustryCode']),
                        'root_cat_name' => pSQL($response['IndustryName']),
                        'sub_cat_Id' => pSQL($response['SubcategoryID']),
                        'sub_cat_name' => pSQL($response['SubcategoryName']))
                        );
                }
            }
          }
        }

        if (isset($rootCategory)) {
           
        $sql = "SELECT * from `" . _DB_PREFIX_ . "newegg_categories` 
        WHERE `root_cat`='" . pSQL($rootCategory) . "'";
        $results = $db->executeS($sql);
        

        
        $this->context->smarty->assign(
            array(
                'neweggCategoryList'=> $results
            )
        );
        die(json_encode(array(
            'success' => true,
            'newegg_categories' => $results,
        )));
        
        }
    }
        
    public function ajaxProcessGetRequiredAttributes(){
        $db = Db::getInstance();
        $accountID = Tools::getValue('newegg_account_id');
        $subcatId = Tools::getValue('newegg_sub_cat');
        if (isset($subcatId) && !empty($subcatId)) {
            $subcat = explode(':', $subcatId);
            $name = $subcat[1];
            $subcatId = $subcat[0];
        }

        $accountDetail = $this->getAccountDetails($accountID);
        $sub_cat_id = $db->getValue(
            "SELECT `id` from `" . _DB_PREFIX_ . "newegg_attributes` 
                    WHERE `sub_cat_id`='" . pSQL($subcatId) . "'"
        );
        if (empty($sub_cat_id)) {
            $response = $this->getPropertyList($subcatId, $accountDetail);
          if(!empty($response)){
            $res = $db->insert(
                'newegg_attributes',
                array(
                'sub_cat_id' => pSQL($subcatId),//isset($response['subcatPropertyValueResponse']['SubcategoryID']) ? pSQL($response['subcatPropertyValueResponse']['SubcategoryID']):pSQL(''),
                'required_attr' => isset($response['required']) ? pSQL($response['required']):pSQL(''),
                'all_attr' => isset($response['all']) ? pSQL($response['all']):pSQL(''),
                'name' => isset($response['name']) ? pSQL($response['name']):pSQL('')
                )
            );
            }
          }
        $sql = "SELECT * from `" . _DB_PREFIX_ . "newegg_attributes` 
        WHERE `sub_cat_id`='" . pSQL($subcatId) . "'";
        $result = $db->executeS($sql);
        // print_r($results[0]); die;
        $results = $result[0];
        $requiredsattributes = isset($results['required_attr']) ? explode(',', $results['required_attr']):'';
        $optionalattributes = explode(',', $results['all_attr']);
        $storeDefaultAttributes = $this->getSystemAttributes();
        $storeAttributes = $this->getAttributes();
        $requiredsDefaultAttr = $this->getRequiredAttributes();
        $this->context->smarty->assign(
            array(
                'requiredAttrs'=> $requiredsattributes,
                'optionalAttrs'=> $optionalattributes,
                'storeDefaultAttributes' => $storeDefaultAttributes,
                'storeAttributes' => $storeAttributes,
                'requiredAttributes' => $requiredsDefaultAttr
            )
        );
        $content = $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/profile/profile_required_attribute.tpl'
        );
        die(json_encode(array(
            'success' => true,
            'content' => $content
        )));
    }

    public function getSubCategories($industryCode, $currentAccount)
    {
        print_r($industryCode);
        $body = '<NeweggAPIRequest>
                    <OperationType>GetSellerSubcategoryRequest</OperationType>
                    <RequestBody>
                        <GetItemSubcategory>
                            <IndustryCodeList> 
                                <IndustryCode>' . $industryCode . '</IndustryCode>       
                            </IndustryCodeList>
                            <Enabled>1</Enabled>
                        </GetItemSubcategory>
                    </RequestBody>
                </NeweggAPIRequest>';
        $subIndustries = $this->getRequest('sellermgmt/seller/subcategory', $currentAccount, ['body' => $body]);
        return $subIndustries;
    }

    /**
     * @param $subcatId
     * @param $account
     * @return array
     */
    public function getPropertyList($subcatId, $account)
    {
        $body = '<NeweggAPIRequest> 
                    <OperationType>GetSellerSubcategoryPropertyRequest</OperationType>   
                    <RequestBody>     
                        <SubcategoryID>' . $subcatId . '</SubcategoryID>   
                    </RequestBody> 
                </NeweggAPIRequest>';
        $subcatFieldsArray = $this->getRequest('/sellermgmt/seller/subcategoryproperty', $account, ['body' => $body]);
        $propertyList = array();
        if (!isset($subcatFieldsArray['ResponseBody']['SubcategoryPropertyList'])) {
            return $propertyList;
        }
        $subcatFieldsResponse = $subcatFieldsArray['ResponseBody']['SubcategoryPropertyList'];
        $propertyList['all'] = $propertyList['required'] = "";
        foreach ($subcatFieldsResponse as $subcatFields) {
            $propertyList['name'] = $subcatFields['SubcategoryName'];
            $subcatFieldName = isset($subcatFields['PropertyName']) ? ($subcatFields['PropertyName']) : '';
            $propertyList['all'] = $propertyList['all'] == "" ? $subcatFieldName : $propertyList['all'] . "," . $subcatFieldName;
            if (isset($subcatFields['IsRequired']) && $subcatFields['IsRequired'] == '1') {
                $propertyList['required'] = $propertyList['required'] == "" ? $subcatFieldName : $propertyList['required'] . "," . $subcatFieldName;
            }
            $subCatPropertyRes = $this->getPropertyDetail($subcatId, $subcatFieldName, $account);
            $propertyList['subcatPropertyValueResponse'] = $subCatPropertyRes;
        }

        return $propertyList;
    }

    /**
     * @param $subcatId
     * @param $propertyName
     * @param $account
     * @return bool|mixed
     */
    public function getPropertyDetail($subcatId, $propertyName, $account)
    {
        try {
            $body = '<NeweggAPIRequest> 
                    <OperationType>GetSellerPropertyValueRequest</OperationType>   
                    <RequestBody>     
                        <SubcategoryID>' . $subcatId . '</SubcategoryID>
                        <PropertyName>' . $propertyName . '</PropertyName>
                    </RequestBody> 
                </NeweggAPIRequest>';

            $response = $this->getRequest('/sellermgmt/seller/propertyvalue', $account, ['body' => $body]);

            if (!$response)
                return false;

            $response = $response['ResponseBody']['PropertyInfoList'][0];
            return $response;

        } catch (\Exception $e) {
        }

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

    public function getAccountDetails($id) {
        $db = Db::getInstance();
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_accounts` where id=".$id;
        $result = $db->executeS($sql);
        return $result[0];
    }

    public function processBulkDeleteProfile() {
        if(!empty($this->boxes)) {
             $profile_ids = $this->boxes;
           $result = $this->deleteProfile($profile_ids);
             if($result == 1){
                 $this->confirmations[] = "Profile Deleted successfully.";
             }else{
                 $this->error[] = "Profile not deleted";
             }
        }
     }

    public function deleteProfile($ids)
    { 
        $db = Db::getInstance();
        if (is_array($ids) && !empty($ids)) {
            foreach($ids as $id){
               
                    $res = $db->delete(
                        'newegg_profile',
                        'id=' . (int)$id
                    );
            }
            if ($res) {
                return true;
            }
        }

        return false;
    }
}


/*
