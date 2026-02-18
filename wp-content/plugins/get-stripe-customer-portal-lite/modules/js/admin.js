jQuery(document).ready(function($) {
    // add key pair
    $('body').on('click', '.gscp-add-keys', function(){
        var firstRow = $('.gscp-table-keys tbody tr').first().clone();
        $('input', firstRow).val('');
        var key = $('.gscp-table-keys tbody tr').length + 1;
        firstRow.find('input.gscp-key-value').val(key);
        firstRow.find('input.gscp-shortcode').val("[get_stripe_customer_portal key_id=\'" + key + "\']");
        firstRow.find('button').prop('disabled', false);
        $('.gscp-table-keys tbody').append( firstRow );
    });

    // remove key pair
    $('body').on('click', '.delete_pair', function(){
        var parent = $(this).parents('tr');
        parent.replaceWith('');
    });
});
