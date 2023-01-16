<?php

class CedNeweggAccount extends ObjectModel
{
    public static $definition = array(
        'table' => 'newegg_account',
        'primary' => 'id',
        'multilang' => false,
        'fields' => array(
            'id' => array(
                'title' => 'Account ID',
                'required' => true,
                'type' => self::TYPE_INT,

            ),
            'account_code' => array(
                'title' => 'Account Code',
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
            ),
            'account_status' => array(
                'title' => 'Status',
                'type' => self::TYPE_STRING,
                'required' => true,
                'validate' => 'isGenericName',
            ),
        ),
    );
    /**
     * @param $profile_id
     * @return array
     * @throws PrestaShopDatabaseException
     */
    public function getAccountDataById($account_id)
    {
        $db = Db::getInstance();
        $profileData = array(
                'accountCode' => '',
                'accountStatus' => '',
                'sellerId' => '',
                'secretKey' => '',
                'authorizationKey' => '',
                'accountLocation' => '',
                'root_cat' => '',
                'warehouseLocation' => '',
        );
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_accounts` WHERE `id`=" . (int)$account_id;
        $result = $db->executeS($sql);
        if (isset($result[0]) && $result[0] && is_array($result[0])) {
            $data = $result[0];
           
        }
        return $accountData;
    }

    /**
     * @param $account_id
     * @return array
     */
    public function getAccountCategoryById($account_id)
    {
        try {
            $db = Db::getInstance();
            $sql = "SELECT `fruugo_category_name` FROM `" . _DB_PREFIX_ . "newegg_account` WHERE `id`=" .
                (int)$account_id;
            $result = $db->executeS($sql);
            if (isset($result[0]['fruugo_category_name']) && $result[0]['fruugo_category_name']) {
                $fruugo_category = $result[0]['fruugo_category_name'];
                return array('success' => true, 'message' => $fruugo_category);
            } else {
                return array('success' => false, 'message' => 'Category not found');
            }
        } catch (\Exception $e) {
            return array('success' => false, 'message' => $e->getMessage());
        }
    }


    // public function deleteAccount($accountIds) {
    //    print_r($accountIds); die('dfdf');
    // }
    /**
     * @param $id
     * @return bool
     */
    public function deleteAccount($ids)
    { //print_r($ids); die('dfdf');
        $db = Db::getInstance();
        if (is_array($ids) && !empty($ids)) {
            foreach($ids as $id){
               
                    $res = $db->delete(
                        'newegg_accounts',
                        'id=' . (int)$id
                    );
                // print_r($res); die();
            }
            if ($res) {
                return true;
            }
        }

        return false;
    }

    /**
     * @param $account_id
     * @return array|mixed
     * @throws PrestaShopDatabaseException
     */
    public function getMappedFruugoCategoryById($account_id)
    {
        $mapped_fruugo_categories = array();
        $db = Db::getInstance();
        $sql = "SELECT `fruugo_category_name` FROM `" . _DB_PREFIX_ . "fruugo_account` WHERE `id`=" . (int)$account_id;
        $result = $db->executeS($sql);

        if (isset($result[0]) && $result[0]) {
            $mapped_fruugo_categories = json_decode($result[0], true);
        }
        return $mapped_fruugo_categories;
    }

    /**
     * @param array $accountData
     * @return array
     */
   

    public function duplicateAccount($accountId = '')
    {
        $db = Db::getInstance();
        try {
            $accountData = $this->getAccountDataById($accountId);
            if (isset($accountData) && is_array($accountData) && !empty($accountData))
            {
                $account_name = $accountData['accountInfo']['accountTitle'] . ' duplicated';
                $res = $db->insert(
                    'fruugo_account',
                    array(
                        'title' => pSQL($account_name),
                        'status' => (int)$accountData['accountInfo']['accountStatus'],
                        'original_price_rule_type' => pSQL($accountData['accountInfo']['accountOriginalPriceRuleType']),   // added
                        'original_price_rule_value' => pSQL($accountData['accountInfo']['accountOriginalPriceRuleValue']), // added
                        'sale_price_rule_type' => pSQL($accountData['accountInfo']['accountSalePriceRuleType']),   // added
                        'sale_price_rule_value' => pSQL($accountData['accountInfo']['accountSalePriceRuleValue']), // added
                        'fruugo_category_name' => pSQL($accountData['accountFruugoCategoryName']),
                        'fruugo_category_id' => (int) $accountData['accountFruugoCategoryId'],
                        'account_attribute_mapping' => pSQL(json_encode($accountData['accountAttributes'])),
                        'account_store_categories' => pSQL(json_encode($accountData['accountStoreCategories'])),
                        'account_variant_mapping' => pSQL(json_encode($accountData['accountVariant'])),
                        'account_default_mapping' => pSQL(json_encode($accountData['accountDefault']))
                    )
                );
                $newAccountId = $db->Insert_ID();
                if($res)
                    return true;
            }
            return false;
        } catch (\Exception $e) {
            return false;
        }
    }

}
