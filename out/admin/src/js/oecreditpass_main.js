var oeCreditPassMain = (function ($) {

    /**
     * local variables
     */
    var manualWorkflow, manualEmail, mainSave, manualEmailRestrictFlag, setCacheInput;

    /**
     * local variable initiation
     */
    var obj = {
        manualWorkflow: '#oecreditpass_selectManualWorkflow',
        manualEmail: '#oecreditpass_inputManualEmail',
        mainSave: '#oecreditpass_buttonMainSave',
        setCacheInput: '#oecreditpass_checkCacheTimeout',
        manualEmailRestrictFlag: false,
        classRedBorder: 'oecreditpass_redBorder',
        maxCachingDays: 60,

        /**
         * Initiate
         */
        init: function () {
            manualWorkflow = $(oeCreditPassMain.manualWorkflow);
            manualEmail = $(oeCreditPassMain.manualEmail);
            mainSave = $(oeCreditPassMain.mainSave);
            setCacheInput = $(oeCreditPassMain.setCacheInput);
            oeCreditPassMain.initActions();
        },

        /**
         * Initiates actions
         */
        initActions: function () {
            manualWorkflow.change(oeCreditPassMain.manualWorkflowChangeActionHandler);
            mainSave.click(oeCreditPassMain.mainSaveHandler);
            manualEmail.change(oeCreditPassMain.manualEmailChangeActionHandler);
            setCacheInput.change(oeCreditPassMain.setCacheInputChangeActionHandler);
        },

        /**
         * Manual workflow select change action handling
         */
        manualWorkflowChangeActionHandler: function () {
            if (manualWorkflow[0].value == '2') {
                if (manualEmail[0].value == '') {
                    oeCreditPassMain.restrictManualEmailInput();
                }
            } else {
                oeCreditPassMain.allowManualEmailInput();
            }
        },

        /**
         * "manual" email change handler
         */
        manualEmailChangeActionHandler: function () {
            if (manualEmail[0].value == '' && manualWorkflow[0].value == '2') {
                oeCreditPassMain.restrictManualEmailInput();
            } else {
                oeCreditPassMain.allowManualEmailInput();
            }
        },

        /**
         * Set restrict flag on, then highlight "manual" email input
         */
        restrictManualEmailInput: function () {
            //console.log("restrict");
            manualEmailRestrictFlag = true;
            oeCreditPassMain.toggleManualEmailHighlight(true);
        },

        /**
         * Set restrict flag off, then remove highlight from "manual" email input
         */
        allowManualEmailInput: function () {
            //console.log("allow");
            manualEmailRestrictFlag = false;
            oeCreditPassMain.toggleManualEmailHighlight(false);
        },

        /**
         * Save handler
         *
         * @returns {boolean}
         */
        mainSaveHandler: function () {
            if (true == manualEmailRestrictFlag) {
                //console.log("save disabled");
                return false;
            }
            //console.log("save enabled");
            return true;
        },

        /**
         * Highlight toggle
         */
        toggleManualEmailHighlight: function (blOn) {
            //console.log("highlight " + blOn);
            if (true == blOn) {
                manualEmail.addClass(oeCreditPassMain.classRedBorder);
            } else {
                manualEmail.removeClass(oeCreditPassMain.classRedBorder);
            }
        },

        /**
         * Sets the maximum caching days
         *
         * @param int maxCachingDays
         */
        setMaxCachingDays: function (maxCachingDays) {
            oeCreditPassMain.maxCachingDays = maxCachingDays;
        },

        /**
         * Check cache input change handler
         */
        setCacheInputChangeActionHandler: function (val) {


            var newValue = $(this).val();
            var maxValue = oeCreditPassMain.maxCachingDays;

            if (!/^[0-9]*[.]?[0-9]*$/.test(newValue) || newValue < 0) {
                $(this).addClass(oeCreditPassMain.classRedBorder);
                //$(this).effect("shake", {}, 50);
                $(this).val(0);
            } else if (newValue > maxValue) {
                $(this).addClass(oeCreditPassMain.classRedBorder);
                //$(this).effect("shake", {}, 50);
                $(this).val(maxValue);
            } else {
                $(this).removeClass(oeCreditPassMain.classRedBorder);
            }

            setTimeout(function () {
                setCacheInput.removeClass(oeCreditPassMain.classRedBorder);
            }, 600);

        }
    }

    return obj;

})(jQuery);
$.noConflict();
jQuery(document).ready(function () {
    // Main init
    oeCreditPassMain.init();

    // Other
    // User groups selection widget initiation
    jQuery(".chosen-select").chosen({no_results_text: "-", disable_search_threshold: 15, width: "280px"});
    jQuery(".chosen-select-multiple").chosen({no_results_text: "-", disable_search_threshold: 15, width: "500px"});
});