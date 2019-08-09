<?php
/**
* 2007-2019 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2019 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*/

if (!defined('_PS_VERSION_')) {
    exit;
}

class Vm_closed_landing extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'vm_closed_landing';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Lecoutre Ayrton';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Vents et Marées Gestion Fermeture');
        $this->description = $this->l('Module pour la fermeture saison Vents et Marées');

        $this->ps_versions_compliancy = array('min' => '1.6', 'max' => _PS_VERSION_);
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('VM_CLOSED_LANDING_LIVE_MODE', false);

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('backOfficeHeader') &&
            $this->registerHook('displayFooter');
    }

    public function uninstall()
    {
        Configuration::deleteByName('VM_CLOSED_LANDING_LIVE_MODE');

        return parent::uninstall();
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool)Tools::isSubmit('submitVm_closed_landingModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path.'views/templates/admin/configure.tpl');

        return $output.$this->renderForm();
    }

    /**
     * Create the form that will be displayed in the configuration of your module.
     */
    protected function renderForm()
    {
        $helper = new HelperForm();

        $helper->show_toolbar = false;
        $helper->table = $this->table;
        $helper->module = $this;
        $helper->default_form_language = $this->context->language->id;
        $helper->allow_employee_form_lang = Configuration::get('PS_BO_ALLOW_EMPLOYEE_FORM_LANG', 0);

        $helper->identifier = $this->identifier;
        $helper->submit_action = 'submitVm_closed_landingModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            .'&configure='.$this->name.'&tab_module='.$this->tab.'&module_name='.$this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = array(
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        );

        return $helper->generateForm(array($this->getConfigForm()));
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return array(
            'form' => array(
                'legend' => array(
                'title' => $this->l('Settings'),
                'icon' => 'icon-cogs',
                ),
                'input' => array(
                    array(
                        'type' => 'text',
                        'label' => $this->l('Status'),
                        'name' => 'VM_CLOSED_LANDING_LIVE_MODE',
                        'desc' => $this->l('ouvert/ferme')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('URL De redirection'),
                        'name' => 'VM_CLOSED_LANDING_URL_CMS',
                        'desc' => $this->l('Url de retour Begles')
                    ),
                    array(
                        'type' => 'text',
                        'label' => $this->l('URL De redirection'),
                        'name' => 'VM_CLOSED_LANDING_URL_CMS_SMJ',
                        'desc' => $this->l('Url de retour Saint Medart')
                    ),
                ),
                'submit' => array(
                    'title' => $this->l('Save'),
                ),
            ),
        );
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return array(
            'VM_CLOSED_LANDING_LIVE_MODE' => Configuration::get('VM_CLOSED_LANDING_LIVE_MODE', null),
            'VM_CLOSED_LANDING_URL_CMS_SMJ' => Configuration::get('VM_CLOSED_LANDING_URL_CMS_SMJ', null),
            'VM_CLOSED_LANDING_URL_CMS' => Configuration::get('VM_CLOSED_LANDING_URL_CMS', null)
        );
    }

    /**
     * Save form data.
     */
    protected function postProcess()
    {
        $form_values = $this->getConfigFormValues();

        foreach (array_keys($form_values) as $key) {
            Configuration::updateValue($key, Tools::getValue($key));
        }
    }

    /**
    * Add the CSS & JavaScript files you want to be loaded in the BO.
    */
    public function hookBackOfficeHeader()
    {
        if (Tools::getValue('module_name') == $this->name) {
            $this->context->controller->addJS($this->_path.'views/js/back.js');
            $this->context->controller->addCSS($this->_path.'views/css/back.css');
        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */
    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path.'/views/js/front.js');
    }
    
    public function hookDisplayFooter()
    {
        $current_url = 'https://'.$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI']; 
        $form_values = Configuration::get('VM_CLOSED_LANDING_LIVE_MODE', null);
        $url_vmcarte = Configuration::get('VM_CLOSED_LANDING_URL_CMS', null);
        $url_smjcarte = Configuration::get('VM_CLOSED_LANDING_URL_CMS_SMJ', null);
        $url_portail = 'https://vents-et-marees.com/portail.html';
        if($form_values == 'ferme'){
                $this->context->controller->addCSS($this->_path.'/views/css/front.css');
                if(strpos($current_url,'content') == false && strpos($current_url,'connexion') == false && strpos($current_url,'mon-compte') == false && strpos($current_url,'historique') == false && strpos($current_url,'adresses') == false && strpos($current_url,'identite') == false && strpos($current_url,'reduction') == false){
                    if(strpos($current_url,'vmcarte') !== false){
                        Tools::redirect($url_vmcarte);
                    }else if(strpos($current_url,'smjcarte') !== false){
                        Tools::redirect($url_smjcarte);
                    }else{
                        Tools::redirect($url_portail);
                    }
                }
        } 

    }
}
