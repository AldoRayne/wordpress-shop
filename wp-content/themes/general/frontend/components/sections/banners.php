<?php
$banners = [
	['title' => '20% Off on Laptops', 'text' => 'products on sale for limited time only.', 'btn' => 'Shop it now', 'img' => 'banner-1.jpg'],
	['title' => 'Apple collections', 'text' => 'get your best apple products.', 'btn' => 'Shop collection', 'img' => 'banner-2.jpg'],
];
?>
<section class="banners">
	<div class="container banners__inner">
		<?php foreach ($banners as $banner) : ?>
			<div class="banners__item">
				<picture class="banners__img">
					<img src="../assets/img/<?= $banner['img'] ?>" alt="" width="750" height="430" loading="lazy">
				</picture>
				<div class="banners__content">
					<h2 class="banners__title h1-title"><?= $banner['title'] ?></h2>
					<p class="banners__text text-lead"><?= $banner['text'] ?></p>
					<a class="banners__btn btn btn_light" href="shop.php"><?= $banner['btn'] ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</section>
