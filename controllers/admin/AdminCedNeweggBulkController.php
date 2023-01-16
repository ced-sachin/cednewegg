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
 * @package   CedFruugo
 */

include_once _PS_MODULE_DIR_ . 'cedfruugo/classes/CedfruugoProfile.php';
include_once _PS_MODULE_DIR_ . 'cedfruugo/classes/CedfruugoProduct.php';

class AdminCedNeweggBulkController extends ModuleAdminController
{
    public $productHelper;
    public function __construct()
    {
        $license = Configuration::get('CEDFRUUGO_LICENSE_VALID');
        if(!$license) {
            $this->errors[] = 'PLEASE VALIDATE YOUR LICENSE KEY FIRST!';
        } else {
            $this->productHelper = new CedfruugoProduct();
            $this->bootstrap  = true;
        }
        parent::__construct();
    }

    public function initPageHeaderToolbar()
    {
        $license = Configuration::get('CEDFRUUGO_LICENSE_VALID');
        if(!$license) {
            $this->errors[] = 'PLEASE VALIDATE YOUR LICENSE KEY FIRST!';
        } else {
            if (empty($this->display)) {
                $link = new LinkCore();
                $this->page_header_toolbar_btn['backtolist'] = array(
                'href' => $link->getAdminLink('AdminCedfruugoProduct'),
                'desc' => $this->l('Back To Product List', null, null, false),
                'icon' => 'process-icon-back'
            );
            }
        }
        parent::initPageHeaderToolbar();
    }

    public function initContent()
    {
        try {
            $db = Db::getInstance();
            $content = null;
            $profileProductsIds = array();
            $sql = "SELECT `id_product` FROM `" . _DB_PREFIX_ . "fruugo_profile_products`";
            $result = $db->executeS($sql);
            if (isset($result) && is_array($result) && !empty($result)) {
                foreach ($result as $res) {
                    $profileProductsIds[] = $res['id_product'];
                }
            }

            parent::initContent();
            $link = new LinkCore();
            $controllerUrl = $link->getAdminLink('AdminCedfruugoBulkUpload');
            $token = $this->token;
            $this->context->smarty->assign(array('controllerUrl' => $controllerUrl));
            $this->context->smarty->assign(array('token' => $token));

            if (Tools::getIsset('refresh_profile') && Tools::getValue('refresh_profile')) {
                $profileIds = array();
                $query = $db->executeS("SELECT * FROM `" . _DB_PREFIX_ . "fruugo_profile`");
                if (isset($query[0]) && !empty($query[0]) && is_array($query)) {
                    foreach ($query as $key => $profile_data) {
                        $profileIds[] = $profile_data['id'];
                    }
                }

                $link = new LinkCore();
                $controllerUrl = $link->getAdminLink('AdminCedfruugoProfile');
                $token = explode('token=', $controllerUrl)[1];

                $this->context->smarty->assign(array('controllerUrl' => $controllerUrl));
                $this->context->smarty->assign(array('token' => $token));
                $this->context->smarty->assign(array(
                    'profile_array' => addslashes(json_encode($profileIds))
                ));
                $content = $this->context->smarty->fetch(
                    _PS_MODULE_DIR_ . 'cedfruugo/views/templates/admin/product/bulk_refresh_profile_form.tpl'
                );
            }

            $this->context->smarty->assign(array(
                'content' => $this->content . $content
            ));
        } catch (\Exception $e) {
            $db->delete(
                'cedfruugo_products_chunk'
            );
        }
    }
}
