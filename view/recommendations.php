<?php
/**
 * Recommendations content for display by shortcode.
 *
 * @package Yugensoft\TasteDive
 */

?>

<div class="taste-dive">
	<h3>Recommendations similar to <i><?php echo esc_html( $info['Name'] ); ?></i></h3>

	<?php foreach ( $recommendations as $recommendation ) : ?>
		<div class="recommendation">
			<h4><?php echo esc_html( $recommendation['Name'] ); ?></h4>
			<div>
				<img src="<?php echo esc_attr( $recommendation['image'] ); ?>">
				<?php echo esc_html( $recommendation['wTeaser'] ); ?>
			</div>
			<div class="links">
				<div><a href="https://youtube.com/watch?v=<?php echo esc_attr( $recommendation['yID'] ); ?>" target="_blank"><i class="fab fa-youtube"></i>&nbsp;&nbsp;YouTube trailer</a></div>
				<div><a href="<?php echo esc_attr( $recommendation['wUrl'] ); ?>" target="_blank"><i class="fab fa-wikipedia-w"></i>&nbsp;&nbsp;Wikipedia article</a></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>


