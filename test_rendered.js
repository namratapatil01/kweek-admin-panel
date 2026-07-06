
        $(document).ready(function() {
                        var admin_panel_color = "#000000";
            setCookie('admin_panel_color', admin_panel_color, 365);
            $('.login-register').css({ 'background-color': admin_panel_color });

            var firstSectionId = '11';
            var firstServiceType = 'ondemand-service';
            if (firstSectionId && firstServiceType) {
                setCookie('section_id', firstSectionId, 1);
                setCookie('service_type', firstServiceType, 1);
            }
        });

        function setCookie(cname, cvalue, exdays) {
            const d = new Date();
            d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
            let expires = "expires=" + d.toUTCString();
            document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
        }
        $(document).ready(function() {
            const icon = $('#togglePasswordIcon');
            icon.removeClass('mdi-eye').addClass('mdi-eye-off');
            $('.password-toggle-icon').on('click', function() {
                const passwordField = $('#password');


                if (passwordField.attr('type') === 'password') {
                    passwordField.attr('type', 'text');
                    icon.removeClass('mdi-eye-off').addClass('mdi-eye');
                } else {
                    passwordField.attr('type', 'password');
                    icon.removeClass('mdi-eye').addClass('mdi-eye-off');
                }
            });
        });
    