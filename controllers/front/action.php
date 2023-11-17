<?php

class Easytis_klorel_quotationActionModuleFrontController extends ModuleFrontController
{
    public function init()
    {
        parent::init();

        switch (Tools::getValue('action')) {
            case 'createProduct':
                $this->createProduct();
                die();
                break;
            case 'getTaxes':
                $this->getTaxes();
                die();
                break;
        }
    }

    private function createProduct()
    {
        $productName = Tools::getValue('productName');
        $productReference = Tools::getValue('productReference');
        $productWholeprice = Tools::getValue('productWholeprice');
        $productUnitprice = Tools::getValue('productUnitprice');
        $productTax = Tools::getValue('productTax');

        $selectedCategoryId = (int)Configuration::get('EASYTIS_KLOREL_QUOTATION_ID_CATEGORY');

        if (!empty($productName) && !empty($productReference) && !empty($productWholeprice)) {
            $product = new Product();

            $product->id_category_default = $selectedCategoryId;
            $product->id_supplier = 0;
            $product->reference = $productReference;
            $product->price = $productUnitprice;
            $product->wholesale_price = $productWholeprice;
            $product->active = false;
            $product->id_tax_rules_group = (int)$productTax;

            $product->name[Configuration::get('PS_LANG_DEFAULT')] = $productName;

            if ($product->add()) {
                echo 'Le produit a été créé avec succès.';
            } else {
                echo 'Une erreur s\'est produite lors de la création du produit.';
            }
        } else {
            echo 'Nom de produit invalide.';
        }
    }

    private function getTaxes()
    {
        $taxes = Tax::getTaxes(Context::getContext()->language->id);

        $formattedTaxes = array();
        foreach ($taxes as $tax) {
            $formattedTaxes[] = array(
                'id_tax' => $tax['id_tax'],
                'name' => $tax['name'],
            );
        }

        header('Content-Type: application/json');
        echo json_encode($formattedTaxes);
    }
}