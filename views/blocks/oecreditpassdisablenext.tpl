[{$smarty.block.parent}]
[{oxscript add='
//always make sure this script is included AFTER oxinputvalidator.js
$("form.payment").submit(function (e){
    if (e.result != false) {
        $("#paymentNextStepBottom").attr("disabled", true);
    }
});
'}]