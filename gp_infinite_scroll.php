<?php
/*
* Plugin Name: Infinite Scroll for GeneratePress
* Plugin URI: https://northwoodsdigital.com/
* Description: A very lightweight infinite scroll plugin for your blog running the GeneratePress Theme by Tom Usborne. Just install and activate this plugin to enable infinite scroll on your blog.
* Version: 1.0.4
* Author: Mathew Moore
* Author URI: https://profiles.wordpress.org/mathewemoore
* License: GPLv2 or later

Copyright (C) 2017 Mathew Moore

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <http://www.gnu.org/licenses/>.

**************************************************************************/

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !class_exists( 'Infinite_Scroll_GeneratePress' ) ) {
  class Infinite_Scroll_GeneratePress
  {
    // Setup some global variables to use throughout this plugin
    private $post_types;
    private $posts_per_page;
    private $ifsg_options;

    public function __construct()
    {
      $this->ifsg_options = get_option( 'ifsg_settings' );
      $this->post_types = (!empty($this->ifsg_options['ifsg_text_field_0']) ? $this->ifsg_options['ifsg_text_field_0'] : '');
      $this->posts_per_page = $this->ifsg_options['ifsg_text_field_1'];
      // Include( INF_SCROLL_PLUGIN_DIR . 'js/ajax-loader.js');
      add_action('wp_enqueue_scripts', array($this,'ifsg_wp_enqueue_scripts'));
      // Ajax for Larger Screens
      add_action( 'wp_ajax_nopriv_post_template_load', array($this, 'post_template_load' ));
      add_action( 'wp_ajax_post_template_load', array($this, 'post_template_load' ));
      // Ajax for Smaller Screens
      add_action( 'wp_ajax_nopriv_post_template_load_small', array($this, 'post_template_load_small' ));
      add_action( 'wp_ajax_post_template_load_small', array($this, 'post_template_load_small' ));
      // Add WordPress Buitin Admin Notices
      add_action( 'admin_notices', array($this, 'isgp_default_admin_notices' ));
      add_action('wp_head', array($this, 'isgp_remove_paging_navigation'));

      // Setup the Plugin Directory Location
      if ( ! defined( 'INF_SCROLL_GP' ))  {
        define( 'INF_SCROLL_GP', plugin_dir_url( __FILE__ ) );
      }
      require_once( dirname(__FILE__) . '/admin/settings.php');
    }

    /* Success message after the users options has been saved */
    public function isgp_default_admin_notices() {
        if( isset($_GET['page']) && $_GET['page'] == 'infinite_scroll_generatepress' ) {
            if( isset($_GET['settings-updated']) ) {
              settings_errors();
            }
        }
    }

    public function isgp_remove_paging_navigation(){
      if (!empty($this->ifsg_options['ifsg_text_field_0']) & !empty($this->ifsg_options['ifsg_text_field_1'])){
        echo '<style type="text/css">.paging-navigation {display: none;}</style>';
      }
    }

    public function infinite_scroll_total_posts()
    {
      // function to get total number of published posts for the specified post types
      if (!empty($this->ifsg_options['ifsg_text_field_1'])){
        $totalcount = array();
          foreach ($this->post_types as $post_type)
          {
            $post_count = wp_count_posts($post_type);
            $totalcount[] = $post_count->publish;
          }
          return array_sum($totalcount);
      } else {return '0';}
    }

    public function ifsg_wp_enqueue_scripts()
    {
      if ( is_home() ) { // Run this script on the blog page only
        // Load CSS & Javascript Assets
        wp_register_style('ifsg_plugin_css',  INF_SCROLL_GP . 'css/style.css', '1.0.0', true);
        wp_enqueue_style('ifsg_plugin_css');

        wp_register_script('ifsg_plugin_scripts',  INF_SCROLL_GP . 'js/ajax-loader.js', array('jquery'), '1.0.0', true);
        wp_enqueue_script('ifsg_plugin_scripts');

        echo '<input type="hidden" name="inf_scroll_gp_ajax_nonce" id="inf_scroll_gp_ajax_nonce" value="' . wp_create_nonce( 'inf_scroll_gp_ajax_nonce' ) . '" />';

        wp_localize_script( 'ifsg_plugin_scripts', 'ifsg_ajax_scripts', array(
          'ajax_url' => admin_url( 'admin-ajax.php' ),
          'plugins_url' => plugins_url( '/', __FILE__ ),
          'total_posts' => $this->infinite_scroll_total_posts(),
        ));
      }
    }

    public function post_template_load(){ // Larger Screens
      // Verify AJAX nonce or die
      check_ajax_referer( 'inf_scroll_gp_ajax_nonce', 'security' );

        $offset = esc_html($_POST['offset']);
        // $count_posts = wp_count_posts($this->post_type);

        // WP_Query arguments
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $args = array(
        	'post_type'    => $this->post_types,
        	'post_status'  => array( 'publish' ),
        	'order'        => 'DESC',
        	'orderby'      => 'date',
          'offset'      => $offset,
          'posts_per_page' => $this->posts_per_page,
        );
        // The Query
        $query = new WP_Query( $args );
        // The Loop
        if ( $query->have_posts() ) {
        	while ( $query->have_posts() ) {
        		$query->the_post();
            ?>
            <?php get_template_part('content'); ?>
            <?php
        	}
        } else {
        	// no posts found
        }
        // Restore original Post Data
        wp_reset_postdata();
          // Custom Content Block 1

          if ( !empty($this->ifsg_options['ifsg_text_field_2'] ) ) { ?>

              <div class="inside-article inf_ad_block">
              <?php echo $this->ifsg_options['ifsg_text_field_2']; ?>
              </div>

            <?php
          }
      }
    die();
    }
    public function post_template_load_small(){ // Small screens
      // Verify AJAX nonce or die
      check_ajax_referer( 'inf_scroll_gp_ajax_nonce', 'security' );

        $offset = esc_html($_POST['offset']);
        // $count_posts = wp_count_posts($this->post_type);

        // WP_Query arguments
        if ( defined( 'DOING_AJAX' ) && DOING_AJAX ) {

        $args = array(
          'post_type'    => $this->post_types,
          'post_status'  => array( 'publish' ),
          'order'        => 'DESC',
          'orderby'      => 'date',
          'offset'      => $offset,
          'posts_per_page' => $this->posts_per_page,
        );
        // The Query
        $query = new WP_Query( $args );
        // The Loop
        if ( $query->have_posts() ) {
          while ( $query->have_posts() ) {
            $query->the_post();
            ?>
            <div class="widget">
              <?php get_template_part('content'); ?>
            </div>
            <?php
          }
        } else {
          return false;
          // no posts found
        }
        // Restore original Post Data
        wp_reset_postdata();
          // Custom Content Block 1

          if ( !empty($this->ifsg_options['ifsg_text_field_2'] ) ) { ?>

              <div class="inside-article inf_ad_block">
              <?php echo $this->ifsg_options['ifsg_text_field_2']; ?>
              </div>

            <?php
          }
      }
    die();
    }
  }

$Infinite_Scroll_GeneratePress = new Infinite_Scroll_GeneratePress();
}
