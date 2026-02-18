<div class="update-message notice inline notice-warning notice-alt">
    <p class="license_expired_text">
        <span><?php echo esc_html__( 'Your license has expired, please ', 'yaymail' ); ?></span>
        <a target="_blank" href="<?php echo esc_url( $license->get_renewal_url() ); ?>"><?php echo esc_html__( 'renew this license', 'yaymail' ); ?></a>
        <span><?php echo esc_html__( ' to download this update. ', 'yaymail' ); ?></span>
    </p>
</div>
