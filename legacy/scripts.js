function qcfclear(thisfield, defaulttext) {if (thisfield.value == defaulttext) {thisfield.value = "";}}
function qcfrecall(thisfield, defaulttext) {if (thisfield.value == "") {thisfield.value = defaulttext;}}

function retryValidation(link) {
	var main = jQuery(link).closest('.qcf-main');
	main.find('.qcf-state').hide();
	main.find('.qcf-form-wrapper').fadeIn('fast');
}
jQuery(document).ready(function() {
	$ = jQuery;
	
	/*
		Add jQuery ajax for form validation.
	*/
	$('.qcf-form').on("submit", function(x) {
		console.log('----------------------------------Submitted');
		x.preventDefault();

		// Contain parent form in a variable
		var f = $(this);
		// Intercept request and handle with AJAX
		console.log( $( this ).serialize() );
		var fd = $(this).serialize();
		var fp = f.closest('.qcf-main');

		
		var executes = 0;
		$('html, body').animate({
			scrollTop: Math.max(fp.offset().top - 100,0),
		}, 200,null,function() {
			
			executes++;
			
			if (executes >= 2) return false;
			
			fp.find('.qcf-state').hide();
			fp.find('.qcf-ajax-loading').show();

			
			$.post(ajaxurl, fd + "&action=qcf_validate_form", function(e) {
					if (e.success !== undefined) {
						data = e;
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
							var file_error = {'name':'attach','error':qfc_file_info.error},
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
												REGEX = new RegExp(qfc_file_info.types[y],"i");
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
									element = f.find('[name='+error.name+']').prepend("<p class='qcf-input-error'><span>"+error.error+"</span></p>");;
								} else {
									element = f.find('[name='+error.name+']');
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
							fp.find('.qcf-state').hide();
							fp.find('.qcf-sending').fadeIn('fast');
							
							/*
								Successful validation!
								
								Disable this callback and officially submit the form.
							*/
							
							f.unbind('submit');
							
							// Invoke submit
							f.find('input[type=submit]').click();
						}
					} else {
						// assume error so just show the form again.
						fp.find('.qcf-state').hide();
						fp.find('.qcf-ajax-error').fadeIn('fast');
					}
					
					return false;
				}, "JSON");
		});
		return false;
	});
});