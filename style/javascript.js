$(document).ready(function() {

        $('[data-toggle="tooltip"]').tooltip();

        $('#channel').bind('change', function() {
        var val = $(this).val();

                if (val != "0") {
                        $('#add_integration, #save_integration').prop('disabled', false);
                        $('#add_integration, #save_integration').removeClass('disabled');
                        $('#add_integration_parent').tooltip('disable');
                } else {
                        $('#add_integration, #save_integration').prop('disabled', true);
                        $('#add_integration, #save_integration').addClass('disabled');
                        $('#add_integration_parent').tooltip('enable');
                }
        });

        $('#save_integration').parent().tooltip('disable');
        
        $('.accordion_expand').on('click', function() {
                var $section = $(this).parent();
                $section.find('.accordion_subsection').slideToggle(100);

                var expand_btn = $section.find('.accordion_expand');    
                if (expand_btn.text() == "Expand") {
                        expand_btn.text("Close");
                } else {
                        expand_btn.text("Expand");
                }
        });   
});