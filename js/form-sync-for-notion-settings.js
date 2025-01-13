jQuery(document).ready(function($) {
    function toggleRecaptchaFields() {
        var isEnabled = $('#form_sync_for_notion_enable_recaptcha').is(':checked');
        var recaptchaFields = $('.recaptcha-dependent-field').closest('tr');
        
        if (isEnabled) {
            recaptchaFields.show();
            $('.recaptcha-required-field').prop('required', true);
        } else {
            recaptchaFields.hide();
            $('.recaptcha-required-field').prop('required', false);
        }
    }

    $('#form_sync_for_notion_enable_recaptcha').on('change', toggleRecaptchaFields);
    toggleRecaptchaFields(); // Run on page load
}); 