<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.2.0/css/all.css" integrity="sha384-hWVjflwFxL6sNzntih27bfxkr27PmbbK/iSvJ+a4+0owXq79v+lsFkW54bOGbiDQ" crossorigin="anonymous">

<div class="taste-dive">
	<h3>Recommendations similar to <i><?php echo $info['Name'] ?></i></h3>

	<?php foreach($recommendations as $recommendation) : ?>
		<div class="recommendation">
			<h4><?php echo $recommendation['Name'] ?></h4>
			<div>
				<img src="<?php echo $recommendation['image'] ?>">
				<?php echo $recommendation['wTeaser'] ?>
			</div>
			<div class="links">
				<div><a href="https://youtube.com/watch?v=<?php echo $recommendation['yID'] ?>" target="_blank"><i class="fab fa-youtube"></i>&nbsp;&nbsp;YouTube trailer</a></div>
				<div><a href="<?php echo $recommendation['wUrl']?>" target="_blank"><i class="fab fa-wikipedia-w"></i>&nbsp;&nbsp;Wikipedia article</a></div>
			</div>
		</div>
	<?php endforeach; ?>
</div>


