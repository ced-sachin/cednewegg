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
        $this->validateAllProducts($product_ids,$profile_id,$account_id);
        return $exec;
    }

    public function profileData($id) {
        $db = Db::getInstance();
        $result = $db->executeS(
            "SELECT * FROM `" . _DB_PREFIX_ . "newegg_profile` 
                where `id`='" . (int)$id . "'"
        );
        // echo '<pre>'; print_r($result[0]); die('<br>aaaa');
        return $result[0];
    }

    /**
     * validate simple as well as configurable products
     * @param array $ids
     * @return array
     */
    public function validateAllProducts($ids = [],$profile_id,$account_id)
    {
            $validatedProducts = [
                'simple' => [],
                'configurable' => [],
            ];
            $this->ids = [];

            foreach ($ids as $id) {

                $profileData = $this->profileData($profile_id);
                // $profileData = $this->profileproducts->create()->load($profileData->getColumnValues('id'));
                $profileId =$profileData['id'];
                // $id_lang = Context::getContext()->language->id;
                $product = new Product($id);
                // $productAttr = $product->getAttributeCombinations((int)$id_lang, true);
                $profile = $profileData;
                
                // echo '<pre>'; print_r($product); die('<br>'.__FILE__); 
                
                if (empty($profile)) {
                    $validatedProducts['errors'][$product->reference()] =
                        'Please assign product to a profile and try again.';
                    continue;
                }

                if ($product->getTypeId() == 'simple' && $product->getVisibility() != 1) {
                    // case 2 : for simple products
                    $productId = $this->validateProduct($product->getId(), $product, $profile, $profileProductsId);

                    if (isset($productId['id'])) {
                        $validatedProducts['simple'][$product->getId()] = [
                            'id' => $productId['id'],
                            'type' => 'simple',
                            'variantid' => null,
                            'variantattr' => null,
                            'category' => $profile->getProfileCategory(),
                            'profile_id' => $profile->getId()
                        ];
                    } elseif (isset($productId['errors']) and is_array($productId['errors'])) {
                        $errors[$product->getSku()] = [
                            'sku' => $product->getSku(),
                            'id' => $product->getId(),
                            'url' => $this->urlBuilder
                                ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                            'errors' => $productId['errors']
                        ];
                    }
                }
            }
        return $validatedProducts;
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
    public function validateProduct($id, $product, $profile, $profileProductsId, $parentId = null)
    {
        try {
            $validatedProduct = false;
            if ($product == null) {
                $product = $this->product->create()->setStoreId($this->selectedStore)
                    ->load($id);
            }
            $profileId = $profile->getId();
            $sku = $product->getSku();
            $productArray = $product->toArray();

            $errors = [];
            if (isset($profileId) and $profileId != false) {
                $category = $profile->getProfileCategory();
                $requiredAttributes = json_decode($profile->getProfileReqOptAttribute(), 1);
                foreach ($requiredAttributes as $neweggAttributeId => $neweggAttribute) {
                    if ($id == $parentId) {
                        if ($neweggAttribute['name'] == 'SellingPrice') {
                            continue;
                        }
                    }
                    if (!isset($productArray[$neweggAttribute['magento_attribute_code']])
                        || (isset($productArray[$neweggAttribute['magento_attribute_code']]) && empty($productArray[$neweggAttribute['magento_attribute_code']]))
                    ) {
                        if ($neweggAttribute['magento_attribute_code'] == 'default' && !empty($neweggAttribute['default'])) {
                            continue;
                        }
                        $errors["$neweggAttributeId"] = "Required attribute empty or not mapped. [{$neweggAttribute['name']}]";
                    } elseif (isset($neweggAttribute['options']) and
                        !empty($neweggAttribute['options'])
                    ) {
                        $valueId = $product->getData($neweggAttribute['magento_attribute_code']);
                        $value = "";
                        $defaultValue = "";
                        if (isset($neweggAttribute['default']) and
                            !empty($neweggAttribute['default'])
                        ) {
                            $defaultValue = $neweggAttribute['default'];
                        }
                        $attr = $product->getResource()->getAttribute($neweggAttribute['magento_attribute_code']);
                        if ($attr && ($attr->usesSource() || $attr->getData('frontend_input') == 'select')) {
                            $value = $attr->getSource()->getOptionText($valueId);
                            if (is_object($value)) {
                                $value = $value->getText();
                            }
                        }
                    }
                }

                $productImages = $product->getMediaGalleryImages();
                if ($productImages->getSize() > 0) {
                    foreach ($productImages as $image){
                        $response=$this->imageValidate($image->getUrl());
                        if(!$response){
                            $errors['Image']="Not valid Image. [{'Image'}]";
                        }
                    }

                }else{
                    $errors['Image']="Required attribute empty . [{'Image'}]";
                }
                if (!empty($errors)) {
                    $validatedProduct['errors'] = $errors;
                    $e = [];
                    $e[$product->getSku()] = [
                        'sku' => $product->getSku(),
                        'id' => $product->getId(),
                        'url' => $this->urlBuilder
                            ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                        'errors' => [$errors]
                    ];
                    if ($parentId < 1) {
                        $profileProducts = $this->profileproducts->create()->load($profileProductsId);
                        $profileProducts->setData('newegg_validation', 'invalid')
                            ->setData('newegg_validation_error', $this->json->jsonEncode($e))->save();
                    }

                } else {
                    $this->ids[] = $product->getId();
                    if ($parentId < 1) {
                        $profileProducts = $this->profileproducts->create()->load($profileProductsId);
                        $profileProducts->setData('newegg_validation_error', '["valid"]')
                            ->save();
                    }
                    $validatedProduct['id'] = $id;
                    $validatedProduct['category'] = $category;
                }
            } else {
                $errors = [
                    "sku" => "$sku",
                    "id" => "$id",
                    "url" => $this->urlBuilder
                        ->getUrl('catalog/product/edit', ['id' => $product->getId()]),
                    "errors" =>
                        [
                            "Profile not found" => "Product is not mapped in any newegg profile"
                        ]
                ];
                $validatedProduct['errors'] = $errors;
                $errors = $this->json->jsonEncode([$errors]);
                if ($parentId < 1) {
                    $profileProducts = $this->profileproducts->create()->load($profileProductsId);
                    $profileProducts->setData('newegg_validation', 'invalid')
                        ->setData('newegg_validation_error', $errors)->save();
                }
            }
            return $validatedProduct;
        } catch (\Exception $e) {
            $messages['error'] = $e->getMessage();
            $this->logger->addError($messages['error'], ['path' => __METHOD__]);
        }
    }

}
