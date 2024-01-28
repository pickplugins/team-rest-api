<?php
/*
Plugin Name: Team - REST Api
Plugin URI: http://pickplugins.com/
Description: Fully responsive and mobile ready Carousel Slider for your WooCommerce product. unlimited slider anywhere via short-codes and easy admin setting.
Version: 1.0.0
WC requires at least: 3.0.0
WC tested up to: 4.1
Author: PickPlugins
Text Domain: team-rest
Author URI: http://pickplugins.com
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
*/

if (!defined('ABSPATH')) exit;  // if direct access


class TeamRest
{

    public function __construct()
    {

        define('team_rest_plugin_url', plugins_url('/', __FILE__));
        define('team_rest_plugin_dir', plugin_dir_path(__FILE__));
        define('team_rest_plugin_name', 'Team Rest');
        define('team_rest_plugin_version', '1.0.0');


        require_once(team_rest_plugin_dir . 'includes/functions-rest.php');

        //add_action('admin_enqueue_scripts', array($this, '_admin_scripts'));
    }


    public function _admin_scripts()
    {

        wp_enqueue_script('team_rest_js', plugins_url('assets/admin/js/scripts-layouts.js', __FILE__), array('jquery'));
        wp_localize_script(
            'team_rest_js',
            'team_rest_ajax',
            array(
                'team_rest_ajaxurl' => admin_url('admin-ajax.php'),
                'ajax_nonce' => wp_create_nonce('team_rest_ajax_nonce'),
            )
        );

        wp_register_style('font-awesome-4', team_rest_plugin_url . 'assets/global/css/font-awesome-4.css');
        wp_register_style('font-awesome-5', team_rest_plugin_url . 'assets/global/css/font-awesome-5.css');

        wp_enqueue_script('team_rest_js');
    }
}


new TeamRest();
