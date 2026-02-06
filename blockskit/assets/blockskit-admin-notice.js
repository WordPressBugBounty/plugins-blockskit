jQuery( document ).ready( function ( $ ) {
//Blockskit Pro Admin Page Notice 
    $(document).on( 'click', '.blockskit-pro-remind-me-later', function() {
        $(document).find('.blockskit-go-pro-notice').slideUp();
         $.ajax({
            url: BLOCKSKIT_PRO_UPGRADE.ajaxurl,
            type: 'POST',
            data: {
                nonce: BLOCKSKIT_PRO_UPGRADE.nonce,
                action: 'remind_me_later_blockskit_pro',
            },
        });
    });

    $(document).on( 'click', '#blockskit-pro-dismiss', function() {
        $(document).find('.blockskit-go-pro-notice').slideUp();
         $.ajax({
            url: BLOCKSKIT_PRO_UPGRADE.ajaxurl,
            type: 'POST',
            data: {
                nonce: BLOCKSKIT_PRO_UPGRADE.dismiss_nonce,
                action: 'upgrade_blockskit_pro_notice_dismiss',
            },
        });
    });
} );