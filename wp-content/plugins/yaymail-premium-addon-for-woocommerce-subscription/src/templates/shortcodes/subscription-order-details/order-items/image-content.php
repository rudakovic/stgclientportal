<?php

defined( 'ABSPATH' ) || exit;

?>
<div class='yaymail-product-image'>
    <?php
        echo wp_kses_post( apply_filters( 'woocommerce_order_item_thumbnail', $image, $item ) );
    ?>
    </div>
<?php
