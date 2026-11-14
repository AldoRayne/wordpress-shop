<a class="product-card" href="<?= $product['href'] ?? 'product.php' ?>" data-anim-item>
	<span class="product-card__img" data-anim-media="zoom">
		<img src="../assets/img/<?= $product['img'] ?>" alt="<?= $product['name'] ?>" width="360" height="418" loading="lazy">
	</span>
	<span class="product-card__body">
		<span class="product-card__name h3-title"><?= $product['name'] ?></span>
		<span class="product-card__price"><?= $product['price'] ?></span>
	</span>
</a>
