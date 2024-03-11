<?php
/*
Plugin Name: Custom Car Categories
Description: Adds custom taxonomy for Car Brands and Models to WooCommerce products
Version: 1.2
Author: MalaTheMan / MT.GG
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
    $parent_brand_slug = $_GET['carBrand'];
    $models = get_terms(array(
        'taxonomy' => 'car_brand',
        'hide_empty' => false,
        'parent' => get_term_by('slug', $parent_brand_slug, 'car_brand')->term_id,
    ));

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

    $carBrand = isset($_GET['carBrand']) ? sanitize_text_field($_GET['carBrand']) : '';
    $carModel = isset($_GET['carModel']) ? sanitize_text_field($_GET['carModel']) : '';

    $args = array(
        'post_type' => 'product',
        'posts_per_page' => -1,
        'tax_query' => array(
            'relation' => 'AND',
            array(
                'taxonomy' => 'car_brand',
                'field'    => 'slug',
                'terms'    => $carBrand,
            ),
        ),
    );

    if ($carModel) {
        $args['tax_query'][] = array(
            'taxonomy' => 'car_brand',
            'field'    => 'slug',
            'terms'    => $carModel,
        );
    }

    $query = new WP_Query($args);

    if ($query->have_posts()) {
        while ($query->have_posts()) {
            $query->the_post();
            wc_get_template_part('content', 'product');
        }
    } else {
        echo '<p>' . __('No products found', 'textdomain') . '</p>';
    }

    wp_reset_postdata();
    wp_die();
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
    wp_enqueue_style('ccc-css', plugin_dir_url(__FILE__) . 'css/front-css.css');
    wp_localize_script('ccc-ajax-script', 'ccc_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'ccc_enqueue_scripts');
