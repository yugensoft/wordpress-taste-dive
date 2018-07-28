<?php
/**
 * Plugin Name: TasteDive Recommendations
 * Plugin URI: https://yugensoft.com/
 * Description: Recommend similar music, movies, TV shows, books and games with TasteDive.
 * Version: 1.0.0
 * Author: Yugensoft
 * Author URI: https://yugensoft.com
 *
 * Text Domain: taste-dive
 *
 * @package TasteDive
 */

defined( 'ABSPATH' ) || exit;

define( 'TASTE_DIVE_PLUGIN_FILE', __FILE__ );

include_once dirname( __FILE__ ) . '/class-taste-dive.php';
include_once dirname( __FILE__ ) . '/class-taste-dive-db.php';
include_once dirname( __FILE__ ) . '/class-wiki-image.php';

TasteDive::init_hooks();