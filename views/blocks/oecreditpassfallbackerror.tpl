[{$smarty.block.parent}]
[{if $iPayError == 'oecreditpassunauthorized_error'}]
    <div class="status error">[{ $oView->getPaymentErrorText() }]</div>
    [{/if}]