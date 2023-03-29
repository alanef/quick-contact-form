function retryValidation(link) {
    var main = jQuery(link).closest('.qcf-main');
    main.find('.qcf-state').hide();
    main.find('.qcf-form-wrapper').fadeIn('fast');
}



jQuery(document).ready(function () {

    $ = jQuery;



    /*
        Add jQuery ajax for form validation.
    */
    $('.qcf-form').on("submit", function (x) {
        x.preventDefault();

        // Contain parent form in a variable
        var f = $(this);

        // Intercept request and handle with AJAX
        console.log($(this).serialize());

       // var fd = $(this).serialize();
        var fp = f.closest('.qcf-main');


        var executes = 0;
        $('html, body').animate({
            scrollTop: Math.max(fp.offset().top - 100, 0),
        }, 200, null, function () {

            executes++;

            if (executes >= 2) return false;

            fp.find('.qcf-state').hide();
            fp.find('.qcf-ajax-loading').show();

            var form_data = new FormData(x.target);
            //loop through all the input fields and get the file data incrementing the names
            var i = 1;
            fp.find('.qcf_filename_input').each(function () {
                var file_data = $(this).prop('files')[0];
                form_data.append('filename' + i, file_data);
                i++;
            });
            form_data.append('action', 'qcf_validate_form');
            var url=window.location.href;
            form_data.append('url', url);

            $.ajax({
                url: ajaxurl,
                type: 'post',
                contentType: false,
                processData: false,
                data: form_data,
            }).done(qcf_ajax_success);

            function qcf_ajax_success(e) {
                const data = JSON.parse(e)
                if (data.success !== undefined) {

                    /* Strip attachment error from errors object */
                    var d = [];
                    for (var x = 0; x < data.errors.length; x++) {
                        if (data.errors[x].name != "attach") {
                            d.push(data.errors[x]);
                        }
                    }
                    data.errors = d;

                    /*
                        Quick validate file fields
                    */
                    var has_file_error = false;

                    if (typeof qfc_file_info !== 'undefined') {

                        /* Define Variables */
                        has_file_error = false;
                        var file_error = {'name': 'attach', 'error': qfc_file_info.error},
                            files = f.find('[type=file]');

                        if (qfc_file_info.required) {

                            /* Due to back end code -- confirm that the first file element contains a file! */
                            if (!files[0].files.length) {
                                has_file_error = true;
                                file_error.error = qfc_file_info.error_required;
                            }
                        }


                        if (!has_file_error) {

                            // so far so good, lets continue checking the rest of the factors

                            // Check file size & file type
                            x = 0; // was defined earlier can reuse
                            var lf, y = 0, match;
                            for (x; x < files.length; x++) {
                                if (files[x].files.length) {
                                    lf = files[x].files[0];

                                    // Check Size
                                    if (lf.size > qfc_file_info.max_size) {
                                        has_file_error = true;
                                        file_error.error = qfc_file_info.error_size;
                                    }

                                    // Check file type
                                    if (qfc_file_info.types.length > 0) {
                                        // loop through valid file types
                                        match = false, REGEX = new RegExp;
                                        for (y = 0; y < qfc_file_info.types.length; y++) {
                                            REGEX = new RegExp(qfc_file_info.types[y], "i");
                                            if (lf.name.match(REGEX)) match = true;
                                        }
                                        if (!match) {
                                            // bad file type!
                                            has_file_error = true;
                                            file_error.error = qfc_file_info.error_type;
                                        }
                                    }
                                }
                            }
                        }
                    }

                    if (has_file_error) {

                        data.errors.push(file_error)
                    }
                    if (data.errors.length) { // errors found

                        /* Remove all prior errors */
                        f.find('.qcf-input-error').remove();
                        f.find('.error').removeClass('error');

                        // Display error header
                        fp.find('.qcf-header').addClass('error').html(data.display);
                        fp.find('.qcf-blurb').addClass('error').html(data.blurb);

                        for (i = 0; i < data.errors.length; i++) {

                            error = data.errors[i];
                            if (error.name == 'attach') {
                                element = f.find('[name=' + error.name + ']').prepend("<p class='qcf-input-error'><span>" + error.error + "</span></p>");
                                ;
                            } else {
                                element = f.find('[name=' + error.name + ']');
                                element.addClass('error');
                                if (error.name == 'qcfname12') {
                                    element.parent().prepend(error.error);
                                } else {
                                    element.before(error.error);
                                }
                            }
                        }

                        fp.find('.qcf-state').hide();
                        fp.find('.qcf-form-wrapper').fadeIn('fast');
                    } else {
                        fp.find('.qcf-state').html(data.display);


                    }
                } else {
                    // assume error so just show the form again.
                    fp.find('.qcf-state').hide();
                    $('.qcf-ajax-error').fadeIn('fast');
                }

                return false;
            };

        });
        return false;
    });

    $('.qcfdate').datepicker({dateFormat: 'dd M yy'});

    $('body').on('click', '.qcf-retry', function () {
        retryValidation(this);
    });

    $('body').on('click', '.qcf-confirm', function () {
        return window.confirm( $(this).data('confirm') );
    });



    $('body').on('focus', '.has-default', function () {
        if ($(this).val() == $(this).data('default')) {
            $(this).val(" ");
        }
        $(this).blur( function() {
            if ( $.trim($(this).val()).length == 0) {
                $(this).val($(this).data('default'));
            }
        });
    });

});