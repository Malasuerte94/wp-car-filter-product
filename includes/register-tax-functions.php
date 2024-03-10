<?php
// Register Taxonomies
function ccc_register_taxonomies(): void
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
        'show_in_rest' => true,
    );

    register_taxonomy('car_brand', array('product'), $args_brand);

    // Add custom field to taxonomy
    add_action('car_brand_add_form_fields', 'ccc_taxonomy_image_field');
    add_action('car_brand_edit_form_fields', 'ccc_taxonomy_image_field');

    function ccc_taxonomy_image_field($term): void
    {
        $term_id = isset($term->term_id) ? $term->term_id : null;
        ?>
        <div class="form-field">
            <label for="ccc_taxonomy_image"><?php _e('Brand Image', 'text_domain'); ?></label>
            <input type="text" name="ccc_taxonomy_image" id="ccc_taxonomy_image" class="ccc_taxonomy_image" value="<?php echo esc_attr(get_term_meta($term_id, 'ccc_taxonomy_image', true)); ?>" />
            <button class="ccc_taxonomy_image_button button"><?php _e('Upload/Add Image', 'text_domain'); ?></button>
            <div class="ccc_taxonomy_image_preview"></div>
        </div>
        <?php
    }

    // Save custom field data when creating or updating a term
    add_action('created_car_brand', 'ccc_save_taxonomy_image');
    add_action('edited_car_brand', 'ccc_save_taxonomy_image');

    function ccc_save_taxonomy_image($term_id): void
    {
        if (isset($_POST['ccc_taxonomy_image'])) {
            update_term_meta($term_id, 'ccc_taxonomy_image', sanitize_text_field($_POST['ccc_taxonomy_image']));
        }
    }

    // Enqueue scripts for media uploader
    add_action('admin_enqueue_scripts', 'ccc_taxonomy_image_scripts');

    function ccc_taxonomy_image_scripts($hook): void
    {
        if ($hook == 'edit-tags.php' || $hook == 'term.php') {
            wp_enqueue_media();
            wp_enqueue_script('ccc-taxonomy-image', plugin_dir_url(__FILE__) . '../js/taxonomy-image.js', array('jquery'), '1.0', true);
            wp_localize_script('ccc-taxonomy-image', 'ccc_taxonomy_image', array('ajax_url' => admin_url('admin-ajax.php')));
        }
    }
}

add_action('init', 'ccc_register_taxonomies');
