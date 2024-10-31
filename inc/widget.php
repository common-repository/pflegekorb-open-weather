<?php

// Weather Widget
function open_weather_widget()
{
    register_widget('open_weather_widget');
}

add_action('widgets_init', 'open_weather_widget');

/**
 * Class simple_weather_widget
 */
class open_weather_widget extends WP_Widget
{
    function __construct()
    {
        parent::__construct('open_weather_widget',

            __('Opem weather widget', 'weather'),

            array('description' => __('Simple widget for weather info', 'weather'),));
    }


    public function widget($args, $instance)
    {
        $title = apply_filters('widget_title', $instance['title']);
        $city = strtolower($instance['weather_city']);
        $country = strtolower($instance['weather_country']);
        $api_key = strtolower($instance['weather_key']);

        $user_ip = getenv('REMOTE_ADDR');
        $geo = json_decode(wp_remote_get("http://ip-api.io/json/$user_ip")['body'], true);
        if ($geo["country_code"] && $geo["city"]) {
            $country = $geo["country_code"];
            $city = $geo["city"];
        }

        $response = wp_remote_get("http://api.openweathermap.org/data/2.5/weather?q=" . $city . "," . $country . "&lang=en&units=metric&APPID=" . $api_key . "");
        $weather = json_decode($response['body'], true);

        echo $args['before_widget'];
        $date = date("F j, Y");
        if ($title) {
            echo $args['before_title'] . $title . $args['after_title'];
        }
        ?>
        <div class="weather">
            <img class="weather__img" src="https://openweathermap.org/img/w/<?= $weather['weather'][0]['icon'] ?>.png"
                 alt="Weather <?= $weather['name'] ?>" title="Weather <?= $weather['name'] ?>">
            <span class="weather__desc"><?= $weather['weather'][0]['description'] ?></span>
            <span class="weather__temp"><?= $weather['main']['temp'] ?>&deg;C</span>
            <span class="weather__date"><?= $date ?></span>
            <span class="weather__region"><?= $weather['name'] . ', ' . $weather['sys']['country'] ?></span>
        </div>

        <?php
        echo $args['after_widget'];
    }

    public function form($instance)
    {
        if (isset($instance['title'])) {
            $title = $instance['title'];
        } else {
            $title = __('New title', 'weather');
        }

        $defaults = array(
            'title'           => 'Weather',
            'weather_id'      => 1,
            'weather_city'    => 'kiev',
            'weather_country' => 'ua',
            'weather_key' => 'you openweathermap api key'
        );
        $instance = wp_parse_args((array)$instance, $defaults);
        ?>

        <p>
            <label for="<?php echo $this->get_field_id('title'); ?>"><?php _e('Title:'); ?></label>
            <input id="<?php echo $this->get_field_id('title'); ?>" name="<?php echo $this->get_field_name('title'); ?>"
                   type="text" value="<?php echo esc_attr($title); ?>"/>

        </p>

        <p>
            <label for="<?php echo $this->get_field_id('weather_key'); ?>"><?php _e('Api key:'); ?></label>
            <input id="<?php echo $this->get_field_id('weather_key'); ?>" name="<?php echo $this->get_field_name('weather_key'); ?>"
                   type="text" value="<?php echo esc_attr($instance['weather_key']); ?>" style="width:100%;"/>

        </p>
        <p>
            <label
                    for="<?php echo esc_attr($this->get_field_id('weather_city')); ?>"><?php _e('City', 'weather'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('weather_city')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('weather_city')); ?>"
                   value="<?php echo esc_attr($instance['weather_city']); ?>" style="width:100%;"/>
        </p>
        <p>
            <label
                    for="<?php echo esc_attr($this->get_field_id('weather_country')); ?>"><?php _e('Country', 'weather'); ?></label>
            <input id="<?php echo esc_attr($this->get_field_id('weather_country')); ?>"
                   name="<?php echo esc_attr($this->get_field_name('weather_country')); ?>"
                   value="<?php echo esc_attr($instance['weather_country']); ?>" style="width:100%;"/>
        </p>

        <?php
    }

    public function update($new_instance, $old_instance)
    {
        $instance = array();
        $instance['title'] = (!empty($new_instance['title'])) ? strip_tags($new_instance['title']) : '';
        $instance['weather_city'] = (!empty($new_instance['weather_city'])) ? strip_tags($new_instance['weather_city']) : '';
        $instance['weather_country'] = (!empty($new_instance['weather_country'])) ? strip_tags($new_instance['weather_country']) : '';
        $instance['weather_key'] = (!empty($new_instance['weather_key'])) ? strip_tags($new_instance['weather_key']) : '';
        return $instance;
    }
}