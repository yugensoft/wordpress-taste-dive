<?php

namespace Yugensoft\TasteDive;

defined( 'ABSPATH' ) || exit;

/**
 * Class WikiImage
 *
 * Functions for extracting the "primary" image from Wikipedia pages or APIs
 */
final class WikiImage {

	const THUMB_SIZE = 500;

	/**
	 * Get wikipedia page main image URL for recommendation item
	 *
	 * @param array $item Recommendation.
	 *
	 * @return string|null URL to image
	 */
	public static function get( $item ) {
		$methods = array(
			array( __CLASS__, 'get_by_parsing' ),
			array( __CLASS__, 'get_by_p18_claim' ),
		);

		foreach ( $methods as $method ) {
			$result = $method( $item );
			if ( null !== $result ) {
				return $result;
			}
		}

		return null;
	}

	/**
	 * Scrape the wikipedia page infobox to find the main image
	 *
	 * Note: it's common that this is set.
	 *
	 * @param array $item Recommendation.
	 *
	 * @return string|null URL to image
	 */
	public static function get_by_parsing( $item ) {
		$url = $item['wUrl'];

		$xml = simplexml_load_file( $url );
		if ( false === $xml ) {
			return null;
		}

		$infobox_img_xpath = $xml->xpath( '//table[contains(@class,"infobox")]//img/@srcset' );
		if ( empty( $infobox_img_xpath ) ) {
			$infobox_img_xpath = $xml->xpath( '//table[contains(@class,"infobox")]//img/@src' );
		}

		if ( isset( $infobox_img_xpath[0][0] ) ) {
			return preg_replace( '/ .*$/', '', (string) $infobox_img_xpath[0][0] );
		}

		return null;
	}

	/**
	 * Use the P18 Wikimedia 'claim' to find the main image
	 *
	 * Note: it's fairly rare this is set, and when it is it's often wrong or not the 'main' image.
	 *
	 * @param array $item Recommendation.
	 *
	 * @return string|null URL to image
	 */
	public static function get_by_p18_claim( $item ) {
		$title = self::item_wiki_title( $item );

		$thumbsize = 500;
		$api_url   = 'https://www.wikidata.org/w/api.php?';
		$thumb_url = function ( $image ) use ( $thumbsize ) {
			return "https://commons.wikimedia.org/w/thumb.php?f=$image&w=$thumbsize";
		};

		// Attempt to get the main image.
		$wiki_uri = $api_url . http_build_query(
			array(
				'action' => 'wbgetentities',
				'sites'  => 'enwiki',
				'props'  => 'claims',
				'titles' => $title,
				'format' => 'json',
			)
		);
		$json     = wp_remote_get( $wiki_uri );
		if ( false === $json ) {
			return null;
		}

		$data = json_decode( $json, true );

		if ( isset( $data['entities'][-1]['missing'] ) ) {
			return null;
		} else {
			$entity = reset( $data['entities'] );

			// P18 is the image property.
			if ( isset( $entity['claims']['P18'] ) ) {

				$p18 = $entity['claims']['P18'];
				if ( empty( $p18 ) ) {
					return null;
				}

				$first = reset( $p18 );
				if ( isset( $first['mainsnak']['datavalue']['value'] ) ) {
					return $thumb_url( $first['mainsnak']['datavalue']['value'] );
				} else {
					return null;
				}
			}
		}

		return null;
	}

	/**
	 * Get the title of the wikipedia article from the URL.
	 *
	 * @param array $item Recommendation.
	 *
	 * @return mixed Title.
	 */
	public static function item_wiki_title( $item ) {
		return preg_replace( '/https?:\/\/[a-z]*\.wikipedia\.org\/wiki\//', '', $item['wUrl'] );
	}

	/**
	 * Get the image thumb URL.
	 *
	 * @param string $image_file Image file name.
	 *
	 * @return string URL to image on wikimedia.
	 */
	public static function thumb_url( $image_file ) {
		return "https://commons.wikimedia.org/w/thumb.php?f=$image_file&w=" . self::THUMB_SIZE;
	}
}