<a class="product-card" href="<?= $product['href'] ?? 'product.php' ?>">
	<span class="product-card__img">
		<img src="../assets/img/<?= $product['img'] ?>" alt="<?= $product['name'] ?>" width="360" height="418" loading="lazy">
	</span>
	<span class="product-card__body">
		<span class="product-card__name h3-title"><?= $product['name'] ?></span>
		<span class="product-card__price"><?= $product['price'] ?></span>
	</span>
</a>
