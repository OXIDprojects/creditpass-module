<p style="font-family: Arial, Helvetica, sans-serif; font-size: 12px; margin: 0; padding: 10px 0;">
    [{if $payment->oxuserpayments__oxpaymentsid->value == "oxempty"}]
    [{oxcontent ident="oecreditpassordernpemail"}]
    [{else}]
    [{oxmultilang ident="OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER" args=$shop->oxshops__oxname->value}]
    [{/if}]
    <br>
    <p>
        [{oxmultilang ident="ORDER_NUMBER" }] <b>[{ $order->oxorder__oxordernr->value }]</b>
    </p>
    <br>
</p>