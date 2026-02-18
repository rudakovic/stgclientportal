<?php

namespace YayMailAddonWcSubscription\Shortcodes\Renderers;

use YayMail\Utils\TemplateHelpers;

/**
 * @method: static WcSubscriptionInformationRenderer get_instance()
 */
class WcSubscriptionInformationRenderer {

    public $item_totals = [];

    public $order = null;

    public $order_note = '';

    public $element_data = null;

    public $is_placeholder = false;

    public $titles = [];

    public $show_product_item_cost = false;

    public $colspan_value = '2';

    private $subscriptions = [ false ];

    private $sample_subscription = [];

    private $has_automatic_renewal = false;

    private $is_parent_order = false;

    private $is_admin_email;

    public function __construct( $order, $element_data, $is_placeholder, $args ) {
        $yaymail_settings     = yaymail_settings();
        $this->element_data   = $element_data;
        $this->is_placeholder = $is_placeholder;
        $this->is_admin_email = ! empty( $args['render_data']['sent_to_admin'] );
        $this->initialize_titles();

        if ( ! $order instanceof \WC_Order ) {
            $this->initialize_sample_data();
        } else {
            $this->order           = $order;
            $this->subscriptions   = wcs_get_subscriptions_for_order( $order, [ 'order_type' => 'any' ] );
            $this->is_parent_order = wcs_order_contains_subscription( $order, 'parent' );
        }
    }

    public function initialize_titles() {
            $this->titles = [
                'id'              => $this->element_data['id_title'] ?? TemplateHelpers::get_content_as_placeholder( 'id_title', esc_html__( 'ID', 'woocommerce' ), $this->is_placeholder ),

                'start_date'      => $this->element_data['start_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'start_date_title', esc_html__( 'Start date', 'woocommerce' ), $this->is_placeholder ),

                'end_date'        => $this->element_data['end_date_title'] ?? TemplateHelpers::get_content_as_placeholder( 'end_date_title', esc_html__( 'End date', 'woocommerce' ), $this->is_placeholder ),

                'recurring_total' => $this->element_data['recurring_total_title'] ?? TemplateHelpers::get_content_as_placeholder( 'recurring_total_title', esc_html__( 'Recurring total', 'woocommerce' ), $this->is_placeholder ),
            ];
    }

    private function initialize_sample_data() {
        $this->sample_subscription = [
            'id'              => 1,
            'id_href'         => '#',
            'start_date'      => gmdate( 'm-d-Y' ),
            'end_date'        => __( 'When cancelled', 'yaymail' ),
            'recurring_total' => '£2 / month',
        ];
    }

    public function get_styles() {
        return TemplateHelpers::get_style(
            [
                'padding'      => '12px',
                'font-size'    => '14px',
                'text-align'   => yaymail_get_text_align(),
                'font-family'  => TemplateHelpers::get_font_family_value( isset( $this->element_data['font_family'] ) ? $this->element_data['font_family'] : 'inherit' ),
                'color'        => isset( $this->element_data['text_color'] ) ? $this->element_data['text_color'] : 'inherit',
                'border-width' => '1px',
                'border-style' => 'solid',
                'border-color' => isset( $this->element_data['border_color'] ) ? $this->element_data['border_color'] : 'inherit',
            ]
        );
    }

    public function render() {
        $is_placeholder = $this->is_placeholder;
        if ( $is_placeholder && empty( $this->sample_subscription ) && empty( $this->subscriptions ) ) {
            esc_html_e( 'The subscription information does not have data in this order.', 'yaymail' );
        } else {
            $this->render_subscriptions();
        }
    }

    public function render_subscriptions() {
        $style       = $this->get_styles();
        $table_style = $this->get_styles() . 'padding: 0';

        ?>
        <table class="yaymail-ws-subscription-information td" cellspacing="0" cellpadding="6" style="<?php echo esc_attr( $table_style ); ?>" border="1" width="100%">

            <thead>
                <tr>
                    <th class="td id-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['id'] ); ?></th>
                    <th class="td start-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['start_date'] ); ?></th>
                    <th class="td end-date-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['end_date'] ); ?></th>
                    <th class="td recurring-total-title" scope="col" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $this->titles['recurring_total'] ); ?></th>
                </tr>
            </thead>

            <tbody>
                <?php
                foreach ( $this->subscriptions as $subscription ) :

                        $is_using_sample = empty( $subscription );

                        $subscription_id_url = $is_using_sample ? $this->sample_subscription['id_href'] : ( ( $this->is_admin_email ) ? wcs_get_edit_post_link( $subscription->get_id() ) : $subscription->get_view_order_url() );

                        $subscription_id = $is_using_sample ? $this->sample_subscription['id'] : $subscription->get_order_number();

                        $subscription_start_date = $is_using_sample ? $this->sample_subscription['start_date'] : date_i18n( wc_date_format(), $subscription->get_time( 'start_date', 'site' ) );

                        $subscription_end_date = $is_using_sample ? $this->sample_subscription['end_date'] : ( ( 0 < $subscription->get_time( 'end' ) ) ? date_i18n( wc_date_format(), $subscription->get_time( 'end', 'site' ) ) : _x( 'When cancelled', 'Used as end date for an indefinite subscription', 'woocommerce-subscriptions' ) );

                        $subscription_total = $is_using_sample ? $this->sample_subscription['recurring_total'] : $subscription->get_formatted_order_total();
                    ?>
                <tr>
                    <td class="td id-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <a href="<?php echo esc_url( $subscription_id_url ); ?>">
                            <?php
                            // translators: %s is the subscription number
                            printf( esc_html_x( '#%s', 'subscription number in email table. (eg: #106)', 'woocommerce-subscriptions' ), esc_html( $subscription_id ) );
                            ?>
                        </a>
                    </td>
                    <td class="td start-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $subscription_start_date ); ?></td>
                    <td class="td end-date-row" scope="row" style="<?php echo esc_attr( $style ); ?>"><?php echo esc_html( $subscription_end_date ); ?></td>
                    <td class="td recurring-total-row" scope="row" style="<?php echo esc_attr( $style ); ?>">
                        <?php echo wp_kses_post( $subscription_total ); ?>
                        <?php if ( $this->is_parent_order && $subscription->get_time( 'next_payment' ) > 0 ) : ?>
                            <br>
                            <small>
                                <?php
                                    // Translators: Next payment date in email table. (eg: Next payment: 2020-12-31)
                                    printf( esc_html__( 'Next payment: %s', 'woocommerce-subscriptions' ), esc_html( date_i18n( wc_date_format(), $subscription->get_time( 'next_payment', 'site' ) ) ) );
                                ?>
                            </small>
                        <?php endif; ?>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
                <?php
                $has_automatic_renewal = ! empty( $subscription ) && ( $this->has_automatic_renewal || ! $subscription->is_manual() );

                if ( $has_automatic_renewal && ! $this->is_admin_email && $subscription->get_time( 'next_payment' ) > 0 ) {
                    if ( count( $this->subscriptions ) === 1 ) {
                        $subscription   = array_values( $this->subscriptions )[0] ?? null;
                        $my_account_url = $subscription->get_view_order_url();
                    } else {
                        $my_account_url = wc_get_endpoint_url( 'subscriptions', '', wc_get_page_permalink( 'myaccount' ) );
                    }

                    // Translators: Placeholders are opening and closing My Account link tags.
                    printf(
                        '
                        <p style="text-align: left; padding-top: 0;">
                        <small>%s</small>
                        </p>',
                        wp_kses_post(
                            sprintf(
                                _n(
                                    'This subscription is set to renew automatically using your payment method on file. You can manage or cancel this subscription from your %1$smy account page%2$s.',
                                    'These subscriptions are set to renew automatically using your payment method on file. You can manage or cancel your subscriptions from your %1$smy account page%2$s.',
                                    count( $this->subscriptions ),
                                    'woocommerce-subscriptions'
                                ),
                                '<a href="' . $my_account_url . '">',
                                '</a>'
                            )
                        )
                    );
                }//end if
                ?>
        <?php
    }
}
