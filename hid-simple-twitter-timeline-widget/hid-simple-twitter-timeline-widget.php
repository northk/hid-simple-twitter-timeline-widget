<?php
/*
Plugin Name: hid-simple-twitter-timeline-widget
Plugin URI: http://highintegritydesign.com
Description: Display a Twitter public timeline as a widget.
Version: 1.0
Author: North Krimsly
Author URI: http://highintegritydesign.com
License: GPL2

hid-simple-twitter-timeline-widget is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 2 of the License, or
any later version.

hid-simple-twitter-timeline-widget is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with hid-simple-twitter-timeline-widget. If not, see http://www.gnu.org/licenses/gpl-2.0.html

*/

class HID_Simple_Twitter_Timeline_Widget extends WP_Widget {  

    private $form_errors; // holds any form validation errors

    public function __construct() {
        parent::__construct(
            // Base ID of your widget
            'HID_Simple_Twitter_Timeline_Widget',
            // Widget name will appear in UI
            'HID Simple Twitter Timeline Widget',
            // Widget description
            array('description' => 'Displays a simple Twitter timeline public feed.'));

        // If the widget is active, add an action to enqueue the Twitter script
        if (is_active_widget(false, false, $this->id_base)) {
            add_action('wp_enqueue_scripts', array( $this, 'load_twitter_script'));
        }

        // Initialize form error messages
        $this->form_errors = new WP_error();
    }


    // enqueue the Twitter script
    public function load_twitter_script() {
        wp_enqueue_script( 'hid-twitter-widget', 
            plugins_url() . '/hid-simple-twitter-timeline-widget/hid-twitter-widget.js');
    }


    // Widget front end - output the widget HTML *********************************************
    public function widget( $args, $instance ) {
        // allow themes to modify the appearance of the widget title
        $title = apply_filters( 'widget_title', $instance['title'] );

        // before and after widget are defined by themes
        print($args['before_widget']);
        if ( ! empty( $title ) )
            print($args['before_title'] . $title . $args['after_title']);

        // Only display the widget if both twitter widget ID and number of tweets is defined
        // Construct a reference to a pre-built Twitter widget
        if (ctype_digit($instance['widget_id']) && ctype_digit($instance['num_tweets'])) {
            $html = '<a class="twitter-timeline" href="https://twitter.com/northk" data-widget-id="' 
                . $instance['widget_id'] . '" data-theme="light" data-link-color="#0862" data-chrome="noheader nofooter noborders noscrollbar transparent" data-tweet-limit="'
                . $instance['num_tweets'] . '"><img src="' . plugins_url() 
                . '/hid-simple-twitter-timeline-widget/ajax-loader.gif" alt="loading"/></a>'; 

            print $html;
        }

        print($args['after_widget']);        
    }


    // Setup form. Allow configuration of a minimal set of widget attributes *****************
    public function form( $instance ) {

        // Set default field values
        $title = empty($instance['title']) ? '' : $instance['title'];
        $widget_id = empty($instance['widget_id']) ? '' : $instance['widget_id'];
        $num_tweets = empty($instance['num_tweets']) ? '' : $instance['num_tweets'];        

        print "<p><label for='" . $this->get_field_id('title') . "'>Title:</label>";
        print "<input class='widefat' id='" . $this->get_field_id('title') . "' name='"
            . $this->get_field_name('title') . "' type='text' value='" . esc_attr($title) . "'/></p>";

        print "<p><label for='" . $this->get_field_id('widget_id') . "'>Enter the numeric widget ID from your Twitter widget settings page:</label>";
        print "<input class='widefat' id='" . $this->get_field_id('widget_id') . "' name='"
            . $this->get_field_name('widget_id') . "' type='text' value='" . esc_attr($widget_id) . "'/></p>";
        if ($this->form_errors->get_error_message('widget-numeric')) {
            print '<p style="color:red">' . $this->form_errors->get_error_message('widget-numeric') . '</p>';      
        }

        print "<p><label for='" . $this->get_field_id('num_tweets') . "'>Number of tweets:</label>";
        print "<input class='widefat' id='" . $this->get_field_id('num_tweets') . "' name='"
            . $this->get_field_name('num_tweets') . "' type='text' value='" . esc_attr($num_tweets) . "'/></p>";
        if ($this->form_errors->get_error_message('num-tweets-numeric')) {
            print '<p style="color:red">' . $this->form_errors->get_error_message('num-tweets-numeric') . '</p>';      
        }
        if ($this->form_errors->get_error_message('num-tweets-range')) {
            print '<p style="color:red">' . $this->form_errors->get_error_message('num-tweets-range') . '</p>';      
        }
    }


    // processes widget options to be saved (including field validation) *********************
    public function update( $new_instance, $old_instance ) {
        $new_instance['title'] = strip_tags( $new_instance['title'] );

        $new_instance['widget_id'] = strip_tags($new_instance['widget_id']);
        if (! ctype_digit($new_instance['widget_id'])) {
            $new_instance['widget_id'] = $old_instance['widget_id'];
            $this->form_errors->add('widget-numeric', 'Widget ID must be numeric.');
        }

        $new_instance['num_tweets'] = strip_tags($new_instance['num_tweets']);
        if (! ctype_digit($new_instance['num_tweets'])) {
            $new_instance['num_tweets'] = $old_instance['num_tweets'];
            $this->form_errors->add('num-tweets-numeric', 'Number of tweets must be numeric.');
        }
        if (ctype_digit($new_instance['num_tweets']) 
            && (($new_instance['num_tweets'] > 20) || ($new_instance['num_tweets'] < 1))) {
            $new_instance['num_tweets'] = $old_instance['num_tweets'];
            $this->form_errors->add('num-tweets-range', 'Number of tweets must be between 1 and 20.');
        }

        return $new_instance;
    }
}


// Register the widget
add_action( 'widgets_init', function(){
     register_widget( 'HID_Simple_Twitter_Timeline_Widget' );
});

?>
