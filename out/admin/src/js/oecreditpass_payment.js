var oeCreditPassPaymentSettings = (function ($) {
    var paymentSettings;

    var obj = {
        paymentSettings: '#oeCreditPassPaymentSettings',
        paymentSettingsError: 'oecreditpass_paymentsettings_error',
        paymentMethod: '.oeCreditPassPaymentSettingsPaymentMethod',
        paymentMethodOptions: {
            status: 'select.oeCreditPassPaymentSettingsStatus',
            fallback: 'select.oeCreditPassPaymentSettingsFallback',
            purchaseType: 'input.oeCreditPassPaymentSettingsPurchaseType',
            allowOnError: 'select.oeCreditPassPaymentSettingsAllowOnError'
        },

        /**
         * initiate
         */
        init: function () {
            paymentSettings = $(oeCreditPassPaymentSettings.paymentSettings);
            oeCreditPassPaymentSettings.toggleActivePaymentSettings();
            oeCreditPassPaymentSettings.initActions();
        },

        /**
         * initiates actions
         */
        initActions: function () {
            paymentSettings.delegate(oeCreditPassPaymentSettings.paymentMethodOptions.status, 'change', oeCreditPassPaymentSettings.paymentMethodStatusChangeActionHandler);
        },

        /**
         * toggle settings from database values on page load
         */
        toggleActivePaymentSettings: function () {
            var paymentMethods = paymentSettings.find(oeCreditPassPaymentSettings.paymentMethod);
            $.each(
                paymentMethods, function (i, oPaymentMethod) {
                    togglePaymentMethodActiveSettings(oPaymentMethod);
                    togglePaymentMethodFallbackByStatus(oPaymentMethod);
                    togglePaymentMethodPurchaseTypeByStatus(oPaymentMethod, true);
                }
            )
        },

        /**
         * payment method status option change in select
         */
        paymentMethodStatusChangeActionHandler: function () {
            var oPaymentMethod = getPaymentMethod($(this));
            togglePaymentMethodSettingsByStatus(oPaymentMethod);
        }
    }

    /**
     * get payment method element
     *
     * @private
     */
    function getPaymentMethod(oElement) {
        return $($(oElement).parents(oeCreditPassPaymentSettings.paymentMethod));
    }

    /**
     * get payment method status element
     *
     * @private
     */
    function getPaymentMethodStatus(oPaymentMethod) {
        return $(oPaymentMethod).find(oeCreditPassPaymentSettings.paymentMethodOptions.status);
    }

    /**
     * get payment method status value
     *
     * @private
     */
    function getPaymentMethodStatusValue(oPaymentMethod) {
        return getPaymentMethodStatus(oPaymentMethod).find(':selected').val();
    }

    /**
     * get payment method fallback element
     *
     * @private
     */
    function getPaymentMethodFallback(oPaymentMethod) {
        return $(oPaymentMethod).find(oeCreditPassPaymentSettings.paymentMethodOptions.fallback);
    }

    /**
     * get payment method purchase type element
     *
     * @private
     */
    function getPaymentMethodPurchaseType(oPaymentMethod) {
        return $(oPaymentMethod).find(oeCreditPassPaymentSettings.paymentMethodOptions.purchaseType);
    }

    /**
     * get payment method allow on error element
     *
     * @private
     */
    function getPaymentMethodAllowOnError(oPaymentMethod) {
        return $(oPaymentMethod).find(oeCreditPassPaymentSettings.paymentMethodOptions.allowOnError);
    }

    /**
     * toggle payment method all settings to database saved settings
     *
     * @private
     */
    function togglePaymentMethodActiveSettings(oPaymentMethod) {
        var oStatus = getPaymentMethodStatus(oPaymentMethod);
        var oFallback = getPaymentMethodFallback(oPaymentMethod);
        var oAllowOnError = getPaymentMethodAllowOnError(oPaymentMethod);
        oStatus.val(oStatus.data('active')).change();
        oFallback.val(oFallback.data('active')).change();
        oAllowOnError.val(oAllowOnError.data('active')).change();
    }

    /**
     * toggle payment method all settings by status
     *
     * @private
     */
    function togglePaymentMethodSettingsByStatus(oPaymentMethod) {
        togglePaymentMethodFallbackByStatus(oPaymentMethod);
        togglePaymentMethodPurchaseTypeByStatus(oPaymentMethod);
        togglePaymentMethodAllowOnErrorByStatus(oPaymentMethod);
    }

    /**
     * toggle payment method fallback setting by status
     *
     * @private
     */
    function togglePaymentMethodFallbackByStatus(oPaymentMethod) {
        var oFallback = getPaymentMethodFallback(oPaymentMethod);
        if (getPaymentMethodStatusValue(oPaymentMethod) == 1) {
            oFallback.attr('disabled', 'disabled');
            oFallback.val(0).change();
        } else {
            oFallback.removeAttr('disabled');
        }
    }

    /**
     * toggle payment method purchase type setting by status
     *
     * @private
     */
    function togglePaymentMethodPurchaseTypeByStatus(oPaymentMethod, showError) {
        var oPurchaseType = getPaymentMethodPurchaseType(oPaymentMethod);
        if (getPaymentMethodStatusValue(oPaymentMethod) == 1) {
            oPurchaseType.removeAttr('disabled');
            if (oPurchaseType.val() == '' && showError) {
                oPurchaseType.addClass(oeCreditPassPaymentSettings.paymentSettingsError);
            }
        } else {
            oPurchaseType.attr('disabled', 'disabled');
        }
    }

    /**
     * toggle payment method allow on error setting by status
     *
     * @private
     */
    function togglePaymentMethodAllowOnErrorByStatus(oPaymentMethod) {
        var oAllowOnError = getPaymentMethodAllowOnError(oPaymentMethod);
        if (getPaymentMethodStatusValue(oPaymentMethod) == 1) {
            oAllowOnError.val(0).change();
        } else {
            oAllowOnError.val(oAllowOnError.data('default')).change();
        }
    }

    return obj;
})(jQuery);

$(document).ready(
    function () {
        oeCreditPassPaymentSettings.init();
    }
);