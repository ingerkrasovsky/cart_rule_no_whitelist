<?php

if (!defined('_PS_VERSION_')) {
    exit;
}

class cart_rule_no_whitelist extends Module
{
    public function __construct()
    {
        $this->name = 'cart_rule_no_whitelist';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Igor Krasovsky';
        $this->need_instance = 0;

        $this->bootstrap = true;

        parent::__construct();
        $this->displayName = $this->l('Cart Rule No Whitelist');
        $this->description = $this->l('A module that prevents the automatic addition of new cart rules (Cart Rule) to the whitelist of other rules.');
        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    public function install()
    {
        return parent::install();
    }

    public function uninstall()
    {
        return parent::uninstall();
    }
}