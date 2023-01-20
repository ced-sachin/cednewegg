<?php

/**
 * CedCommerce
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to the End User License Agreement(EULA)
 * that is bundled with this package in the file LICENSE.txt.
 * It is also available through the world-wide-web at this URL:
 * http://cedcommerce.com/license-agreement.txt
 *
 * @author    CedCommerce Core Team <connect@cedcommerce.com>
 * @copyright Copyright CEDCOMMERCE(http://cedcommerce.com/)
 * @license   http://cedcommerce.com/license-agreement.txt
 * @category  Ced
 * @package   CedNewegg
 */

// require_once _PS_MODULE_DIR_ . 'cednewegg/classes/CedneweggHelper.php';
// require_once _PS_MODULE_DIR_ . 'cednewegg/classes/CedneweggProfile.php';

class CedNeweggProduct
{
   
    public function getProductsProfile($product_id = 0)
    {
        $db = Db::getInstance();
        $sql = 'SELECT `id_fruugo_profile` FROM `' . _DB_PREFIX_ . 'fruugo_profile_products`
            WHERE `id_product` =' . $product_id;
        $response = $db->ExecuteS($sql);
        if (is_array($response) && count($response) && isset($response[0]['id_fruugo_profile'])) {
            $profile_id = $response[0]['id_fruugo_profile'];
            $CedfruugoProfile = new CedfruugoProfile();
            $profileData = $CedfruugoProfile->getProfileDataById((int)$profile_id);
            return $profileData;
        } else {
            return false;
        }
    }

    public function getCategoryNameById($id)
    {
        $db = Db::getInstance();
        $name = $db->getValue(
            "SELECT `name` FROM `" . _DB_PREFIX_ . "fruugo_category_list` 
                where `id`='" . (int)$id . "'"
        );
        return $name;
    }

    public function getAllMappedCategories()
    {
        $db = Db::getInstance();
        $row = $db->ExecuteS("SELECT `mapped_categories` 
        FROM `" . _DB_PREFIX_ . "fruugo_category_list` 
        WHERE `mapped_categories` != '' ORDER BY `mapped_categories` DESC");

        if (isset($row['0']) && $row['0']) {
            $mapped_categories = array();
            foreach ($row as $value) {
                $mapped_categories = array_merge($mapped_categories, unserialize($value['mapped_categories']));
            }
            $mapped_categories = array_unique($mapped_categories);
            $mapped_categories = array_values($mapped_categories);
            return $mapped_categories;
        } else {
            return array();
        }
    }

    public function makeInclude($product_ids)
    {
        if (isset($product_ids) && !empty($product_ids)) {
            $product_idss = array_chunk($product_ids, 300);
            foreach ($product_idss as $product_ids) {
                $sql = "INSERT INTO " . _DB_PREFIX_ . "fruugo_products (id, product_id, fruugo_status) VALUES ";
                foreach ($product_ids as $product_id) {
                    $sql .= "((SELECT `id` FROM `" . _DB_PREFIX_ . "fruugo_products` pscp WHERE pscp.product_id='" . (int)$product_id . "' LIMIT 1), '" . (int)$product_id . "', ''), ";
                }
                $sql = rtrim($sql, ', ');
                $sql .= " ON DUPLICATE KEY UPDATE product_id=values(product_id), fruugo_status=values(fruugo_status)";
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function makeExclude($product_ids)
    {
        if (isset($product_ids) && !empty($product_ids)) {
            $product_idss = array_chunk($product_ids, 300);
            foreach ($product_idss as $product_ids) {
                $sql = "INSERT INTO " . _DB_PREFIX_ . "fruugo_products (id, product_id, fruugo_status) VALUES ";
                foreach ($product_ids as $product_id) {
                    $sql .= "((SELECT `id` FROM `" . _DB_PREFIX_ . "fruugo_products` pscp WHERE pscp.product_id='" . (int)$product_id . "' LIMIT 1), '" . (int)$product_id . "', 'Excluded'), ";
                }
                $sql = rtrim($sql, ', ');
                $sql .= " ON DUPLICATE KEY UPDATE product_id=values(product_id), fruugo_status=values(fruugo_status)";
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function removeProfile($product_ids, $profile_id)
    {
        if (isset($product_ids) && !empty($product_ids)) {
            $product_idss = array_chunk($product_ids, 300);
            foreach ($product_idss as $product_ids) {
                $sql = "DELETE FROM " . _DB_PREFIX_ . "fruugo_profile_products WHERE id_product IN (" . implode(',', $product_ids) . ")";
                Db::getInstance()->execute($sql);
            }
        }
    }

    public function assignProfile($product_ids, $profile_id)
    {   $account_id = Db::getInstance()->executeS("SELECT `account_id` FROM " . _DB_PREFIX_ . "newegg_profile where id=".$profile_id);
        $account_id = $account_id[0]['account_id'];
        $exec = 0;
        if (isset($product_ids) && !empty($product_ids)) {
            $product_idss = array_chunk($product_ids, 300);
            foreach ($product_idss as $product_ids) {
                $sql = "INSERT INTO " . _DB_PREFIX_ . "newegg_profile_product (id, product_id, profile_id, account_id) VALUES ";
                foreach ($product_ids as $product_id) {

                    $sql .= "((SELECT `id` FROM " . _DB_PREFIX_ . "newegg_profile_product pscp WHERE pscp.product_id='" . (int)$product_id . "' and pscp.account_id='" . (int)$account_id . "' LIMIT 1), '" . (int)$product_id . "', '" . (int)$profile_id . "', '" . (int)$account_id . "'), ";
                }
                $sql = rtrim($sql, ', ');
                $sql .= " ON DUPLICATE KEY UPDATE product_id=values(product_id), profile_id=values(profile_id), account_id=values(account_id)";
                $exec = Db::getInstance()->execute($sql);
                // die($exec);
            }
        }
        return $exec;
    }

    public function prepareData($ids, $profile_id) {
        $db = Db::getInstance();
        $response = false;
        $account_id = $db->executeS("SELECT `account_id` FROM " . _DB_PREFIX_ . "newegg_profile where id=".$profile_id);
        $account_id = $account_id[0]['account_id'];
        $validatedProducts = '';
        foreach ($ids as $id) {

            $profileData = $this->profileData($profile_id);
            $profileId =$profileData['id'];
            $product = new Product($id);
            $profile = $profileData;
            $sql = "SELECT * from " . _DB_PREFIX_ . "newegg_profile_product WHERE product_id=".$id. " and profile_id =".$profile_id;
            $productInProfile = $db->executeS($sql);

            if(!empty($productInProfile)){
                $productId = $this->validateProduct($id, $product, $profile);
            }else{
                $validatedProducts .= 'Product '.$id.' not in profile!!';
                continue;
            }
        }
    }

    /**
     * validate products
     * @param $id
     * @param $product
     * @param $profile
     * @param $profileProductsId
     * @param null $parentId
     * @return bool
     * @throws \Exception
     */
    public function validateProduct($id, $product, $profile, $parentId = null)
    {   $db = Db::getInstance();
        try {
            $id_lang = Context::getContext()->language->id;
            $validatedProduct = false;
            if ($product == null) {
                $product = new Product($id, false, $id_lang);
            }
            
            $profileId = $profile['id'];
            $sku = $product->reference;
            $productArray = (array)$product;
            $errors = [];
            $result['error'] = '';
            if (isset($profileId) and $profileId != false) {
                $category = $profile['profile_category'];
                $requiredAttributes = json_decode($profile['profile_req_opt_attribute'], 1);
                foreach ($requiredAttributes[0] as $key => $neweggAttribute) {
                    switch ($neweggAttribute['name']) {
                        case 'SellerPartNumber':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'Manufacturer':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'ManufacturerPartNumberOrISBN':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'WebsiteShortTitle':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'ProductDescription':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'ItemWeight':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'PacksOrSets':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'ItemCondition':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'ShippingRestriction':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        case 'Shipping':
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                        default:
                            if (isset($neweggAttribute['presta_attr_code']) && $neweggAttribute['presta_attr_code']) {
                                $attributeCode = '';
                                if($neweggAttribute['presta_attr_code']!= '--Set Default Value--'){
                                    $attributeCode = $neweggAttribute['presta_attr_code'];
                                }
                                if ($attributeCode){                                    
                                    $attributeValue = $this->getMappingValues($id, $productArray ,$attributeCode);
                                }else{
                                    $attributeValue = $neweggAttribute['default'];
                                }
                            } else {
                                $result['error'] .= $neweggAttribute['name'] . ' is a required field. </br>';
                            }
                            break;
                    }
                }
            } 

                $sql = "UPDATE " . _DB_PREFIX_ . "newegg_profile_product SET `newegg_validation_error` = '".$result['error']."' where `profile_id`=".$profileId." and  `product_id`= ".$id;
                Db::getInstance()->execute($sql);

        } catch (\Exception $e) {
            die($e->getMessage().'::'.$e->getLine().'::'.$e->getFile());
        }
    }

    public function profileData($id) {
        $db = Db::getInstance();
        $result = $db->executeS(
            "SELECT * FROM `" . _DB_PREFIX_ . "newegg_profile` 
                where `id`='" . (int)$id . "'"
        );
        return $result[0];
    }

     /**
     * create simple product on newegg
     * @param array $ids
     * @throws \Exception
     */
    private function prepareSimpleProducts($ids = [])
    {
        try {
            if (is_array($ids) && count($ids) > 0) {
                $newegg_envelope = array();
                $newegg_envelope['Header'] = array('DocumentVersion' => '1.0');
                $newegg_envelope['MessageType'] = 'BatchItemCreation';
                $newegg_envelope['Overwrite'] = 'No';
                $message = array();
                $itemFeeds = array();
                $post_data = array();
                $this->key = 0;
                foreach ($ids as $id) {
                    $product = $this->product->create()->setStoreId($this->selectedStore)->load($id['id']);
                    $profileData = $this->profileproducts->create()->getCollection()->addFieldToFilter('account_id', $this->accountId)->addFieldToFilter('product_id', $id['id']);
                    $profileData = $this->profileproducts->create()->load($profileData->getColumnValues('id'));
                    $profileId = $profileData->getprofile_id()/*$profileData->getColumnValues('profile_id')*/;
                    $productStatus =$profileData->getnewegg_product_status() /*$profileData->getColumnValues('newegg_product_status')*/;
                    $profileProductsId = $profileData->getId()/*$profileData->getColumnValues('id')*/;
                    $profile = $this->profile->create()->load($profileId);
                    $categoryId = isset(explode(':', $profile->getProfileCategory())[0]) ? explode(':', $profile->getProfileCategory())[0] : null;
                    $categoryName = isset(explode(':', $profile->getProfileCategory())[1]) ? explode(':', $profile->getProfileCategory())[1] : null;
                    if (!$categoryId) {
                        continue;
                    }
                    if ($this->key == 0) {
                        $item = array();
                        $itemFeed = array();
                        $itemFeed['SummaryInfo'] = array('SubCategoryID' => $categoryId);
                    }
                    $productArray = $product->toArray();
                    if ($productStatus == 'uploaded') {
                        $item['Action'] = 'Update Item';
                    } else {
                        $item['Action'] = 'Create Item';
                    }
                    $item['BasicInfo'] = $this->getProductInfo($productArray, $id['id'], null, $product, $profile);
                    $item['SubCategoryProperty'] = $this->getCategoryDataModified($product, $categoryName, $profile, $productArray);
                    $itemFeed['Item'][] = $item;
                    $this->key++;
                    $profileProducts = $this->profileproducts->create()->load($profileProductsId);
                    $profileProducts->setData('newegg_product_status', 'uploaded')->save();
                }
                $message['Itemfeed'] = $itemFeed;
                $newegg_envelope['Message'] = $message;
                $post_data['NeweggEnvelope'] = $newegg_envelope;
                $data = json_encode($post_data);

                $response = $this->dataHelper->postRequest('/datafeedmgmt/feeds/submitfeed', $this->account, ['body' => $data,
                    'append' => '&requesttype=ITEM_DATA']);
                $this->responseParse($response, $this->account);
            }
        } catch (\Exception $e) {
            $messages['error'] = $e->getMessage();
            $this->logger->addError($messages['error'], ['path' => __METHOD__]);
        }
    }

    public function getMappingValues($product_id, $product, $attr_code)
    {          
                    $attribute = $attr_code;
                    $attr_id = explode("-",$attribute);
                    $attr_type = $attr_id[0];
                    $attribute_id = $attr_id[1];
                    switch ($attr_type) {
                        case 'attribute':
                            if ($mapped_attribute_id) {
                                $attr_val = $this->getAttributeValue($attribute_id, $product_id);
                            } else {
                                $attr_val = $attribute_id;
                            }
                            break;
                        case 'feature':
                            $attr_val = $this->getFeatureValue($attribute_id, $product_id);
                            break;
                        case 'system':
                            $attr_val = $this->getSystemValue($attribute_id, $product, $product_id);
                            break;
                        default:
                            $attr_val = '';
                            break;
                    }

               return $attr_val;
    }

    public function getAttributeValue($attribute_group_id, $product_id)
    {
        $sql_db_intance = Db::getInstance(_PS_USE_SQL_SLAVE_);
        $features = $sql_db_intance->executeS('
	        SELECT *
			FROM ' . _DB_PREFIX_ . 'product_attribute pa
			LEFT JOIN ' . _DB_PREFIX_ . 'product_attribute_combination pac 
			ON pac.id_product_attribute = pa.id_product_attribute
			LEFT JOIN ' . _DB_PREFIX_ . 'attribute a 
			ON a.id_attribute = pac.id_attribute
			LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group ag 
			ON ag.id_attribute_group = a.id_attribute_group
			LEFT JOIN ' . _DB_PREFIX_ . 'attribute_lang al 
			ON (a.id_attribute = al.id_attribute AND al.id_lang = "' . (int)$this->defaultLang . '")
			LEFT JOIN ' . _DB_PREFIX_ . 'attribute_group_lang agl 
			ON (ag.id_attribute_group = agl.id_attribute_group 
			AND agl.id_lang = "' . (int)$this->defaultLang . '")
			WHERE pa.id_product = "' . (int)$product_id . '" 
			AND a.id_attribute_group = "' . (int)$attribute_group_id . '" 
			ORDER BY pa.id_product_attribute');
        if (isset($features['0']['name'])) {
            return $features['0']['name'];
        } else {
            return false;
        }
    }

    public function getFeatureValue($attribute_id, $product_id)
    {
        $sql_db_intance = Db::getInstance(_PS_USE_SQL_SLAVE_);

        $features = $sql_db_intance->executeS('
	        SELECT value FROM ' . _DB_PREFIX_ . 'feature_product pf
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_lang fl ON (fl.id_feature = pf.id_feature 
	        AND fl.id_lang = ' . (int)$this->defaultLang . ')
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature_value_lang fvl 
	        ON (fvl.id_feature_value = pf.id_feature_value 
	        AND fvl.id_lang = ' . (int)$this->defaultLang . ')
	        LEFT JOIN ' . _DB_PREFIX_ . 'feature f ON (f.id_feature = pf.id_feature 
	        AND fl.id_lang = ' . (int)$this->defaultLang . ')
	        ' . Shop::addSqlAssociation('feature', 'f') . '
	        WHERE pf.id_product = ' . (int)$product_id . ' 
	        AND fl.id_feature = "' . (int)$attribute_id . '" 
	        ORDER BY f.position ASC');
        if (isset($features['0']['value'])) {
            return $features['0']['value'];
        } else {
            return false;
        }
    }

    public function getSystemValue($attribute_id, $product, $product_id)
    {
        $db = Db::getInstance();
        if ($attribute_id == 'id_manufacturer') {
            if (isset($product['id_manufacturer']) && $product['id_manufacturer']) {
                $Execute = 'SELECT `name` FROM `' . _DB_PREFIX_ . 'manufacturer` 
                    where `id_manufacturer`=' . (int)$product['id_manufacturer'];
                $qresult = $db->ExecuteS($Execute);
                if (isset($qresult['0']["name"])) {
                    return $qresult['0']["name"];
                }
            }
        }
        if ($attribute_id == 'id_category_default') {
            if (isset($product['id_category_default']) && $product['id_category_default']) {
                $Execute = 'SELECT `name` FROM `' . _DB_PREFIX_ . 'category_lang` 
                    where `id_category`=' . (int)$product['id_category_default'] . ' 
                    AND `id_lang` = ' . (int)$this->defaultLang;
                $qresult = $db->ExecuteS($Execute);
                if (isset($qresult['0']["name"])) {
                    return $qresult['0']["name"];
                }
            }
        }
        if ($attribute_id == 'id_tax_rules_group') {
            if (isset($product['id_tax_rules_group']) && $product['id_tax_rules_group']) {
                $Execute = 'SELECT `rate` FROM `' . _DB_PREFIX_ . 'tax_rule` tr 
                    LEFT JOIN `' . _DB_PREFIX_ . 'tax` t on (t.id_tax = tr.id_tax) 
                    where tr.`id_tax_rules_group`=' . (int)$product['id_tax_rules_group'];
                $qresult = $db->ExecuteS($Execute);
                if (isset($qresult['0']["rate"])) {
                    return number_format($qresult['0']["rate"], 2);
                }
            }
        }
        if ($attribute_id == 'price_ttc') {
            $p = Product::getPriceStatic($product_id, true);
            return $p;
        }
        if (isset($product[$attribute_id])) {
            return $product[$attribute_id];
        } else {
            return false;
        }
    }


    public function productImageUrls($product_id = 0, $attribute_id = 0)
    {
        if ($product_id) {
            $additionalAssets = array();
            $default_lang = Context::getContext()->language->id;
            $db = Db::getInstance();
            $sql = 'SELECT * FROM `' . _DB_PREFIX_ . 'image` i 
            LEFT JOIN `' . _DB_PREFIX_ . 'image_lang` il 
            ON (i.`id_image` = il.`id_image`)';

            if ($attribute_id) {
                $sql .= ' LEFT JOIN `' . _DB_PREFIX_ . 'product_attribute_image` ai 
                ON (i.`id_image` = ai.`id_image`)';
                $attribute_filter = ' AND ai.`id_product_attribute` = ' . (int)$attribute_id;
                $sql .= ' WHERE i.`id_product` = ' . (int)$product_id . ' 
                AND il.`id_lang` = ' . (int)$default_lang . $attribute_filter . ' 
                ORDER BY i.`position` ASC';
            } else {
                $sql .= ' WHERE i.`id_product` = ' . (int)$product_id . ' 
                AND il.`id_lang` = ' . (int)$default_lang . ' ORDER BY i.`position` ASC';
            }

            $Execute = $db->ExecuteS($sql);
            if (version_compare(_PS_VERSION_, '1.7', '>=') === true) {
                $type = ImageType::getFormattedName('large');
            } else {
                $type = ImageType::getFormatedName('large');
            }
            $product = new Product($product_id);
            $link = new Link;
            if (count($Execute) > 0) {
                foreach ($Execute as $image) {
                    $image_url = $link->getImageLink(
                        $product->link_rewrite[$default_lang],
                        $image['id_image'],
                        $type
                    );
                    if (isset($image['cover']) && $image['cover']) {
                        $additionalAssets['mainImageUrl'] = (Configuration::get('PS_SSL_ENABLED') ?
                                'https://' : 'http://') . $image_url;
                    } else {
                        if (!isset($additionalAssets['mainImageUrl'])) {
                            $additionalAssets['mainImageUrl'] = (Configuration::get('PS_SSL_ENABLED') ?
                                    'https://' : 'http://') . $image_url;
                        } else {
                            $additionalAssets['productSecondaryImageURL'][] =
                                (Configuration::get('PS_SSL_ENABLED') ?
                                    'https://' : 'http://') . $image_url;
                        }
                    }
                }
            }
            return $additionalAssets;
        }
    }
     
    public function getAttributeValueByAttributeCode($product,$attributeCode){

        if($attributeCode=='EAN'){
            return $product->ean13;
        }
        return false;
    }

}
