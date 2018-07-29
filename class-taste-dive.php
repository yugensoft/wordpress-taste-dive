<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class TasteDive
 *
 * Controller-esque main class
 */
final class TasteDive {

	const DEFAULT_RECOMMENDATIONS_LIMIT = 5;
	const DEFAULT_CHAR_LIMIT = 0;

	public function __construct() {
		throw new Exception( 'No instantiation.' );
	}

	/**
	 * Registers hooks and adds actions upon plugin being loaded
	 */
	public static function init_hooks(){
		register_activation_hook( TASTE_DIVE_PLUGIN_FILE, array( 'TasteDiveDb', 'db_install' ) );
		register_uninstall_hook( TASTE_DIVE_PLUGIN_FILE, array( 'TasteDiveDb', 'db_uninstall' ) );
		add_action( 'plugins_loaded', array( 'TasteDiveDb', 'db_update' ) );
		add_action( 'init', array( __CLASS__, 'init' ) );

		add_action( 'admin_menu', function() {
			add_options_page(
				'TasteDive Settings',
				'TasteDive',
				'manage_options',
				'taste-dive-settings',
				function() {
					echo self::render( 'settings' );
				}
			);
		});
		add_action( 'admin_init', array( __CLASS__, 'admin_init' ) );

		add_action( 'wp_enqueue_scripts', function() {
			wp_enqueue_style( 'taste_dive', plugin_dir_url( __FILE__ ) . 'assets/css/taste_dive.css' );
		});
	}

	/**
	 * Runs on action: init
	 */
	public static function init() {
		add_shortcode( 'tastedive', array( __CLASS__, 'shortcode' ) );
	}

	/**
	 * Runs on action: admin_init
	 */
	public static function admin_init(){
		// Settings
		register_setting( 'taste_dive', 'taste_dive_settings', function( $input ) {
			return $input;
		} );

		add_settings_section(
			'taste-dive-settings',
			'',
			function(){},
			'taste-dive-settings'
		);

		$settings = array(
			array( 'key' => 'api_key', 'title' => 'API key', 'default' => '' ),
			array( 'key' => 'cache_timeout', 'title' => 'Cache timeout<br><small>(minutes, 0 for no cache)</small>', 'default' => TasteDiveDb::CACHE_TIMEOUT_DEFAULT ),
			array( 'key' => 'default_limit', 'title' => 'Default number of recommendations', 'default' => self::DEFAULT_RECOMMENDATIONS_LIMIT ),
			array( 'key' => 'char_limit', 'title' => 'Character limit for descriptions<br><small>(0 for no limit)</small>', 'default' => self::DEFAULT_CHAR_LIMIT ),
		);
		$options = get_option( 'taste_dive_settings' );

		foreach ( $settings as $setting ) {
			add_settings_field(
				$setting['key'],
				$setting['title'],
				function() use ($setting, $options) {
					$value = isset($options[ $setting['key'] ]) ? $options[ $setting['key'] ] : $setting['default'];
					echo "<input type='text' id='{$setting['key']}' name='taste_dive_settings[{$setting['key']}]' value='$value' size='40' />";
				},
				'taste-dive-settings',
				'taste-dive-settings'
			);
		}

	}

	/**
	 * Render the [tastedive] shortcode.
	 * Accepts attributes:
	 *   search - Media title
	 *   type   - Media type (e.g. 'movie')  [optional]
	 *   limit  - How many recommendations to show  [optional]
	 *
	 * @param array $attrs Tag attributes
	 * @param null $content Content enclosed by tags
	 * @param string $tag Tag identifier
	 *
	 * @return string
	 */
	public static function shortcode( $attrs, $content = null, $tag = '' ) {
		$attrs = array_change_key_case((array)$attrs, CASE_LOWER);

		// Gather attributes
		$search = self::array_multi_search_with_default( $attrs, array( 'search', 'q' ), false );
		$type = self::array_multi_search_with_default( $attrs, array( 'type' ), null );
		$limit = self::array_multi_search_with_default( $attrs, array( 'count', 'limit' ), self::DEFAULT_RECOMMENDATIONS_LIMIT);

		// Check for missing required attributes
		if ( $search === false ) {
			return self::shortcode_error( new WP_Error( 'search_attr', "No 'search' attribute set." ) );
		}

		// General settings
		$settings = get_option( 'taste_dive_settings' );
		$charLimit = isset( $settings['char_limit'] ) ? $settings['char_limit'] : self::DEFAULT_CHAR_LIMIT;

		$recommendations = self::get_recommendations(
			$search,
			$type,
			$limit,
			1,
			$charLimit
		);
		if ( is_wp_error( $recommendations ) ) {
			return self::shortcode_error( $recommendations );
		}

		return self::render( 'recommendations', array(
			'search' => $attrs['search'],
			'info' => $recommendations['Info'][0],
			'recommendations' => $recommendations['Results'],
		) );
	}

	/**
	 * Retrieve recommendations from the TasteDive API, or from the cache if possible.
	 *
	 * @param string $q Search query
	 * @param string|null $type Type of recommendations to return (e.g. 'movie')
	 * @param int $limit Number of recommendations to return
	 * @param int $info Whether to add extended information
	 * @param int $charLimit Character limit for descriptions (or 0 for no limit)
	 *
	 * @return array|mixed|object|WP_Error Recommendations or error
	 */
	public static function get_recommendations(
		$q,
		$type = null,
		$limit = self::DEFAULT_RECOMMENDATIONS_LIMIT,
		$info = 1,
		$charLimit = self::DEFAULT_CHAR_LIMIT
	) {
		$settings = get_option( 'taste_dive_settings' );
		if ( ! isset($settings['api_key']) || empty($settings['api_key']) ) {
			return new WP_Error( 'api_key', 'No API key.' );
		}
		$k = $settings['api_key'];

		$qEnc = urlencode($q);

		// Check for cache
		$key = "{$qEnc}_{$type}_{$info}_{$limit}_{$charLimit}";
		$cache = TasteDiveDb::get_cache($key);
		if ( $cache ) {
			return json_decode($cache['value'], true)['Similar'];
		}

		// Create endpoint uri
		$query = array( 'k' => $k, 'q' => $qEnc, 'info' =>$info, 'limit' => $limit );
		if( $type ) {
			$query['type'] = $type;
		}
		$taste_uri = "https://tastedive.com/api/similar?" . build_query( $query );

		// Get data from endpoint
		$data = @file_get_contents( $taste_uri );
		if ( $data === false ) {
			return new WP_Error( 'get_failed', 'Failed to access TasteDive API.' );
		}

		$data = json_decode( $data, true );

		if ( empty( $data['Similar']['Results'] ) ) {
			return new WP_Error( 'get_failed', "No results found for: $q" );
		}

		foreach ( $data['Similar']['Results'] as &$item ) {
			$item['image'] = WikiImage::get($item);
			if ( $charLimit ) {
				$ellipses = strlen( $item['wTeaser'] ) > $charLimit ? '&hellip;' : '';
				$item['wTeaser'] = substr( $item['wTeaser'], 0, $charLimit ).$ellipses;
			}
		}

		// Save to cache
		TasteDiveDb::set_cache( $key, $data );

		return $data['Similar'];
	}


	/**
	 * Render 'view' files
	 *
	 * @param string $view Name of view
	 * @param array $data Variables to be passed to view
	 *
	 * @return string Rendered view
	 */
	public static function render($view, array $data = array()){
		extract($data);
		ob_start();
		include( "view/{$view}.php" );
		return ob_get_clean();
	}

	/**
	 * Inform the user of an error rendering the shortcode
	 *
	 * @param WP_Error $error
	 *
	 * @return string Error description
	 */
	public static function shortcode_error( WP_Error $error ) {
		return "<p>TasteDive shortcode error: ".$error->get_error_message()."</p>";
	}

	/**
	 * Get the first element of an array, with one of a set of given keys, that is not empty;
	 * or the default if none are found.
	 *
	 * @param array $array Input array
	 * @param array $keys Keys to search for
	 * @param mixed $default Value to return if none of the keys are found
	 *
	 * @return mixed Found value or default
	 */
	public static function array_multi_search_with_default( array $array, array $keys, $default ) {
		$a = array_intersect_key( $array, array_flip( $keys ) );
		return empty($a) ? $default : array_values($a)[0];
	}

}