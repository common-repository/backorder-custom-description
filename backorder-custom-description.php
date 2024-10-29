<?php

/**
 * Backorder Custom Description
 *
 * @package           BackorderCustomDescription
 * @author            AQ Technologies
 * @copyright         2022 AQ Technologies
 * @license           GPL-2.0-or-later
 *
 * @wordpress-plugin
 * Plugin Name:       WooCommerce Backorder Custom Description
 * Description:       A plugin to add custom description for backordered enabled products.
 * Version:           1.0.0
 * Requires at least: 5.4
 * Requires PHP:      7.3
 * Author:            AQ Technologies
 * Author URI:        https://aq-tech.net
 * Text Domain:       backorder-custom-description
 * License:           GPL v2 or later
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */


 
 /**
 * Activate the plugin.
 */
function bocustom_activate() { 
    // Trigger our function that registers the custom post type plugin.
    
    
    // Clear the permalinks after the post type has been registered.
    flush_rewrite_rules(); 
}
register_activation_hook( __FILE__, 'bocustom_activate' );

add_action('wp_enqueue_scripts', function() {
    wp_enqueue_style('dashicons');
});

add_action( 'admin_menu', 'bocustom_settings_page' );
function bocustom_settings_page() {
    add_menu_page(
        'Backorder Customer Description',
        'Backorder',
        'manage_options',
        'b_order',
        'bocustom_options_page_html',
        'dashicons-media-spreadsheet',
        20
    );
}

function bocustom_options_page_html() {
    if (!empty($_POST)) {
        foreach ($_POST as $key => $value) {
            $key = sanitize_text_field($key);
            $value = sanitize_text_field($value);
            if ($value != "") {
                $meta = get_post_meta($key, 'custom_message', true);
                ($meta) ? update_post_meta($key, 'custom_message', $value) : add_post_meta($key, 'custom_message', $value, true);
            }
            if ($value == "DELETE") {
                delete_post_meta($key, 'custom_message');
            }
        }
        echo "<div class='notice notice-success is-dismissible'><h3>Settings saved successfully!!</h3></div>";
    }
    ?>
    <div class="wrap">
      <h1><?php echo esc_html( get_admin_page_title() ); ?></h1>
      <form action="admin.php?page=b_order" method="post">
            <h2>List of products</h2>
            <div class='notice notice-warning'><h4>Type <i>DELETE</i> to remove custom description</h4></div>
        <?php
        $args = array(
            'orderby'  => 'name',
            'posts_per_page' => -1
        );
        $products = wc_get_products( $args );
        //var_dump($products);
        foreach ($products as $product) {
            if ($product->get_stock_status() == "onbackorder") {
                $meta = esc_attr(get_post_meta($product->get_id(), 'custom_message', true));
                echo ("<b>Product title: </b> " . esc_attr($product->get_name()) . "<br /><b>Custom description: </b>");
                ?>
                <input type='text' class='field' name='<?php echo esc_attr($product->get_id()); ?>' value='<?php echo isset($meta) ? esc_attr($meta) : ""; ?>' />
                <br /><br />
                <?php
            }
        }

        submit_button( __( 'Save Settings', 'textdomain' ) );
        ?>
      </form>
    </div>
    <?php
}

function bocustom_custom_action_after_single_product_title($text) {
    global $product; 
    if ($product) {
        if ($product->is_on_backorder() == true) {
            $meta = get_post_meta($product->get_id(), 'custom_message', true);
            $text = ($meta) ? __($meta, 'your-textdomain') : $text;
        }
    }
    return $text;
}
add_action('woocommerce_get_availability_text', 'bocustom_custom_action_after_single_product_title', 10, 2);

// The code for displaying WooCommerce Product Custom Fields
add_action('woocommerce_product_options_inventory_product_data', 'bocustom_woocommerce_product_custom_fields'); 
function bocustom_woocommerce_product_custom_fields () {
    global $woocommerce, $post;
    echo '<div class=" product_custom_field ">';
    woocommerce_wp_text_input(
        array(
            'id' => 'custom_message',
            'placeholder' => 'Available on backorder until 2025',
            'label' => __('Backorder Custom Description', 'woocommerce'),
            'desc_tip' => 'true',
            'value' => (get_post_meta($post->ID, 'custom_message', true) !== null) ? get_post_meta($post->ID, 'custom_message', true) : ""
        )
    );    
    echo '</div>';
}

// Following code Saves  WooCommerce Product Custom Fields
add_action('woocommerce_process_product_meta', 'bocustom_woocommerce_product_custom_fields_save');
function bocustom_woocommerce_product_custom_fields_save($post_id) {
    // Custom Product Text Field
    $woocommerce_custom_product_text_field = sanitize_text_field($_POST['custom_message']);
    if (!empty($woocommerce_custom_product_text_field)) {
        update_post_meta($post_id, 'custom_message', esc_attr($woocommerce_custom_product_text_field));
    }
}

?>