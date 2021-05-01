<?php
/*
Plugin Name: Manual Order for Woocommerce
Plugin URI: https://wordpress.org/plugins/manual-order/
Description: Manually create quick WooCommerce order for existing and new customers from Dashboard menu. 
Version: 1.1.0
Author: Coderstime
Author URI: https://profiles.wordpress.org/coderstime/
Domain Path: /languages
License: GPLv2 or later
Text Domain: mofw
*/

// don't call the file directly
if ( !defined( 'ABSPATH' ) ) exit;

/**
 * CodersManualOrder class
 *
 * The class that holds the entire CodersManualOrder plugin
 *
 * @author Coders Time <coderstime@gmail.com>
 */

final class CodersManualOrder {

    /**
     *
     * construct method description
     *
     */
    public function __construct ( ) {

        register_activation_hook( __FILE__, [ $this, 'activate' ] );
        register_deactivation_hook( __FILE__, [ $this, 'deactivate' ] );

         // Localize our plugin
        add_action( 'init', [ $this, 'localization_setup' ] );

        add_filter( 'plugin_action_links_' . plugin_basename( __FILE__ ), [ $this, 'action_links' ] );

        add_action('admin_menu', [$this,'create_dashboard_menu']);
        add_action('admin_enqueue_scripts', [$this,'mofw_scripts'] );
        add_action('wp_ajax_mofw_genpw', [ $this,'mofw_password_generate']);
        add_action('admin_post_mofw_form', [$this,'post_mofw_form'] );
        add_action('wp_ajax_mofw_fetch_user', [$this,'mofw_fetch_user'] );
        add_action('mofw_order_processing_complete',[$this,'order_processing_complete']);

    }

    /**
     *
     * run when plugin install
     * install time store on option table
     */
    
    public function activate ( ) {
        if ( false === get_option('mofw_active') ) {
           add_option('mofw_active',[time(), '1.1.0']);
        }
    }

    /**
     *
     * run when deactivate the plugin
     * store deactivate time on database option table
     */
    

    public function deactivate ( ) {
        add_option('mofw_deactive',time());
    }

    /**
     * Initialize plugin for localization
     *
     * @uses load_plugin_textdomain()
     */
    public function localization_setup() {
        load_plugin_textdomain( 'mofw', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
    }

    /**
     *
     * run css and javascript code with thickbox for modal
     *
     */
    public function mofw_scripts( $hook ) {

        if ( 'toplevel_page_wc-manual-order' == $hook ) {
            add_filter( 'admin_footer_text', [$this,'mofw_copyright_text'] );

            $asset_file_link = plugins_url( '', __FILE__ );
            $folder_path = __DIR__ ;

            wp_enqueue_style('select2', $asset_file_link . '/../woocommerce/assets/css/select2.css', array(), filemtime($folder_path.'/../woocommerce/assets/css/select2.css'));
            wp_enqueue_style('mofw-style', $asset_file_link . '/assets/css/style.css', array(), filemtime($folder_path.'/assets/css/style.css'));            
            wp_enqueue_script('select2', $asset_file_link . '/../woocommerce/assets/js/select2/select2.js', array('jquery'), filemtime($folder_path.'/../woocommerce/assets/js/select2/select2.js'), true);
            wp_enqueue_script('mofw-script', $asset_file_link . '/assets/js/mofw.js', array('jquery', 'thickbox'), filemtime($folder_path.'/assets/js/mofw.js'), true);

            wp_localize_script('mofw-script', 'mofw', array(
                'nonce' => wp_create_nonce('mofw'),
                'ajax_url' => admin_url('admin-ajax.php'),
                'sp' => __('Choose Product', 'mofw'),
                'dc' => __('Discount Coupon', 'mofw'),
                'cc' => __('Coupon Code', 'mofw'),
                'dt' => __('Discount in ' . get_option( 'woocommerce_currency' ), 'mofw') . ' ('.get_woocommerce_currency_symbol() . ')',
            ));
            add_thickbox();
        }
    }

    /**
     *
     * Create Manual order menu with cart icon
     *
     */
    public function create_dashboard_menu ( ) {
        add_menu_page(
            __('Manual Order Create', 'mofw'),
            __('Manual Order', 'mofw'),
            'manage_woocommerce',
            'wc-manual-order',
            [$this,'mofw_admin_page'],
            'dashicons-cart'
        );
    }

    /**
     *
     * Load html design form
     *
    */

    public function mofw_admin_page() {
        include('order-form.php');
    }

    /**
     *
     * Generate password for new user
     *
    */
    public function mofw_password_generate () {
        $nonce = sanitize_text_field($_POST['nonce']);
        $action = 'mofw';
        if ( wp_verify_nonce($nonce, $action)) {
            echo wp_generate_password(12);
        }
        die();
    }

    /**
     *
     * after submitting form run this method
     *
     */
    

    public function post_mofw_form () {
        if (isset($_POST['submit'])) {
            $order_id =  $this->mofw_process_submission();
            wp_safe_redirect(
                esc_url_raw(
                    add_query_arg('order_id', $order_id, admin_url('admin.php?page=wc-manual-order'))
                )
            );
        }
    }

    /**
     *
     * Process submitted data and return order id
     *
     */
    

    public function mofw_process_submission() {
        $mofw_order_identifier = sanitize_text_field($_POST['mofw_identifier']);
        $processed = get_transient("mofw{$mofw_order_identifier}");

        if ($processed) {
            return $processed;
        }

        if ( wp_verify_nonce(sanitize_text_field($_POST['mofw_form_nonce']), 'mofw_form')) {
            if ( sanitize_text_field($_POST['customer_id']) == 0 ) {
                $email = strtolower(sanitize_text_field($_POST['email']));
                $first_name = sanitize_text_field($_POST['first_name']);
                $last_name = sanitize_text_field($_POST['last_name']);
                $password = sanitize_text_field($_POST['password']);
                $billing_phone = sanitize_text_field($_POST['phone']);
                $customer = wp_create_user( $email, $password, $email );
                update_user_meta($customer, 'first_name', $first_name);
                update_user_meta($customer, 'last_name', $last_name);
                update_user_meta($customer, 'billing_phone', $billing_phone);
                $customer = new WP_User($customer);
            } else {
                $customer = new WP_User(sanitize_text_field($_POST['customer_id']));
            }
            WC()->frontend_includes();
            WC()->session = new WC_Session_Handler();
            WC()->session->init();
            WC()->customer = new WC_Customer($customer->ID, 1);

            $cart = new WC_Cart();
            WC()->cart = $cart;
            $cart->empty_cart();

            $selected_items = wc_clean($_POST['item']);
            if ( !empty($selected_items) ) {
                foreach ($selected_items as $item) {
                    $cart->add_to_cart( $item, 1);
                }
            }

            $discount = trim(sanitize_text_field($_POST['discount']));
            if ($discount == '') {
                $discount = 0;
            }

            $isCoupon = (isset($_POST['coupon'])) ? true : false;

            $checkout = WC()->checkout();

            $phone = sanitize_text_field($_POST['phone']);

            $order_id = $checkout->create_order(array(
                'billing_phone' => $phone,
                'billing_email' => $customer->user_email,
                'payment_method' => 'cash',
                'billing_first_name' => $customer->first_name,
                'billing_last_name' => $customer->last_name,
            ));

            set_transient("mofw{$mofw_order_identifier}", $order_id, 60);

            $order = wc_get_order($order_id);
            update_post_meta( $order_id, '_customer_user', $customer->ID);
            if ($isCoupon) {
                $order->apply_coupon($discount);
            } elseif ($discount > 0) {
                $total = $order->calculate_totals();
                $order->set_discount_total($discount);
                $payable_amount = $total - floatval($discount);
                $order->set_total( $payable_amount < 0 ? 0 : $payable_amount );
            }
            if (isset($_POST['note']) && !empty($_POST['note'])) {
                $order_note = apply_filters('mofw_order_note', sanitize_text_field($_POST['note']), $order_id);
                $order->add_order_note($order_note);
            }
            $order_status = apply_filters( 'mofw_order_status', 'processing' );
            $order->set_status($order_status);
            do_action('mofw_order_complete', $order_id);
            return $order->save();
        }
    }

    /**
     *
     * Get user by email
     *
     */
    public function mofw_fetch_user ( ) {
        $nonce = sanitize_text_field($_POST['nonce']);
        $email = strtolower(sanitize_text_field($_POST['email']));
        $action = 'mofw';
        if (wp_verify_nonce($nonce, $action)) {
            $user = get_user_by('email', $email);
            if ($user) {
                echo json_encode(array(
                    'error' => false,
                    'id' => $user->ID,
                    'fn' => $user->first_name,
                    'ln' => $user->last_name,
                    'pn' => get_user_meta($user->ID, 'billing_phone', true)
                ));
            } else {
                echo json_encode(
                    [
                        'error' => true,
                        'id' => 0,
                        'fn' => __('Not Found', 'mofw'),
                        'ln' => __('Not Found', 'mofw'),
                        'pn' => ''
                    ]
                );
            }
        }
        die();
    }

    /**
     *
     * Thickbox modal data process here
     *
     */
    

    public function order_processing_complete ( $order_id ) {
        $order = wc_get_order($order_id);
        $message =  __("<p>Your order number %s is now processing. Please click the next button to edit this order</p><p class='text-center'>%s</p>", 'mofw');
        $order_button = sprintf("<a target='_blank' href='%s' id='mofw-edit-button' class='button button-primary button-hero'>%s %s</a>", $order->get_edit_order_url(), __('Edit Order # ', 'mofw'), $order_id);

        printf($message, $order_id, $order_button);
    }


    /**
     * Show action links on the plugin screen
     *
     * @param mixed $links
     * @return array
     */
    public function action_links( $links ) 
    {
        return array_merge(
            [
                '<a href="' . admin_url( 'admin.php?page=wc-manual-order' ) . '">' . __( 'Settings', 'mofw' ) . '</a>',
                '<a href="' . esc_url( 'https://www.facebook.com/coderstime' ) . '">' . __( 'Support', 'mofw' ) . '</a>',
                '<a href="' . esc_url( 'https://wordpress.org/support/plugin/manual-order/reviews/#new-post' ) . '">' . __( 'Review', 'mofw' ) . '</a>',
            ], $links );
    }

    /*Copyright View Function*/
    public function mofw_copyright_text()
    {
        $plugin = get_plugin_data( __FILE__ );
        $text = sprintf(
            /* translators: %1$s: plugin url, %2$s: Plugin Name */
            __( 'Thank you for using our <a href="%1$s" target="_blank">%2$s</a> plugin.' ),
            $plugin['PluginURI'],
            __( $plugin['Name'] )
        );
        return '<span id="footer-thankyou"> '. $text .'</div>';
    }

}

new CodersManualOrder();