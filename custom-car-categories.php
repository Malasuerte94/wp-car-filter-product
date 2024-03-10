<?php
/*
Plugin Name: Custom Car Categories
Description: Adds custom taxonomy for Car Brands and Models to WooCommerce products, with support for brand images via ACF.
Version: 1.0
Author: Your Name
*/

if (!defined('ABSPATH')) {
    exit; // Exit if accessed directly
}

require_once plugin_dir_path(__FILE__) . 'includes/register-tax-functions.php';
require_once plugin_dir_path(__FILE__) . 'includes/widget-functions.php';

/**
 * Return car models in the form
 * @return void
 */
function ccc_load_models_ajax()
{
    $parent_brand_slug = $_POST['carBrand'];
    $models = get_terms(array(
        'taxonomy' => 'car_brand',
        'hide_empty' => false,
        'parent' => get_term_by('slug', $parent_brand_slug, 'car_brand')->term_id,
    ));

    echo '<option value="">Wait</option>';
    foreach ($models as $model) {
        echo '<option value="' . $model->slug . '">' . $model->name . '</option>';
    }

    wp_die(); // This is required to terminate immediately and return a proper response
}

add_action('wp_ajax_ccc_load_models', 'ccc_load_models_ajax');
add_action('wp_ajax_nopriv_ccc_load_models', 'ccc_load_models_ajax');


/**
 * Ajax filter the products
 * @return void
 */
function ccc_filter_products_ajax() {
    $carBrand = $_POST['carBrand'];
    $carModel = $_POST['carModel'];

    // Modify WP_Query as needed to filter products based on car brand and model
    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1, // or any number
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'car_brand',
                'field'    => 'slug',
                'terms'    => $carBrand,
            ),
            array(
                'taxonomy' => 'car_brand',
                'field'    => 'slug',
                'terms'    => $carModel,
            ),
        ),
    );

    $query = new WP_Query($args);

    if($query->have_posts()) {
        while($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product'); // Use WooCommerce template to display products
        }
    } else {
        echo '<p>'.__('No products found', 'textdomain').'</p>';
    }

    wp_reset_postdata();
    wp_die(); // always end ajax function with this
}

add_action('wp_ajax_ccc_filter_products', 'ccc_filter_products_ajax');
add_action('wp_ajax_nopriv_ccc_filter_products', 'ccc_filter_products_ajax');


/**
 * Add the required files
 * @return void
 */
function ccc_enqueue_scripts(): void
{
    wp_enqueue_script('ccc-ajax-script', plugin_dir_url(__FILE__) . 'js/ccc-ajax.js', array('jquery'));
    wp_localize_script('ccc-ajax-script', 'ccc_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'ccc_enqueue_scripts');
