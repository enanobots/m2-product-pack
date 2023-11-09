/**
 * Copyright © Q-Solutions Studio: eCommerce Nanobots. All rights reserved.
 *
 * @category    Nanobots
 * @package     Nanobots_ProductPack
 * @author      Jakub Winkler <jwinkler@qsolutionsstudio.com>
 * @author      Wojtek Wnuk <wojtek@qsolutionsstudio.com>
 * @author      Łukasz Owczarczuk <lukasz@qsolutionsstudio.com>
 */
define([
    'jquery',
    'underscore',
    'mage/template',
    'mage/translate',
    'priceUtils',
    'priceBox',
    'jquery-ui-modules/widget',
    'jquery/jquery.parsequery',
    'fotoramaVideoEvents'
], function ($, _, mageTemplate, $t, priceUtils) {
    'use strict';

    $.widget('nanobots.productpack', {
        options: {
            packPrices: {},
            productId: null,
            display_calculated_price: false,
        },

        /**
         * Creates widget
         * @private
         */
        _create: function () {
            this._initializeRadioBtnClick();
        },

        _initializeRadioBtnClick: function () {
            $('input[name="pack_option_id"]').on('change', function (e) {
                let checkedOption = $('input[name="pack_option_id"]:checked'),
                    checkedOptionQty = checkedOption.data('pack_size'),
                    length = checkedOption.data('length'),
                    height = checkedOption.data('height'),
                    width = checkedOption.data('width'),
                    discountType = checkedOption.data('discount_type'),
                    discountValue = checkedOption.data('discount_value'),
                    productId = this.options.productId

                let checkedOptionVal = checkedOption.val(),
                    checkedPackageName = checkedOption.data('package_name'),
                    checkedOptionExtraWeight = checkedOption.data('extra_weight'),
                    packOptionHashData = {
                        value: checkedOptionVal,
                        packageName: checkedPackageName,
                        discount_type: discountType,
                        discount_value: discountValue,
                        length: length,
                        height: height,
                        width: width,
                        extra_weight: checkedOptionExtraWeight,
                        pack_size: checkedOptionQty
                    };

                $('input[name="pack_option[value]"]').val(checkedOptionVal);
                $('input[name="pack_option[title]"]').val(checkedPackageName);
                $('input[name="pack_option[discount_type]"]').val(discountType);
                $('input[name="pack_option[discount_value]"]').val(discountValue);
                $('input[name="pack_option[extra_weight]"]').val(checkedOptionExtraWeight);
                $('input[name="pack_option[pack_size]"]').val(checkedOptionQty);
                $('input[name="pack_option_hash"]').val(btoa(JSON.stringify(packOptionHashData)));

                let data = this.options.packPrices[checkedOptionVal];
                if (data.hasOwnProperty('price')) {
                    var priceDisplay = data.price;
                    if( this.options.display_calculated_price ) {
                        if (data.hasOwnProperty('qty_price')) {
                            priceDisplay = data.qty_price;
                        }
                    }
                    $('.price-wrapper  > .price').text(priceDisplay);
                }
                if (data.hasOwnProperty('base_price')) {
                    $('#price-excluding-tax-product-price-'+productId + ' > .price').text(data.base_price);
                }
            }.bind(this));
        }
    });

    return $.nanobots.productpack;
});
