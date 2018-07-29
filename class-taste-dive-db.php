
<?php

defined( 'ABSPATH' ) || exit;

/**
 * Class TasteDiveDb
 *
 * "Model of the whole" class for all DB actions
 */
final class TasteDiveDb {

	const DB_VERSION = 6;
	const DB_PREFIX = 'tastedive';
	const CACHE_TIMEOUT_DEFAULT = 1440; // minutes

	/**
	 * Initial create from database schema
	 */
	public static function db_install() {
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( self::db_schema() );
		add_option( 'taste_dive_db_version', self::DB_VERSION );
	}

	/**
	 * Update of database schema upon DB_VERSION change
	 */
	public static function db_update() {
		if ( self::DB_VERSION != get_option( 'taste_dive_db_version' ) ) {
			require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
			dbDelta( self::db_schema() );
			update_option( 'taste_dive_db_version', self::DB_VERSION );
		}
	}

	/**
	 * Removal of database entries on plugin uninstall
	 */
	public static function db_uninstall() {
		global $wpdb;
		delete_option( 'taste_dive_settings' );
		$wpdb->query( "DROP TABLE IF EXISTS " . self::db_prefix() . "_cache" );
	}

	/**
	 * Get full prefix for tables associated with this plugin
	 *
	 * @return string Prefix
	 */
	public static function db_prefix(){
		global $wpdb;
		return $wpdb->prefix . self::DB_PREFIX;
	}

	/**
	 * Get the database schema query
	 *
	 * @return string Schema
	 */
	public static function db_schema() {
		global $wpdb;
		$collate = $wpdb->get_charset_collate();
		$prefix = self::db_prefix();

		return "		
			CREATE TABLE `{$prefix}_cache` (
				`key` VARCHAR(255) NOT NULL,
				`value` BLOB NOT NULL,
				`ts` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
				
				PRIMARY KEY (`key`)
			) $collate COMMENT 'Caches of API queries';
		";

	}

	/**
	 * Get a cached TasteDive recommendation set
	 *
	 * @param string $key
	 *
	 * @return array|bool|null|object|void
	 */
	public static function get_cache( $key ) {
		global $wpdb;
		$prefix = self::db_prefix();
		$options = get_option( 'taste_dive_settings' );
		$n = isset( $options['cache_timeout'] ) ? intval($options['cache_timeout']) : self::CACHE_TIMEOUT_DEFAULT;

		// Take a cache timeout of 0 to mean "don't cache"
		if($n <= 0){
			return false;
		}

		return $wpdb->get_row( "
			SELECT * FROM {$prefix}_cache 
			WHERE ts > NOW() - INTERVAL $n MINUTE
			  AND `key`='$key'
			",
			ARRAY_A
		);
	}

	/**
	 * Save a recommendation set
	 *
	 * @param $key
	 * @param $value
	 */
	public static function set_cache( $key, $value ) {
		global $wpdb;
		$prefix = self::db_prefix();

		// Take a cache timeout of 0 to mean "don't cache"
		$options = get_option( 'taste_dive_options' );
		if ( isset( $options['cache_timeout'] ) && intval($options['cache_timeout']) <= 0 ) {
			return;
		}

		$wpdb->replace(
			$prefix."_cache",
			array(
				'key'=>$key,
				'value'=> is_array($value) ? json_encode($value) : $value,
			)
		);
	}
}
