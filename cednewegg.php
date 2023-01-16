<?php

class Cednewegg extends Module {
    public function __construct(){
        $this->name = 'cednewegg';
        $this->author = 'Cedcommerce';
        $this->version = '1.0.0';
        $this->bootstrap = true;
        parent::__construct();
        $this->displayName = $this->l('cednewegg');
        $this->description = $this->l('Newegg Integration By Cedcommerce Is a connector module for PrestaShop and Newegg.');
        $this->ps_version_compliancy = array('min'=>'1.6', 'max'=>_PS_VERSION_);        
    }

    public function install(){
    // calling hook displayHome immediately after install
        return parent::install() &&
        $this->installTab(
            'AdminCedNewegg',
            'Newegg Integration',
            0
        ) &&
        $this->installTab(
            'AdminCedNeweggConfig',
            'Configuration',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        ) &&
        $this->installTab(
            'AdminCedNeweggAccount',
            'Account',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        )&&
        $this->installTab(
            'AdminCedNeweggProfile',
            'Profile(s)',
            (int)Tab::getIdFromClassName('AdminCedNewegg')
        ) &&
        $this->installTab(
            'AdminCedNeweggProducts',
            'Product(s)',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        ) &&
        $this->installTab(
            'AdminCedNeweggOrders',
            'Order(s)',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        ) &&
        $this->installTab(
            'AdminCedNeweggFailedorder',
            'Failed Order(s)',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        ) &&
        $this->installTab(
            'AdminCedNeweggLogs',
            'Log(s)',
            (int) Tab::getIdFromClassName('AdminCedNewegg')
        );

    }

    public function installTab($class_name, $tab_name, $parent)
    {
        // Create new admin tab
        $tab = new Tab();
        $tab->name = array();
        foreach (Language::getLanguages(true) as $lang) {
            $tab->name[$lang['id_lang']] = $tab_name;
        }
        if ($parent == 0 && _PS_VERSION_ >= '1.7') {
            $tab->id_parent = (int)Tab::getIdFromClassName('SELL');
            $tab->icon = 'NE';
        } else {
            $tab->id_parent = $parent;
        }
        $tab->class_name = $class_name;
        $tab->module = $this->name;
        $tab->active = 1;
        return $tab->add();
    }

    public function uninstall(){
        return parent::uninstall();
    }

    public function uninstallTab($class_name)
    {
        $id_tab = (int)Tab::getIdFromClassName($class_name);
        if ($id_tab) {
            try {
                $tab = new Tab($id_tab);
                return $tab->delete();
            } catch (\Exception $e) {
                return false;
            }
        } else {
            return false;
        }
    }


    public function hookHeader(){
        $this->context->controller->addCSS(array(
            $this->_path.'views/css/cednewegg.css'
        ));
        $this->context->controller->addJS(array(
            $this->_path.'views/js/cednewegg.js'
        ));
    }

    public function getContent(){//all the configuration setting code will be done inside this function
        $output = '';

    // this part is executed only when the form is submitted
    if (Tools::isSubmit('submit' . $this->name)) {
        // retrieve the value set by the user
        $configValue0 = (string) Tools::getValue('CEDNEWEGG_LIVE_MODE');
        $configValue1 = (string) Tools::getValue('CEDNEWEGG_PRICE_VARIANT_TYPE');
        $configValue2 = (string) Tools::getValue('CEDNEWEGG_PRICE_VARIANT_AMOUNT');
        $configValue3 = (string) Tools::getValue('CEDNEWEGG_ORDER_NOTIFICATION_EMAIL');
        $configValue4 = (string) Tools::getValue('CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL');
        $configValue5 = (string) Tools::getValue('CEDNEWEGG_ORDER_PREFIX');
        $configValue6 = (string) Tools::getValue('CEDNEWEGG_FETCHORDER_OUTOFSTOCK');
        $configValue7 = (string) Tools::getValue('CEDNEWEGG_FETCHCREATE_PRODUCT');
        $configValue8 = (string) Tools::getValue('CEDNEWEGG_ORDER_CRON');
        $configValue9 = (string) Tools::getValue('CEDNEWEGG_INV_PRICE_CRON');
        // check that the value is valid
        // if (empty($configValue0) || !Validate::isGenericName($configValue0)) {
        //     // invalid value, show an error
        //     $output = $this->displayError($this->l('Invalid Configuration value'));
        // } else {
            // value is ok, update it and display a confirmation message
            Configuration::updateValue('CEDNEWEGG_LIVE_MODE', $configValue0);
            Configuration::updateValue('CEDNEWEGG_PRICE_VARIANT_TYPE', $configValue1);
            Configuration::updateValue('CEDNEWEGG_PRICE_VARIANT_AMOUNT', $configValue2);
            Configuration::updateValue('CEDNEWEGG_ORDER_NOTIFICATION_EMAIL', $configValue3);
            Configuration::updateValue('CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL', $configValue4);
            Configuration::updateValue('CEDNEWEGG_ORDER_PREFIX', $configValue5);
            Configuration::updateValue('CEDNEWEGG_FETCHORDER_OUTOFSTOCK', $configValue6);
            Configuration::updateValue('CEDNEWEGG_FETCHCREATE_PRODUCT', $configValue7);
            Configuration::updateValue('CEDNEWEGG_ORDER_CRON', $configValue8);
            Configuration::updateValue('CEDNEWEGG_INV_PRICE_CRON', $configValue9);
            $output = $this->displayConfirmation($this->l('Settings updated'));
        // }
    }

    // display any message, then the form
    return $output . $this->displayForm();
}

/**
 * Builds the configuration form
 * @return string HTML code
 */
public function displayForm()
{
    // Init Fields form array
    $fields_form0['form'] = array(
        'legend' => array(
            'title' => $this->l('General Settings'),
            'icon' => 'icon-cogs',
        ),
        'input' => array(
            array(
                'type' => 'switch',
                'label' => $this->l('Module Status'),
                'name' => 'CEDNEWEGG_LIVE_MODE',
                'is_bool' => true,
                'values' => array(
                    array(
                        'id' => 'active_on',
                        'value' => true,
                        'label' => $this->l('Yes')
                    ),
                    array(
                        'id' => 'active_off',
                        'value' => false,
                        'label' => $this->l('No')
                        )
                    ),
                )
            )
        );
        $fields_form1['form'] = array(
            'legend' => array(
                'title'=> $this->l('Newegg Product Setting'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'label' => $this->l('Product Price'),
                    'name' => 'CEDNEWEGG_PRICE_VARIANT_TYPE',
                    'required' => false,
                    'desc' => $this->l('Select to send a different product price to Newegg'),
                    'default_value' => '',
                    'options' => array(
                        'query' => array(
                            array('value' => 0, 'label' => '--  Select Price Variation --'),
                            array('value' => 1, 'label' => 'Regular Price'),
                            array('value' => 2, 'label' => 'Increase Fixed Amount'),
                            array('value' => 3, 'label' => 'Decrease Fix Amount'),
                            array('value' => 4, 'label' => 'Increase Fix Percent'),
                            array('value' => 5, 'label' => 'Decrease Fix Percent'),
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    )
                ),
                array(  
                    'col' => 3,
                    'type' => 'text',
                    'prefix' => '<i class="icon icon-envelope"></i>',
                    'desc' => $this->l('Amount to be variate on the basis of increment or decrement value.'),
                    'name' => 'CEDNEWEGG_PRICE_VARIANT_AMOUNT',
                    'label' => $this->l('Price Markup Value'),
                ),
            )
            );
        $fields_form2['form'] = array(
            'legend' => array(
                'title' => $this->l('Newegg Order Setting'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'col' => 6,
                    'type' => 'text',
                    'readonly' => false,
                    'name' => 'CEDNEWEGG_ORDER_NOTIFICATION_EMAIL',
                    'label' => $this->l('Order Notification Email'),
                ),
                array(
                    'col' => 6,
                    'type' => 'text',
                    'readonly' => false,
                    'name' => 'CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL',
                    'label' => $this->l('Default Customer email'),
                    'desc' => $this->l('Enter the mail id by which you want to create your customer'),
                ),
                array(
                    'col' => 6,
                    'type' => 'text',
                    'readonly' => false,
                    'name' => 'CEDNEWEGG_ORDER_PREFIX',
                    'label' => $this->l('Newegg Order Id Prefix'),
                    'desc' => $this->l('Prefix for Newegg Increment ID'),
                ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Order Fetch For Out Of Stock Product'),
                    'name' => 'CEDNEWEGG_FETCHORDER_OUTOFSTOCK',
                    'desc' => $this->l('Order Create for out of stock products'),
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => array(
                            array('value' => 0, 'label' => 'Yes'),
                            array('value' => 1, 'label' => 'No'),
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    )
                 ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Create New Product (if Not Exist) on the time of oreder creation'),
                    'name' => 'CEDNEWEGG_FETCHCREATE_PRODUCT',
                    'desc' => $this->l('Create New Product if SKU not found in Magento Store on the time of Order Fetch'),
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => array(
                            array('value' => 0, 'label' => 'Yes'),
                            array('value' => 1, 'label' => 'No'),
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    )
                )
            )
        );
        $fields_form3['form'] = array(
            'legend' => array(
                'title' => $this->l('Newegg Cron Setting'),
                'icon' => 'icon-cogs',
            ),
            'input' => array(
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Order Cron'),
                    'name' => 'CEDNEWEGG_ORDER_CRON',
                    'desc' => $this->l('If you want to fetch order automatically'),
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => array(
                            array('value' => 0, 'label' => 'Enable'),
                            array('value' => 1, 'label' => 'Disable'),
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    )
                ),
                array(
                    'type' => 'select',
                    'col' => 6,
                    'label' => $this->l('Inventory|Price Cron'),
                    'name' => 'CEDNEWEGG_INV_PRICE_CRON',
                    'desc' => $this->l('If you want to sync inventory|price automatically'),
                    'required' => false,
                    'default_value' => '',
                    'options' => array(
                        'query' => array(
                            array('value' => 0, 'label' => 'Enable'),
                            array('value' => 1, 'label' => 'Disable'),
                        ),
                        'id' => 'value',
                        'name' => 'label',
                    )
                )),
            'submit' => array(
                'title' => $this->l('Save'),
            ),
        );
    // echo '<pre>';print_r($form); die();
    $helper = new HelperForm();

    // Module, token and currentIndex
    $helper->table = $this->table;
    $helper->name_controller = $this->name;
    $helper->token = Tools::getAdminTokenLite('AdminModules');
    $helper->currentIndex = AdminController::$currentIndex . '&' . http_build_query(['configure' => $this->name]);
    $helper->submit_action = 'submit' . $this->name;

    // Default language
    $helper->default_form_language = (int) Configuration::get('PS_LANG_DEFAULT');

    // Load current value into the form
    $helper->fields_value['CEDNEWEGG_LIVE_MODE'] = Tools::getValue('CEDNEWEGG_LIVE_MODE', Configuration::get('CEDNEWEGG_LIVE_MODE'));
    $helper->fields_value['CEDNEWEGG_PRICE_VARIANT_TYPE'] = Tools::getValue('CEDNEWEGG_PRICE_VARIANT_TYPE', Configuration::get('CEDNEWEGG_PRICE_VARIANT_TYPE'));
    $helper->fields_value['CEDNEWEGG_PRICE_VARIANT_AMOUNT'] = Tools::getValue('CEDNEWEGG_PRICE_VARIANT_AMOUNT', Configuration::get('CEDNEWEGG_PRICE_VARIANT_AMOUNT'));
    $helper->fields_value['CEDNEWEGG_ORDER_NOTIFICATION_EMAIL'] = Tools::getValue('CEDNEWEGG_ORDER_NOTIFICATION_EMAIL', Configuration::get('CEDNEWEGG_ORDER_NOTIFICATION_EMAIL'));
    $helper->fields_value['CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL'] = Tools::getValue('CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL', Configuration::get('CEDNEWEGG_DEFAULT_CUSTOMER_EMAIL'));
    $helper->fields_value['CEDNEWEGG_ORDER_PREFIX'] = Tools::getValue('CEDNEWEGG_ORDER_PREFIX', Configuration::get('CEDNEWEGG_ORDER_PREFIX'));
    $helper->fields_value['CEDNEWEGG_FETCHORDER_OUTOFSTOCK'] = Tools::getValue('CEDNEWEGG_FETCHORDER_OUTOFSTOCK', Configuration::get('CEDNEWEGG_FETCHORDER_OUTOFSTOCK'));
    $helper->fields_value['CEDNEWEGG_FETCHCREATE_PRODUCT'] = Tools::getValue('CEDNEWEGG_FETCHCREATE_PRODUCT', Configuration::get('CEDNEWEGG_FETCHCREATE_PRODUCT'));
    $helper->fields_value['CEDNEWEGG_ORDER_CRON'] = Tools::getValue('CEDNEWEGG_ORDER_CRON', Configuration::get('CEDNEWEGG_ORDER_CRON'));
    $helper->fields_value['CEDNEWEGG_INV_PRICE_CRON'] = Tools::getValue('CEDNEWEGG_INV_PRICE_CRON', Configuration::get('CEDNEWEGG_INV_PRICE_CRON'));
    return $helper->generateForm(array($fields_form0, $fields_form1, $fields_form2, $fields_form3));
}
}