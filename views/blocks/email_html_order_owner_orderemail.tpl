[{if $creditPassEmail}]
    <p>[{oxmultilang ident="OECREDITPASS_MANUAL_REVIEW_EMAIL_ORDER" args=$shop->oxshops__oxname->value}]</p>
[{/if}]
[{$smarty.block.parent}]