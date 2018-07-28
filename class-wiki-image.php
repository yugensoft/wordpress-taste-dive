<?php

defined( 'ABSPATH' ) || exit;

final class WikiImage {

	const THUMB_SIZE = 500;

	/**
	 * Get wikipedia page main image URL for recommendation item
	 *
	 * @param $item
	 *
	 * @return string|null URL to image
	 */
	public static function get( $item ) {
		$methods = array(
			array(__CLASS__, 'get_by_pageimage'),
			array(__CLASS__, 'get_by_parsing'),
			array(__CLASS__, 'get_by_p18_claim'),
		);

		foreach ( $methods as $method ) {
			$result = $method( $item );
			if ( $result !== null ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Use the Wikimedia P18 'claim' to find the main image
	 *
	 * @param $item
	 *
	 * @return string|null URL to image
	 */
	public static function get_by_parsing( $item ) {
		$url = $item['wUrl'];

		$xml = simplexml_load_file( $url );
		if ( $xml  === false ) {
			return null;
		}

		$infobox_img_xpath = $xml->xpath( '//table[contains(@class,"infobox")]//img/@srcset' );
		if ( empty( $infobox_img_xpath ) ) {
			$infobox_img_xpath = $xml->xpath( '//table[contains(@class,"infobox")]//img/@src' );
		}

		if ( isset( $infobox_img_xpath[0][0] )) {
			return preg_replace( '/ .*$/', '', (string) $infobox_img_xpath[0][0] );
		}

		return null;
	}

	/**
	 * Use the Wikimedia P18 'claim' to find the main image
	 *
	 * @param $item
	 *
	 * @return string|null URL to image
	 */
	public static function get_by_pageimage( $item ) {
		$title = self::item_wiki_title( $item );

		$api_url = "https://www.mediawiki.org/w/api.php?";

		// Attempt to get the main image
		$wiki_uri = $api_url . build_query( array(
				'action'      => 'query',
				'prop'       => 'pageimages',
				'titles'      => $title,
				'format'      => 'json',
				'pithumbsize' => self::THUMB_SIZE,
			) );
		$json = @file_get_contents( $wiki_uri );
		if ( $json === false ) {
			return null;
		}

		$data = json_decode( $json, true );

		// Find the associated image file if possible
		if (isset( $data['query']['pages'][ -1 ]['missing'] ) ) {
			return null;
		} else {

		}

		return null;
	}

	/**
	 * Use the P18 Wikimedia 'claim' to find the main image
	 *
	 * @param $item
	 *
	 * @return string|null URL to image
	 */
	public static function get_by_p18_claim( $item ) {
		$title = self::item_wiki_title( $item );

		$thumbsize = 500;
		$api_url = "https://www.wikidata.org/w/api.php?";
		$thumb_url = function($image) use ($thumbsize) {
			return "https://commons.wikimedia.org/w/thumb.php?f=$image&w=$thumbsize";
		};

		// Attempt to get the main image
		$wiki_uri = $api_url . build_query( array(
				'action'      => 'wbgetentities',
				'sites'       => 'enwiki',
				'props'       => 'claims',
				'titles'      => $title,
				'format'      => 'json',
			) );
		$json = @file_get_contents( $wiki_uri );
		if ( $json === false ) {
			return null;
		}

		$data = json_decode( $json, true );

		if (isset( $data['entities'][ -1 ]['missing'] ) ) {
			return null;
		} else {
			$entity = reset( $data['entities'] );

			// P18 is the image property
			if ( isset( $entity['claims']['P18'] ) ){

				$p18 = $entity['claims']['P18'];
				if ( empty($p18 ) ) {
					return null;
				}

				$first = reset( $p18 );
				if ( isset( $first['mainsnak']['datavalue']['value'] )) {
					return $thumb_url($first['mainsnak']['datavalue']['value']);
				} else {
					return null;
				}
			}
		}

		return null;
	}

	public static function item_wiki_title( $item ) {
		return preg_replace( '/https?:\/\/[a-z]*\.wikipedia\.org\/wiki\//', '', $item['wUrl'] );
	}

	public static function thumb_url( $imageFile ) {
		return "https://commons.wikimedia.org/w/thumb.php?f=$imageFile&w=".self::THUMB_SIZE;
	}
}