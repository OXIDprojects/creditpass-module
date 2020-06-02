[{if $creditPassEmail}]
    [{oxmultilang ident="OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER" args=$shop->oxshops__oxname->value}]
[{/if}]
[{$smarty.block.parent}]