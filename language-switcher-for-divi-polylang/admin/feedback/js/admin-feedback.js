(function($){
    $(document).ready(function(){
        let plugin_slug = 'language-switcher-for-divi-polylang';
		let text_domain = 'LSDP';
        $target = $('#the-list').find('[data-slug="'+plugin_slug+'"] span.deactivate a');

        var plugin_deactivate_link = $target.attr('href');
        $($target).on('click', function(event){
            event.preventDefault();
            $('#wpwrap').css('opacity','0.4');

            $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").animate({
                opacity:1
            },200,function(){
                $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").removeClass('hide-feedback-popup');
                $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").find('#cool-plugin-submitNdeactivate').addClass(text_domain);
                $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").find('#cool-plugin-skipNdeactivate').addClass(text_domain);
            });
        });

        $("." + plugin_slug + " .cool-plugins-deactivate-feedback-dialog-input").on('click',function(){
            if($("." + plugin_slug + " #cool-plugins-GDPR-data-notice").is(":checked") === true && $("." + plugin_slug + " .cool-plugins-deactivate-feedback-dialog-input").is(':checked') === true){
                $("." + plugin_slug + " #cool-plugin-submitNdeactivate").removeClass('button-deactivate');
            }
            else{
                $("." + plugin_slug + " #cool-plugin-submitNdeactivate").addClass('button-deactivate');
            }

        });

        $("." + plugin_slug + " #cool-plugins-GDPR-data-notice").on('click', function(){

            if($("." + plugin_slug + " #cool-plugins-GDPR-data-notice").is(":checked") === true && $("." + plugin_slug + " .cool-plugins-deactivate-feedback-dialog-input").is(':checked') === true){
                $("." + plugin_slug + " #cool-plugin-submitNdeactivate").removeClass('button-deactivate');
            }
            else{
                $("." + plugin_slug + " #cool-plugin-submitNdeactivate").addClass('button-deactivate');
            }
        })

        $('#wpwrap').on('click', function(ev){
            if( $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper.hide-feedback-popup").length==0 ){
                ev.preventDefault();
                $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").animate({
                    opacity:0
                },200,function(){
                    $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").addClass("hide-feedback-popup");
                    $("." + plugin_slug + "#cool-plugins-deactivate-feedback-dialog-wrapper").find('#cool-plugin-submitNdeactivate').removeClass(text_domain);
                    $('#wpwrap').css('opacity','1');
                })

            }
        })

        $(document).on('click', '.' + plugin_slug + ' #cool-plugin-submitNdeactivate.'+text_domain+':not(".button-deactivate")', function(event){
            let nonce = $("." + plugin_slug + " #_wpnonce").val();
            let reason = $("." + plugin_slug + " .cool-plugins-deactivate-feedback-dialog-input:checked").val();
            let message = '';
            if( $("." + plugin_slug + " textarea[name='reason_"+reason+"']").length>0 ){
                if( $("." + plugin_slug + " textarea[name='reason_"+reason+"']").val() == '' ){
                    alert('Please provide some extra information!');
                    return;
                }else{
                    message=$("." + plugin_slug + " textarea[name='reason_"+reason+"']").val();
                }
            }

            $.ajax({
                url:ajaxurl,
                method:'POST',
                data:{
                    'action':text_domain+'_submit_deactivation_response',
                    '_wpnonce':nonce,
                    'reason':reason,
                    'message':message,
                },
                beforeSend:function(data){
                    $("." + plugin_slug + " #cool-plugin-submitNdeactivate").text('Deactivating...');
                    $("." + plugin_slug + " #cool-plugin-submitNdeactivate").attr('id','deactivating-plugin');
                    $("." + plugin_slug + " #cool-plugins-loader-wrapper").show();
                    $("." + plugin_slug + " #cool-plugin-skipNdeactivate").remove();
                },
                success:function(res){
                    $("." + plugin_slug + " #cool-plugins-loader-wrapper").hide();
                    window.location = plugin_deactivate_link;
                    $("." + plugin_slug + " #deactivating-plugin").text('Deactivated');
                }
            })

        });

        $(document).on('click', '.' + plugin_slug + ' #cool-plugin-skipNdeactivate.'+text_domain+':not(".button-deactivate")', function(){
            $("." + plugin_slug + " #cool-plugin-submitNdeactivate").remove();
            $("." + plugin_slug + " #cool-plugin-skipNdeactivate").addClass('button-deactivate');
            $("." + plugin_slug + " #cool-plugin-skipNdeactivate").attr('id','deactivating-plugin');
            window.location = plugin_deactivate_link;
        });

    });
})(jQuery);
