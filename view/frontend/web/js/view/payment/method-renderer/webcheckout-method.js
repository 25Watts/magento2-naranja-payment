define(
    [
        'Magento_Checkout/js/view/payment/default'
    ],
    function (Component) {
        'use strict';

        let configPayment = window.checkoutConfig.payment.naranja_webcheckout;

        return Component.extend({
            defaults: {
                template: 'Watts25_Naranja/payment/webcheckout'
            },
            redirectAfterPlaceOrder: false,

            afterPlaceOrder: function () {
                window.location = this.getActionUrl();
            },

            getActionUrl: function () {
                if (configPayment !== undefined) {
                    return configPayment['actionUrl'];
                }
                return '';
            },

            /**
             * @returns {*}
             */
            getLogoUrl: function () {
                if (configPayment !== undefined) {
                    return configPayment['logoUrl'];
                }
                return '';
            },
           
        });
    }
);
