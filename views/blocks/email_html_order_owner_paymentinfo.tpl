[{if $payment->oxuserpayments__oxpaymentsid->value != "oxempty"}]
    <h3 class="underline">[{oxmultilang ident="PAYMENT_INFORMATION"}]</h3>
    <p>
        <b>[{oxmultilang ident="PAYMENT_METHOD" suffix="COLON"}] [{$payment->oxpayments__oxdesc->value}]
            [{if $basket->getPaymentCosts()}]([{$basket->getFPaymentCosts()}] [{$currency->sign}])[{/if}]
        </b>
        ([{oxmultilang ident="OECREDITPASS_SETTINGS_MANUAL_EMAIL_MESSAGE"}])
    </p>
    <br>
[{/if}]