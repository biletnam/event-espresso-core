
//uses the variables localized in EE_Form_Section_Proper's _enqueue_and_localize_form_js()
//to generate the validation js
jQuery(document).ready(function(){jQuery(ee_form_section_vars.form_section_id).closest('form').validate({
		rules : ee_form_section_vars.validation_rules
		});
});

//adds a method used for validation URLs, which isn't native to jquery validate
jQuery(document).ready(function(){
	jQuery.validator.addMethod("validUrl", function(value, element) {
		if(this.optional(element)){
			return true;
		}else{
			var RegExp = /(ftp|http|https):\/\/(\w+:{0,1}\w*@)?(\S+)(:[0-9]+)?(\/|\/([\w#!:.?+=&%@!\-\/]))?/;

			if(RegExp.test(value)){
				return true;
			}else{
				return false;
			}
		}
	}, ee_form_section_vars.localized_error_messages.validUrl);
});