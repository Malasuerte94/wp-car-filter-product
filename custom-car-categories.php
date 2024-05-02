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

    echo '<option value="">'.__("Alege modelul masinii").'</option>';
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
 * Filter products by url query
 */
function custom_taxonomy_filter($query) {
    if ( !is_admin() && $query->is_main_query() && is_post_type_archive('product') ) {
        $carBrand = isset($_GET['carBrand']) ? sanitize_text_field($_GET['carBrand']) : '';
        $carModel = isset($_GET['carModel']) ? sanitize_text_field($_GET['carModel']) : '';

        // Constructing tax query for car brand
        if ($carBrand) {
            $carBrandQ = array(
                array(
                    'taxonomy' => 'car_brand',
                    'field'    => 'slug',
                    'terms'    => $carBrand,
                ),
            );
        }

        // Constructing tax query for car model
        if ($carModel) {
            $carModelQ = array(
                array(
                    'taxonomy' => 'car_brand',
                    'field'    => 'slug',
                    'terms'    => $carModel,
                ),
            );
        }

        // Combining brand and model tax queries if both are present
        if (isset($carBrandQ) && isset($carModelQ)) {
            $tax_query = array(
                'relation' => 'AND',
                $carBrandQ,
                $carModelQ,
            );
        } elseif (isset($carBrandQ)) {
            $tax_query = $carBrandQ;
        } elseif (isset($carModelQ)) {
            $tax_query = $carModelQ;
        }

        if (isset($tax_query)) {
            $query->set('tax_query', $tax_query);
        }
    }
}
add_action('pre_get_posts', 'custom_taxonomy_filter');


/**
 * Render the Car Brand and Model Filter widget in the shortcode
 *
 * @return string
 */
function ccc_render_filter_widget(): string
{
    ob_start();
    the_widget('Car_Brand_Model_Filter_Widget');
    return ob_get_clean();
}

/**
 * Register the shortcode
 */
function ccc_register_shortcode(): void
{
    add_shortcode('car_brand_model_filter', 'ccc_render_filter_widget');
}
add_action('init', 'ccc_register_shortcode');


/**
 * Add the required files
 * @return void
 */







/**
 * Add the selected car brand and model to WooCommerce filters
 *
 * @param WP_Query $query
 * @return void
 */
function ccc_add_selected_filters($query): void
{
    $carBrand = isset($_GET['carBrand']) ? sanitize_text_field($_GET['carBrand']) : '';
    $carModel = isset($_GET['carModel']) ? sanitize_text_field($_GET['carModel']) : '';

    if ($carBrand && $carModel) {
        $selectedBrand = get_term_by('slug', $carBrand, 'car_brand');
        $selectedModel = get_term_by('slug', $carModel, 'car_brand');

        // Add the selected car brand filter
        $brandFilter = new WC_Query_Meta_Filter('filter_car_brand', 'Car Brand', 'taxonomy', 'car_brand');
        $brandFilter->add_filter_setting('is_hierarchy', true);
        $brandFilter->add_filter_setting('filter_term', $selectedBrand->slug);
        $brandFilter->add_filter_setting('filter_name', $selectedBrand->name);
        $query->add_filter($brandFilter);

        // Add the selected car model filter
        $modelFilter = new WC_Query_Meta_Filter('filter_car_model', 'Car Model', 'taxonomy', 'car_brand');
        $modelFilter->add_filter_setting('is_hierarchy', true);
        $modelFilter->add_filter_setting('filter_term', $selectedModel->slug);
        $modelFilter->add_filter_setting('filter_name', $selectedModel->name);
        $query->add_filter($modelFilter);
    }
}

if (isset($carBrandQ) && isset($carModelQ)) {
    $tax_query = array(
        'relation' => 'AND',
        $carBrandQ,
        $carModelQ,
    );
} elseif (isset($carBrandQ)) {
    $tax_query = $carBrandQ;
} elseif (isset($carModelQ)) {
    $tax_query = $carModelQ;
}

if (isset($tax_query)) {
    $query->set('tax_query', $tax_query);
}

/**
 * Add the selected filters to the WooCommerce product archive page
 *
 * @return void
 */
function ccc_add_selected_filters_to_archive(): void
{
    add_action('woocommerce_product_query', 'ccc_add_selected_filters');
}
add_action('init', 'ccc_add_selected_filters_to_archive');









function ccc_enqueue_scripts(): void
{
    wp_enqueue_script('ccc-ajax-script', plugin_dir_url(__FILE__) . 'js/ccc-ajax.js', array('jquery'));
    wp_enqueue_style('ccc-css', plugin_dir_url(__FILE__) . 'css/front-css.css');
    wp_localize_script('ccc-ajax-script', 'ccc_ajax_obj', array('ajaxurl' => admin_url('admin-ajax.php')));
}

add_action('wp_enqueue_scripts', 'ccc_enqueue_scripts');