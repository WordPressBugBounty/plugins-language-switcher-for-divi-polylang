(function($) {
    "use strict";

    $(document).ready(function() {
        // Elementor Review Notice Start
        jQuery(document).on('click','#lsep_elementor_review_dismiss',(event)=>{
            jQuery(".lsep_elementor_review_notice").hide();
            const btn=jQuery(event.target);
            const nonce=btn.data('nonce');
            const url=btn.data('url');

            jQuery.ajax({
                type: 'POST',
                // eslint-disable-next-line no-undef
                url: url, // Set this using wp_localize_script
                data: {
                    action: 'lsep_elementor_review_notice',
                    lsep_notice_dismiss: true,
                    nonce: nonce
                },
                success: (response) => {
                    btn.closest('.elementor-control').remove();
                },
                error: (xhr, status, error) => {
                    console.log(error);
                }
            });
        });
        // Elementor Review Notice End
    });
})(jQuery);