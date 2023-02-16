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
 * @package   CedOnbuy
 */

include_once(_PS_MODULE_DIR_.'cednewegg/classes/CedNeweggProduct.php');

class CedNeweggCronModuleFrontController extends ModuleFrontController
{
    public function initContent()
    {
        if (Tools::getValue('secure_key')) {
            if (Tools::getIsset('method') && Tools::getValue('method')=='updateinventoryprice') {
                try {
                    $db = Db::getInstance();
                    $productIds = array();
                    $cedOnbuyProduct = new CedOnbuyProduct();
                    $cedOnbuyHelper = new CedOnbuyHelper();
                    $query = $db->executeS("SELECT `id_product` FROM `"._DB_PREFIX_."cedonbuy_products_queue`");
                    if (!$query || count($query) < 50) {
                        $db->execute("TRUNCATE TABLE `"._DB_PREFIX_."cedcdonlatest_product_queue`");
                        $pquery = $db->executeS("SELECT cp.`id_product` FROM `"._DB_PREFIX_."cednewegg_profile_product` cp 
            JOIN `"._DB_PREFIX_."product` p ON (p.id_product=cp.id_product)");
                        if (!empty($pquery)) {
                            $prowschunk = array_chunk($pquery, 500);
                            foreach ($prowschunk as $pRows) {
                                $psql = "INSERT INTO `"._DB_PREFIX_."cednewegg_products_queue` (id_product, `type`) VALUES ";
                                foreach ($pRows as $pRow) {
                                    $psql .= "('".$pRow['id_product']."','type'),";
                                }
                                $psql = rtrim($psql, ',');
                                $db->execute($psql);
                            }
                        }
                    }
                    $idquery = $db->executeS("SELECT DISTINCT `id_product` FROM `"._DB_PREFIX_."cedonbuy_products_queue` LIMIT 500");
                    if (!empty($idquery)) {
                        foreach ($idquery as $row) {
                            $ids[] = $row['id_product'];
                        }
                    }
                    if (!empty($ids)) {
                        $response[] = $cedOnbuyProduct->updateInventoryPrice($ids);
                        $db->execute("DELETE FROM `"._DB_PREFIX_."cedonbuy_products_queue` WHERE `id_product` IN (".implode(',', $ids).")");
                    } else {
                        $response[] = array("No Product Ids found to updated");
                    }
                    echo '<pre>';
                    print_r($response);

                    $cedOnbuyHelper->log(
                        'CronUpdateInventory',
                        'Info',
                        'Cron For Update Inventory',
                        json_encode(array(
                            'Ids' => $ids,
                            'Response' => $response
                        ))
                    );
                } catch (Exception $e) {
                    $cedOnbuyHelper->log(
                        'CronUpdateInventoryPrice',
                        'Exception',
                        'Update Inventory Price By Cron '.$e->getMessage()
                    );
                    print_r($e->getMessage());
                }
            }
            if (Tools::getIsset('method') && Tools::getValue('method')=='wipeoutlog') {
                $this->wipeoutLog();
            }
        }



        die('Done');
    }

    public function wipeoutLog()
    {
//        die('YOU\'RE IN');
        $cedOnbuyHelper = new CedOnbuyHelper();
        try {
            if (!Tools::isSubmit('secure_key')
                || Tools::getValue('secure_key') !=
                Configuration::get('CEDONBUY_CRON_SECURE_KEY')) {
                return array('success' => false, 'message' => 'Secure key not matched');
            }
            $days_before = (int) Configuration::get('CEDONBUY_WIPEOUT_NO_OF_DAYS');
            if ($days_before > 0) {
                $days_before = date('Y-m-d', strtotime("-".$days_before." days"));
            } else {
                $days_before = date('Y-m-d', strtotime("-3 days"));
            }
//            die('YOU\'RE IN SECOND TIME');
            $db = Db::getInstance();
//            $sql = $db->ExecuteS("SELECT * FROM `". _DB_PREFIX_ ."cedonbuy_logs`");
//            echo '<pre>'; print_r($days_before); die;
//            echo '<pre>'; print_r($sql); die;

//            $db = Db::getInstance();
            $sql = $db->ExecuteS("SELECT * FROM `". _DB_PREFIX_ ."cedonbuy_logs` 
            WHERE `created_at` LIKE '%". pSQL($days_before) ."%' ");
//            echo '<pre>'; print_r($sql); die;
            // `created_at` LIKE '%". pSQL($three_days_before) ."%'

            if(isset($sql[0]) && !empty($sql[0]))
            {
                $db->Execute("DELETE FROM `". _DB_PREFIX_ ."cedonbuy_logs`
                 WHERE `created_at` LIKE '%". pSQL($days_before) ."%' ");
                $response = 'All log(s) cleared before ' . $days_before;
            } else {
                $response = 'No log(s) present before ' . $days_before;
            }
            // Configuration::updateValue('CEDONBUY_WIPEOUT_LOG_CRON_LAST_EXECUTION', date("Y-m-d H:i:s"));
            $cedOnbuyHelper->log(
                'wipeoutLog',
                'Response',
                'wipeoutLog Response',
                json_encode($response)
            );
            echo '<pre>';
            print_r($response);
            die;
        } catch(Exception $e) {
            $cedOnbuyHelper->log(
                'wipeoutLog',
                'Exception',
                'wipeoutLog Exception',
                json_encode(array(
                    'success' => false,
                    'message' => $e->getMessage()
                ))
            );
            echo '<pre>';
            print_r($e->getMessage());
            echo '</pre>';
            die;
        }
    }
}
