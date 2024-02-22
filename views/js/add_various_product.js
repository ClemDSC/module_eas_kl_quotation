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
*  @author    PrestaShop SA <contact@prestashop.com>
*  @copyright 2007-2023 PrestaShop SA
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*
* Don't forget to prefix your containers with your own identifier
* to avoid any conflicts with others containers.
*/
$(document).ready(function() {

    const $modalDiv = $('#quotationspro_addproduct_modal');
    const $modalHeader = $modalDiv.find('.modal-header');

    const $newDiv = $('<div id="quotationspro_add_various_product"></div>');
    const $title = $('<h4 class="modal-title">Create "various" product</h4>')
    const $line = $('<div class="col-lg-12 search">' +
        '<div class="form-horizontal">' +
        '   <div class="col-lg-3">\n' +
        '      <div class="form-group">\n' +
        '         <label class="col-lg-12">Name</label>\n' +
        '         <div class="col-lg-12">\n' +
        '            <input type="text" autocomplete="false" name="various_product_name">\n' +
        '         </div>\n' +
        '       </div>\n' +
        '   </div>' +
        '   <div class="col-lg-3">\n' +
        '      <div class="form-group">\n' +
        '         <label class="col-lg-12">Reference</label>\n' +
        '         <div class="col-lg-12">\n' +
        '            <input type="text" autocomplete="false" name="various_product_reference">\n' +
        '         </div>\n' +
        '       </div>\n' +
        '   </div>' +
        '   <div class="col-lg-3">\n' +
        '      <div class="form-group">\n' +
        '         <label class="col-lg-12">Wholesale price</label>\n' +
        '         <div class="col-lg-12">\n' +
        '            <input type="text" autocomplete="false" name="various_product_wholeprice">\n' +
        '         </div>\n' +
        '       </div>\n' +
        '   </div>' +
        '   <div class="col-lg-3">\n' +
        '      <div class="form-group">\n' +
        '         <label class="col-lg-12">Unit price</label>\n' +
        '         <div class="col-lg-12">\n' +
        '            <input type="text" autocomplete="false" name="various_product_unitprice">\n' +
        '         </div>\n' +
        '       </div>\n' +
        '   </div>' +
        '   <div class="col-lg-3">\n' +
        '      <div class="form-group">\n' +
        '         <label class="col-lg-12">Taxes</label>\n' +
        '         <div class="col-lg-12">\n' +
        '       <select name="various_product_tax"></select>' +
        '         </div>\n' +
        '       </div>\n' +
        '   </div>' +
        '   <div class="col-lg-1">\n' +
        '         <div class="form-group">\n' +
        '            <label class="col-lg-12">&nbsp;</label>\n' +
        '               <div class="col-lg-12">\n' +
        '                  <input type="button" class="btn btn-primary btn-add-various-products" value="Add">\n' +
        '               </div>\n' +
        '          </div>\n' +
        '       </div>' +
        '   </div>' +
        '   </div>' +
        '<div id="response-message"></div>')

    $newDiv.append($title, $line);
    $newDiv.insertBefore($modalHeader.find('h4'));

    $line.find('select[name="various_product_tax"]').append('<option value="">Taxes</option>');

    $.ajax({
        url: '/module/easytis_klorel_quotation/action?action=getTaxes',
        type: 'GET',
        success: function(taxes) {
            const $taxSelect = $line.find('select[name="various_product_tax"]');
            taxes.forEach(function(tax) {
                $taxSelect.append('<option value="' + tax.id_tax + '">' + tax.name + '</option>');
            });
            },
        error: function(xhr, status, error) {
                    console.error('Error : ', status, error);
        }
    });

    $(document).on('click', '.btn-add-various-products', function() {
        const variousName = $('input[name="various_product_name"]').val();
        const variousReference = $('input[name="various_product_reference"]').val();
        const variousWholeprice = $('input[name="various_product_wholeprice"]').val();
        const variousUnitprice = $('input[name="various_product_unitprice"]').val();
        const variousTax = $('select[name="various_product_tax"]').val();

        $.ajax({
            url: '/module/easytis_klorel_quotation/action',
            type: 'POST',
            data: {
                action: 'createProduct',
                productName: variousName,
                productReference: variousReference,
                productWholeprice: variousWholeprice,
                productUnitprice: variousUnitprice,
                productTax: variousTax,
            },
            beforeSend: function () {
                roja45quotationspro.toggleModal();
            },
            success: function(response) {
                const $responseMessage = $('#response-message');
                if (response == 'Le produit a été créé avec succès.') {
                    $responseMessage.html('<p class="text-success">' + response + '</p>');
                    roja45quotationspro.toggleModal();
                } else {
                    $responseMessage.html('<p class="text-danger">Erreur AJAX : ' + response + '</p>');
                    roja45quotationspro.toggleModal();
                }
            },
            error: function(xhr, status, error) {
                console.error('Error : ', status, error);
                roja45quotationspro.toggleModal();
            }
        });
    });
})
