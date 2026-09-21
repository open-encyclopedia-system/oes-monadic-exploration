<?php

/**
 * OES Monadic Exploration (OES Core Module)
 *
 * @wordpress-plugin
 * Plugin Name:        OES Monadic Exploration (OES Core Module)
 * Plugin URI:         https://www.open-encyclopedia-system.org/
 * Description:        Display a collection of objects as a monadic exploration, based on the design of Marian Dörk (https://mariandoerk.de/monadicexploration/). Requires OES Core to function.
 * Version:            1.1.0
 * Author:             Maren Welterlich-Strobl, Freie Universität Berlin, FUB-IT, Digitale Forschungsinfrastrukturen
 * Author URI:         https://www.fu-berlin.de/
 * Requires at least:  6.5
 * Tested up to:       7.1
 * Requires PHP:       8.1
 * License:            GPLv2 or later
 * License URI:        https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:        oes-monadic-exploration
 */

if (!defined('ABSPATH')) exit; // Exit if accessed directly

define('OES_MONADIC_PLUGIN_URL', plugin_dir_url( __FILE__ ));

add_action('oes/plugins_loaded', function () {

    if (!OES()->initialized) {
        return;
    }

    if(is_admin()){
        include_once __DIR__ . '/includes/admin/class-module_page.php';
    }

    include_once(__DIR__ . '/includes/functions.php');
    include_once  __DIR__ . '/includes/class-monadic_explorer.php';

    add_filter('body_class', '\OES\Monadic\body_class');
    add_action('wp_enqueue_scripts', '\OES\Monadic\enqueue_scripts');

    add_shortcode('oes_monadic_exploration', '\OES\Monadic\html');

    do_action('oes/monadic_exploration_plugin_loaded');
}, 12);