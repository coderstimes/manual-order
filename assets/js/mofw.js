; (function ($) {
    $(document).ready(function () {

        $("#mofw_genpw").on('click', function () {
            $.post(mofw.ajax_url, { 'action': 'mofw_genpw', 'nonce': mofw.nonce }, function (data) {
                $("#password").val(data);
            });
        });
        $("#coupon").on('click', function () {

            if ($(this).is(':checked')) {
                $("#discount-label").html(mofw.dc);
                $("#discount").attr("placeholder", mofw.cc);
            } else {
                $("#discount-label").html(mofw.dt);
                $("#discount").attr("placeholder", mofw.dt);
            }
        });

        $("#email").on('blur', function () {
            if( $(this).val() == '' ){ return; }

            $("#first_name").val('');
            $("#last_name").val('');
            let email = $(this).val();
            $.post(mofw.ajax_url, { 'action': 'mofw_fetch_user', 'email': email, 'nonce': mofw.nonce }, function (data) {
                if ($("#first_name").val() == '') {
                    $("#first_name").val(data.fn);
                }
                if ($("#last_name").val() == '') {
                    $("#last_name").val(data.ln);
                }
                $("#phone").val(data.pn);
                $("#customer_id").val(data.id);

                if (!data.error) {
                    $("#first_name").attr('readonly', 'readonly');
                    $("#last_name").attr('readonly', 'readonly');
                    $("#password_container").hide();
                } else {
                    $("#password_container").show();
                    $("#first_name").removeAttr('readonly')
                    $("#last_name").removeAttr('readonly');
                }

            }, "json");
        });


        $(".select_product").select2({
            templateResult: formatState,
            templateSelection: formatState
        });

        function formatState (opt) {
            var optimage = $(opt.element).attr('data-thumbnail'); 
            
            if(!optimage ){
                return opt.text;               
            } else if(opt.selected) {
                return $( '<span style="display:flex;align-items:center;"><img src="' + optimage + '" height="30px" width="30px" style="margin-right:10px;" /> ' + opt.text + '</span>');
            }else {
                return $( '<span style="display:flex;align-items:center;"><img src="' + optimage + '" height="50px" width="50px" style="margin-right:10px;" /> ' + opt.text + '</span>');
            }
        }


        if ($('#mofw-edit-button').length > 0) {
            tb_show( $('.mofw-form-title h4').text(), "#TB_inline?inlineId=mofw-modal&width=700");
        }

        $( 'body' ).on( 'thickbox:removed', function() {
            var url = window.location.href;
            var a = url.indexOf("?");
            var b = url.substring(a);
            var c = url.replace(b,"?page=wc-manual-order");
            window.history.pushState({}, document.title, c );
        });


    });
})(jQuery);