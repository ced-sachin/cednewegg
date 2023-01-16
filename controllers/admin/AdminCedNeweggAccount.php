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

 require_once _PS_MODULE_DIR_ ."/cednewegg/classes/CedNeweggAccount.php";

 class AdminCedNeweggAccountController extends ModuleAdminController {

    public function __construct()
    {
            $db = Db::getInstance();
            
            $this->id_lang = Context::getContext()->language->id;
            $this->bootstrap = true;
            $this->table = 'newegg_accounts';
            $this->identifier = 'id';
            $this->list_no_link = true;
            $this->addRowAction('edit');
            $this->addRowAction('duplicateAccount');
            $this->addRowAction('deleteAccount');
            
            $this->fields_list = array(
                'id' => array(
                    'title' => 'ID',
                    'type' => 'text',
                    'align' => 'center',
                    'class' => 'fixed-width-xs',
                ),
                'account_code' => array(
                    'title' => 'Account Code',
                    'type' => 'text',
                    'align' => 'text-center',
                ),
                'account_status' => array(
                    'title' => 'Account Status',
                    'type' => 'bool',
                    'align' => 'text-center',
                    'class' => 'fixed-width-sm',
                    'orderby' => false
                ),
                'account_location' => array(
                    'title' => 'Account Location',
                    'type' => 'text',
                    'align' => 'text-center',
                ),
                'warehouse_location' => array(
                    'title' => 'Warehouse Location',
                    'type' => 'bool',
                    'align' => 'text-center',
                    'class' => 'fixed-width-sm',
                    'orderby' => false
                ),
                );  

                $this->bulk_actions = array(
                    'duplicate' => array(
                        'text' => ('Duplicate'),
                        'icon' => 'icon-copy',
                    ),
                    'delete_account' => array(
                        'text' => ('Delete'),
                        'icon' => 'icon-trash',
                    )
                );
                if (Tools::isSubmit('submitNeweggAccountSave')) {
                    $this->saveAccount();
                }
            parent::__construct();
    }
    

    public function postProcess(){
     
        $req = Tools::getAllValues();
        if (Tools::getIsset('submitBulkdeleteAccountnewegg_accounts') && Tools::getValue('submitBulkdeleteAccountnewegg_accounts')) {
            $id = Tools::getValue('newegg_accountsBox');
        }
        parent::postProcess();

    }
    
    

    public function apiValidation($SecretKey,$Authorization,$sellerId,$accountlocation,$url){
        switch ($accountlocation) {
            case "CAN":
                $accountDetail['url'] = "https://api.newegg.com/marketplace/can/";
                break;
            case "B2B":
                $accountDetail['url'] = "https://api.newegg.com/marketplace/b2b/";
                break;
            default:
                $accountDetail['url'] = "https://api.newegg.com/marketplace/";
                break;

        }
        $url = $accountDetail['url'] . $url . "?sellerid=" .$sellerId;
        
        $headers = array();
        $headers[] = "Content-Type: application/json";
        $headers[] = "Accept: application/json";
        $headers[] = "Authorization: " . $Authorization;
        $headers[] = "SecretKey: " . $SecretKey;
        $ch = curl_init();
        // print_r($url);
        // echo '<pre>';
        // print_r($headers);
        curl_setopt($ch, CURLOPT_URL,$url);
        curl_setopt($ch, CURLOPT_HEADER, false);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $serverOutput = curl_exec ($ch);
        curl_close ($ch);
        // print_r($serverOutput);die(__FILE__);
        return json_decode($this->formatJson($serverOutput), true);
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


    public function saveAccount()
    {
        $db = Db::getInstance();
        $accountData = Tools::getAllValues();
        $accountId = Tools::getValue('id');

            if (!isset($accountData['accountCode'])) {
                $accountData['accountCode'] = '';
            }

            if (!isset($accountData['sellerId'])) {
                $accountData['sellerId'] = '';
            }

            if (!isset($accountData['secretKey'])) {
                $accountData['secretKey'] = '';
            }

            if (!isset($accountData['authorizationKey'])) {
                $accountData['authorizationKey'] = '';
            }

            if (!isset($accountData['accountStatus'])) {
                $accountData['accountStatus'] = '';
            }

            if (!isset($accountData['accountLocation'])) {
                $accountData['accountLocation'] = '';
            }

            if (!isset($accountData['root_cat'])) {
                $accountData['root_cat'] = array();
            }

            if (!isset($accountData['warehouseLocation'])) {
                $accountData['warehouseLocation'] = '';
            }

            if (!isset($accountData['profileDefault'])) {
                $accountData['profileDefault'] = array();
            }


            $url='contentmgmt/item/inventory';
            $response= $this->apiValidation( $accountData['secretKey'],$accountData['authorizationKey'], $accountData['sellerId'], $accountData['accountLocation'],$url);
            if (is_null($response)) {
                $this->errors[] ='Incorrect Details Filled.';
            } else if(isset($response[0]['Code']) ){
                $this->errors[] ='Unable to Save Account Details Please Try Again. Reason : ' .$response[0]['Message'];
            }else{
            
            if (!empty($accountId)) {
              
                $res = $db->update(
                    'newegg_accounts',
                    array(
                        // 'account_code' => pSQL($accountData['accountCode']),
                        'account_location' => (int)$accountData['accountLocation'],
                        // 'account_store' => pSQL($accountData['accountStore']),
                        'account_status' => pSQL($accountData['accountStatus']),
                        'root_cat' => pSQL(json_encode($accountData['root_cat'])),
                        'seller_id' => pSQL($accountData['sellerId']),
                        'secret_key' => pSQL($accountData['secretKey']),
                        'authorization_key' => pSQL($accountData['authorizationKey']),
                        'warehouse_location' => pSQL($accountData['warehouseLocation'])
                    ),
                    'id=' . (int)$accountId
                );

                if ($res) {
                    $this->confirmations[] = "Account updated successfully.";
                }
            } else {
                $a_code = $db->getValue(
                    "SELECT `id` from `" . _DB_PREFIX_ . "newegg_accounts` 
                              WHERE `account_code`='" . pSQL($accountData['accountCode']) . "'"
                );
                if (!$a_code) {
                    $res = $db->insert(
                        'newegg_accounts',
                        array(
                            'account_code' => pSQL($accountData['accountCode']),
                        'account_location' => (int)$accountData['accountLocation'],
                        // 'account_store' => pSQL($accountData['accountStore']),
                        'account_status' => pSQL($accountData['accountStatus']),
                        'root_cat' => pSQL(json_encode($accountData['root_cat'])),
                        'seller_id' => pSQL($accountData['sellerId']),
                        'secret_key' => pSQL($accountData['secretKey']),
                        'authorization_key' => pSQL($accountData['authorizationKey']),
                        'warehouse_location' => pSQL($accountData['warehouseLocation'])
                        )
                    );

                    if ($res) {
                        $this->confirmations[] = "Account created successfully";
                    }
                    if ($res) {
                        $link = new LinkCore();
                        $controller_link = $link->getAdminLink('AdminCedNeweggAccount') . '&created=1';
                        Tools::redirectAdmin($controller_link);
                    }
                } else {
                    $this->errors[] = "The account code must be unique. " . $accountData['accountCode'] .
                        " is already assigned to account Id " . $a_code;
                }
            }
        }
    }

    public function initPageHeaderToolbar()
    {
            if (empty($this->display)) {
                $this->page_header_toolbar_btn['new_account'] = array(
                'href' => self::$currentIndex . '&addnewegg_accounts&token=' . $this->token,
                'desc' => $this->l('Create Account', null, null, false),
                'icon' => 'process-icon-new'
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
    {   $accountId = Tools::getValue('id');
        if(!empty($accountId)){
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
        $sql = "SELECT * FROM `" . _DB_PREFIX_ . "newegg_accounts` WHERE `id`=" .$accountId;
        $result = $db->executeS($sql);
        if (isset($result[0]) && $result[0] && is_array($result[0])) {
            $data = $result[0];
            // print_r(json_decode($data['root_cat'],true)); die(__FILE__);
        }
        $accountData['accountCode'] = isset($data['account_code']) ? $data['account_code'] : '';
        $accountData['accountStatus'] = isset($data['account_status']) ? $data['account_status'] : '';
        $accountData['sellerId'] = isset($data['seller_id']) ? $data['seller_id'] : '';
        $accountData['secretKey'] = isset($data['secret_key']) ? $data['secret_key'] : '';
        $accountData['authorizationKey'] = isset($data['authorization_key']) ? $data['authorization_key'] : '';
        $accountData['accountLocation'] = isset($data['account_location']) ? $data['account_location'] : '';
        // $accountData['root_cat'] = isset($data['root_cat']) ? json_decode($data['root_cat'],true) : '';
        $accountData['warehouseLocation'] = isset($data['warehouse_location']) ? $data['warehouse_location'] : '';
        // die($accountData['root_cat']);
        $this->context->smarty->assign(array('accountId' => $accountId));
        $this->context->smarty->assign(array('root_cat' => json_decode($data['root_cat'],true)));
        $this->context->smarty->assign(array(
            'token' => Tools::getAdminTokenLite('AdminCedNeweggAccount'),
            'currentToken' => Tools::getAdminTokenLite('AdminCedNeweggAccount'),
            'controllerUrl' => $this->context->link->getAdminLink('AdminCedNeweggAccount'),
            'accountCode' =>$accountData['accountCode'],
            'accountStatus' =>$accountData['accountStatus'],
            'sellerId' =>$accountData['sellerId'],
            'secretKey' =>$accountData['secretKey'],
            'authorizationKey' =>$accountData['authorizationKey'],
            'accountLocation' =>$accountData['accountLocation'],
            // 'root_cat' =>$accountData['root_cat'],
            'warehouseLocation' =>$accountData['warehouseLocation'],
        ));
    }
        parent::renderForm();
        
        return $this->context->smarty->fetch(
            _PS_MODULE_DIR_ . 'cednewegg/views/templates/admin/account/edit_account.tpl'
        );
    }
   
    public function processBulkDeleteAccount() {
       if(!empty($this->boxes)) {
            $account_ids = $this->boxes;
            $cedAccountHelper = new CedNeweggAccount();
          $result = $cedAccountHelper->deleteAccount($account_ids);
            // print_r($result); die('dd');
            if($result == 1){
                $this->confirmations[] = "Account Deleted successfully.";
            }else{
                $this->error[] = "Account not deleted";
            }
       }
    }

 }