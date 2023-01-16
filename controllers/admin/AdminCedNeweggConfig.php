<?php 
 
 class AdminCedNeweggConfigController extends ModuleAdminController {

    public function __construct()
    {
        $this->bootstrap = true;
        $link = new LinkCore();
        $controller_link = $link->getAdminLink('AdminModules');
        Tools::redirectAdmin($controller_link .'&configure=cednewegg');
        parent::__construct();
    
    }

    public function ajaxProcessValidateCredentials(){
        
    }



 }