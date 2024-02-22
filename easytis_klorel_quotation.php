<?php
/**
 * 2007-2023 PrestaShop
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
 * @author    PrestaShop SA <contact@prestashop.com>
 * @copyright 2007-2023 PrestaShop SA
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 *  International Registered Trademark & Property of PrestaShop SA
 */

if (!defined('_PS_VERSION_')) {
    exit;
}

class Easytis_klorel_quotation extends Module
{
    protected $config_form = false;

    public function __construct()
    {
        $this->name = 'easytis_klorel_quotation';
        $this->tab = 'administration';
        $this->version = '1.0.0';
        $this->author = 'Klorel';
        $this->need_instance = 0;

        /**
         * Set $this->bootstrap to true if your module is compliant with bootstrap (PrestaShop 1.6)
         */
        $this->bootstrap = true;

        parent::__construct();

        $this->displayName = $this->l('Easytis x Klorel Quotation');
        $this->description = $this->l('Ajout de fonctionnalités au module de devis \"roja45quotationspro\"');

        $this->ps_versions_compliancy = ['min' => '1.6', 'max' => _PS_VERSION_];
    }

    /**
     * Don't forget to create update methods if needed:
     * http://doc.prestashop.com/display/PS16/Enabling+the+Auto-Update
     */
    public function install()
    {
        Configuration::updateValue('EASYTIS_KLOREL_QUOTATION_LIVE_MODE', false);
        $this->addColumnAdditionalShippingCost();

        return parent::install() &&
            $this->registerHook('header') &&
            $this->registerHook('actionDispatcher') &&
            $this->registerHook('displayBackOfficeHeader');
    }

    public function uninstall()
    {
        Configuration::deleteByName('EASYTIS_KLOREL_QUOTATION_LIVE_MODE');

        return parent::uninstall();
    }

    private function addColumnAdditionalShippingCost()
    {
        $sql = "ALTER TABLE " . _DB_PREFIX_ . "roja45_quotationspro_product 
            ADD `additional_shipping_cost` FLOAT(10,2) DEFAULT 0 AFTER `qty`";

        return Db::getInstance()->execute($sql);
    }

    /**
     * Load the configuration form
     */
    public function getContent()
    {
        /**
         * If values have been submitted in the form, process.
         */
        if (((bool) Tools::isSubmit('submitEasytis_klorel_quotationModule')) == true) {
            $this->postProcess();
        }

        $this->context->smarty->assign('module_dir', $this->_path);

        $output = $this->context->smarty->fetch($this->local_path . 'views/templates/admin/configure.tpl');

        return $output . $this->renderForm();
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
        $helper->submit_action = 'submitEasytis_klorel_quotationModule';
        $helper->currentIndex = $this->context->link->getAdminLink('AdminModules', false)
            . '&configure=' . $this->name . '&tab_module=' . $this->tab . '&module_name=' . $this->name;
        $helper->token = Tools::getAdminTokenLite('AdminModules');

        $helper->tpl_vars = [
            'fields_value' => $this->getConfigFormValues(), /* Add values for your inputs */
            'languages' => $this->context->controller->getLanguages(),
            'id_language' => $this->context->language->id,
        ];

        return $helper->generateForm([$this->getConfigForm()]);
    }

    /**
     * Create the structure of your form.
     */
    protected function getConfigForm()
    {
        return [
            'form' => [
                'legend' => [
                    'title' => $this->l('Settings'),
                    'icon' => 'icon-cogs',
                ],
                'input' => [
                    [
                        'type' => 'categories',
                        'label' => 'Select a category for "various" products',
                        'name' => 'EASYTIS_KLOREL_QUOTATION_ID_CATEGORY',
                        'tree' => [
                            'root_category' => 1,
                            'id' => 'id_category',
                            'name' => 'name_category',
                            'selected_categories' => [3],
                        ],
                    ],
                ],
                'submit' => [
                    'title' => $this->l('Save'),
                ],
            ],
        ];
    }

    /**
     * Set values for the inputs.
     */
    protected function getConfigFormValues()
    {
        return [
            'EASYTIS_KLOREL_QUOTATION_ID_CATEGORY' => Configuration::get('EASYTIS_KLOREL_QUOTATION_ID_CATEGORY', null),
        ];
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
    public function hookDisplayBackOfficeHeader()
    {
        if (Tools::getValue('controller') == 'AdminQuotationsPro') {
            $this->context->controller->addJS($this->_path . 'views/js/add_various_product.js');
            $this->context->controller->addCSS($this->_path . 'views/css/back.css');
        }


    }

    // récupération de variables -> utilisées dans le pdf pdf_quotation_ [lang]
    public function hookActionDispatcher()
    {
        if (Tools::getValue('controller') == 'AdminQuotationsPro' && Tools::getValue('id_roja45_quotation')) {

            if (isset($_SESSION['discountShipping'])){
                $discountShipping = $_SESSION['discountShipping'];
            dump($discountShipping);
            die('yeaaah');
            }


            $invoiceLogo = Configuration::get('PS_LOGO_INVOICE');

            $id_quotation = Tools::getValue('id_roja45_quotation');
            if ($invoiceLogo) {
                $this->context->smarty->assign('invoiceLogo', $invoiceLogo);
            }

            $query = 'SELECT note FROM ' . _DB_PREFIX_ . 'roja45_quotationspro_note WHERE id_roja45_quotation = ' . $id_quotation;
            $result = Db::getInstance()->executeS($query);

            if (!empty($result)) {
                $noteValue = nl2br($result[0]['note']);
                $this->context->smarty->assign('firstNote', $noteValue);
            }

            // formatage de la date de création d un devis (-> pdf devis)

            $query_creation_date = 'SELECT date_add FROM ' . _DB_PREFIX_ . 'roja45_quotationspro WHERE id_roja45_quotation = ' . $id_quotation;
            $result_date = Db::getInstance()->executeS($query_creation_date);

            if (!empty($result_date)) {
                $dateString = $result_date[0]['date_add'];

                $dateTime = new DateTime($dateString);

                $formattedDate = $dateTime->format('d/m/Y');

                $this->context->smarty->assign('formattedDate', $formattedDate);
            }

            $quotation = new RojaQuotation($id_quotation);

            $adressInvoice = new Address($quotation->id_address_invoice);
            $companyInvoice = $adressInvoice->company;
            $firstnameInvoice = $adressInvoice->firstname;
            $lastnameInvoice = $adressInvoice->lastname;

            if ($companyInvoice) {
                $this->context->smarty->assign('companyInvoice', $companyInvoice);
            }
            if ($firstnameInvoice) {
                $this->context->smarty->assign('firstnameInvoice', $firstnameInvoice);
            }
            if ($lastnameInvoice) {
                $this->context->smarty->assign('lastnameInvoice', $lastnameInvoice);
            }

            $adressDelivery = new Address($quotation->id_address_delivery);
            $companyDelivery = $adressDelivery->company;
            $firstnameDelivery = $adressDelivery->firstname;
            $lastnameDelivery = $adressDelivery->lastname;

            if ($companyDelivery) {
                $this->context->smarty->assign('companyDelivery', $companyDelivery);
            }
            if ($firstnameDelivery) {
                $this->context->smarty->assign('firstnameDelivery', $firstnameDelivery);
            }
            if ($lastnameDelivery) {
                $this->context->smarty->assign('lastnameDelivery', $lastnameDelivery);
            }

            /*            dump($adressDelivery);
                        die(',jh');*/


            // variables total produits HT / HT 5.5% / HT 20% / TTC / montant remise

            $quotationProductList = $quotation->getQuotationProductList();

            /*            dump($quotationProductList);*/

            $totalProductTax5_5 = 0;
            $totalProductTax20 = 0;
            $totalProductHT = 0;
            $totalProductTTC = 0;

            foreach ($quotationProductList as $product) {
                $totalProductHT += $product['unit_price_tax_excl'] * $product['qty'];
                $totalProductTTC += $product['unit_price_tax_incl'] * $product['qty'];

                $taxRate = floatval($product['tax_rate']);
                if ($taxRate === 5.5) {
                    $totalProductTax5_5 += $product['unit_price_tax_excl'];
                } elseif ($taxRate === 20.0) {
                    $totalProductTax20 += $product['unit_price_tax_excl'];
                }

            }

            $totalProductTax5_5Formated = Tools::displayPrice($totalProductTax5_5);
            $totalProductTax20Formated = Tools::displayPrice($totalProductTax20);
            $totalProductHTFormated = Tools::displayPrice($totalProductHT);
            $totalProductTTCFormated = Tools::displayPrice($totalProductTTC);

            if ($totalProductTax5_5 != 0) {
                $this->context->smarty->assign('totalProductTax5_5Formated', $totalProductTax5_5Formated);
            }
            if ($totalProductTax20 != 0) {
                $this->context->smarty->assign('totalProductTax20Formated', $totalProductTax20Formated);
            }

            if ($totalProductHTFormated) {
                $this->context->smarty->assign('totalProductHTFormated', $totalProductHTFormated);
            }
            if ($totalProductTTCFormated) {
                $this->context->smarty->assign('totalProductTTCFormated', $totalProductTTCFormated);
            }


            // variables total expedition HT et TTC

            $quotationShippingHT = $quotation->getQuotationTotals(false);
            if ($quotationShippingHT['quotation_total_shipping']) {
                $totalShippingHT = $quotationShippingHT['quotation_total_shipping'];
                $totalShippingHTFormated = Tools::displayPrice($totalShippingHT);

                $this->context->smarty->assign('totalShippingHTFormated', $totalShippingHTFormated);
            }

            if ($quotationShippingHT['quotation_total_additional_shipping']) {
                $totalShippingHT = $totalShippingHT + $quotationShippingHT['quotation_total_additional_shipping'];
                $totalShippingHTFormated = Tools::displayPrice($totalShippingHT);

                $this->context->smarty->assign('totalShippingHTFormated', $totalShippingHTFormated);
            }

            $quotationShippingTTC = $quotation->getQuotationTotals();
            if ($quotationShippingTTC['quotation_total_shipping']) {
                $totalShippingTTC = $quotationShippingHT['quotation_total_shipping'];
                $totalShippingTTCFormated = Tools::displayPrice($totalShippingTTC);

                $this->context->smarty->assign('totalShippingTTCFormated', $totalShippingTTCFormated);
            }

            if ($quotationShippingTTC['quotation_total_additional_shipping']) {
                $totalShippingTTC = $totalShippingTTC + $quotationShippingTTC['quotation_total_additional_shipping'];
                $totalShippingTTCFormated = Tools::displayPrice($totalShippingTTC);

                $this->context->smarty->assign('totalShippingTTCFormated', $totalShippingTTCFormated);
            }

            // variable ecotaxe

            $ecotaxeTTC = $quotationShippingTTC['quotation_total_ecotax'];
            $ecotaxeTTCFormated = Tools::displayPrice($ecotaxeTTC);

            if ($ecotaxeTTC) {
                $this->context->smarty->assign('ecotaxeTTC', $ecotaxeTTCFormated);
            }

            // variables taxe 5.5% et taxe 20%
            $totalTax20 = 0;
            $totalTax5_5 = 0;

            foreach ($quotationProductList as $product) {
                $unitPriceTaxExcl = floatval($product['unit_price_tax_excl']) * $product['qty'];
                $taxRate = floatval($product['tax_rate']);

                $taxAmount = $unitPriceTaxExcl * ($taxRate / 100);

                if ($taxRate === 20.0) {
                    $totalTax20 += $taxAmount;
                } elseif ($taxRate === 5.5) {
                    $totalTax5_5 += $taxAmount;
                }
            }

            $formattedTotalTax5_5 = Tools::displayPrice($totalTax5_5);
            $formattedTotalTax20 = Tools::displayPrice($totalTax20);

            if ($totalTax5_5 != 0) {
                $this->context->smarty->assign('totalTax5_5', $totalTax5_5);
                $this->context->smarty->assign('formattedTotalTax5_5', $formattedTotalTax5_5);
            }

            if ($totalTax20 != 0) {
                $this->context->smarty->assign('formattedTotalTax20', $formattedTotalTax20);
            }

        }
    }

    /**
     * Add the CSS & JavaScript files you want to be added on the FO.
     */

    public function hookHeader()
    {
        $this->context->controller->addJS($this->_path . '/views/js/front.js');
        $this->context->controller->addCSS($this->_path . '/views/css/front.css');
    }

}
