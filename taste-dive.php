<?php
/**
 * Plugin Name:   Recommendations TasteDive
 * Plugin URI:    https://yugensoft.com/
 * Description:   Recommend similar music, movies, TV shows, books and games with TasteDive.
 * Version:       1.0.2
 * Author:        Yugensoft
 * Author URI:    https://yugensoft.com
 * License:       GPL2
 * License URI:   https://www.gnu.org/licenses/gpl-2.0.html
 * Text Domain:   taste-dive
 *
 * @package TasteDive
 */

namespace Yugensoft\TasteDive;

defined( 'ABSPATH' ) || exit;

define( 'TASTE_DIVE_PLUGIN_FILE', __FILE__ );

include_once dirname( __FILE__ ) . '/class-taste-dive.php';
include_once dirname( __FILE__ ) . '/class-taste-dive-db.php';
include_once dirname( __FILE__ ) . '/class-wiki-image.php';

TasteDive::init_hooks();