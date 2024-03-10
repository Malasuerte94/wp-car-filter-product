<?php

// Register Custom Widget
function ccc_register_car_brand_filter_widget(): void
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

    public function widget($args, $instance): void
    {
        echo $args['before_widget'];
        if (!empty($instance['title'])) {
            echo $args['before_title'] . apply_filters('widget_title', $instance['title']) . $args['after_title'];
        }
        ?>
        <form action="<?php echo esc_url(home_url('/')); ?>" method="GET">
            <div class="car-model">
            <?php
            $terms = get_terms(array('taxonomy' => 'car_brand', 'hide_empty' => false, 'parent' => 0));
            foreach ($terms as $term) {
                echo '<label>';
                echo '<input class="car_brand_select hidden" type="radio" id="car_brand_select'.$term->slug.'" name="car_brand" value="'
                    .$term->slug.'" />';
                echo '<img alt="'.$term->slug.'" src="'.get_term_meta($term->term_id, 'ccc_taxonomy_image', true).'"/></label>';
            }
            ?>
            </div>
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
    public function update($new_instance, $old_instance): array
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