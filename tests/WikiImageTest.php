<?php

namespace Yugensoft\TasteDive;
define( 'ABSPATH', true );
require __DIR__ . '/../class-wiki-image.php';

use PHPUnit\Framework\TestCase;

class WikiImageTest extends TestCase {

	public function testGetByParsing() {
		$result = WikiImage::get_by_parsing( [ 'wUrl' => 'https://en.wikipedia.org/wiki/Jaguar' ] );
		$this->assertNotNull( $result );
	}

	public function testGetByP18Claim() {
		$result = WikiImage::get_by_p18_claim( [ 'wUrl' => 'https://en.wikipedia.org/wiki/Albert_Einstein' ] );
		$this->assertNotNull( $result );
	}
}

