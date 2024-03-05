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

// Register Taxonomies
function ccc_register_taxonomies()
{
    // Car Brands
    $labels_brand = array(
        'name' => 'Car Brands',
        'singular_name' => 'Car Brand',
        'search_items' => 'Search Car Brands',
        'all_items' => 'All Car Brands',
        'parent_item' => 'Parent Car Brand',
        'parent_item_colon' => 'Parent Car Brand:',
        'edit_item' => 'Edit Car Brand',
        'update_item' => 'Update Car Brand',
        'add_new_item' => 'Add New Car Brand',
        'new_item_name' => 'New Car Brand Name',
        'menu_name' => 'Car Brands',
    );

    $args_brand = array(
        'hierarchical' => true,
        'labels' => $labels_brand,
        'show_ui' => true,
        'show_admin_column' => true,
        'query_var' => true,
        'rewrite' => array('slug' => 'car-brand'),
        'show_in_rest' => true, // Enable Gutenberg support
    );

    register_taxonomy('car_brand', array('product'), $args_brand);

}

add_action('init', 'ccc_register_taxonomies');


// Register Custom Widget
function ccc_register_car_brand_filter_widget()
{
    register_widget('Car_Brand_Model_Filter_Widget');
}

add_action('widgets_init', 'ccc_register_car_brand_filter_widget');


class Car_Brand_Model_Filter_Widget extends WP_Widget
{

    public function __construct()
    {
        parent::__construct(
            'car_brand_model_filter_widget', // Base ID
            'Car Brand & Model Filter', // Name
            array('description' => __('A widget to filter products by Car Brand and Model.', 'textdomain'),) // Args
        );
    }

    public function widget($args, $instance)
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }

        ?>
        <form action="<?php echo esc_url(home_url('/')); ?>" method="GET">
            <select name="car_brand" id="car_brand_select">
                <option value=""><?php _e('Select Car Brand', 'textdomain'); ?></option>
                <?php
                $terms = get_terms(array('taxonomy' => 'car_brand', 'hide_empty' => false, 'parent' => 0));
                foreach ($terms as $term) {
                    echo '<option value="' . $term->slug . '">' . $term->name . '</option>';
                }
                ?>
            </select>

            <select name="car_model" id="car_model_select" disabled>
                <option value=""><?php _e('Select Car Model', 'textdomain'); ?></option>
                <!-- Options will be loaded via AJAX -->
            </select>
        </form>
        <?php

        echo $args['after_widget'];
    }

    // Widget Backend
    public function form($instance): void
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('Car Brands', 'textdomain');
        }
        // Widget admin form
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input class="widefat" id="<?php echo $this->get_field_id('title'); ?>"
                   name="<?php echo $this->get_field_name('title'); ?>" type="text"
                   value="<?php echo esc_attr($title); ?>"/>
        </p>
        <?php
    }

    // Updating widget replacing old instances with new
    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        return $instance;
    }
}

// Register and load the widget
function ccc_load_widget(): void
{
    register_widget('car_brand_model_filter_widget');
}

add_action('widgets_init', 'ccc_load_widget');


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
