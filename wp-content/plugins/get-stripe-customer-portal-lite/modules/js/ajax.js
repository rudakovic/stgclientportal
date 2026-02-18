jQuery(document).ready(function($) {
    var button = $('button[name="gscp-submitted"]');
    button.on('click', function(event) {
        event.preventDefault();
        button.attr('disabled', true);
        var parent = button.closest('.gscp-form');
        var input = parent.find('input[name="gscp-email"]');
        if (input.length && input[0].value) {
            $.post(
                wp_ajax.ajaxurl, {
                    'gscp-email': input[0].value,
                    action: 'gscp-request',
                    'gscp-submitted': true,
                }, function(response) {
                    button.attr('disabled', false);
                    var responseContainer = parent.find('.gscp-response');
                    if (!responseContainer.length) {
                        responseContainer = $('<div class="gscp-response"></div>');
                        parent.prepend(responseContainer);
                    }
                    responseContainer.html(response);
                }
            );
        } else {
            button.attr('disabled', false);
        }
    });
});
