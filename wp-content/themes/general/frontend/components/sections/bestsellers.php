<?php
$products = [
	['name' => 'Sony PlayStation Move', 'price' => '$870', 'img' => 'product-1.png'],
	['name' => 'GoPro', 'price' => '$600', 'img' => 'product-2.png'],
	['name' => 'AirPods Pro', 'price' => '$400', 'img' => 'product-3.png'],
	['name' => 'Iphone 15 Pro', 'price' => '$2000', 'img' => 'product-4.png'],
	['name' => 'Apple MacBook Pro', 'price' => '$3000', 'img' => 'product-5.png'],
	['name' => 'Iphone X Pro', 'price' => '$900', 'img' => 'product-6.png'],
	['name' => 'PlayStation 5', 'price' => '$750', 'img' => 'product-7.png'],
	['name' => 'Gimbal Parts', 'price' => '$250', 'img' => 'product-8.png'],
];
?>
<section class="bestsellers">
	<div class="container">
		<h2 class="bestsellers__title h2-title" data-anim="title">Best selling items</h2>
		<div class="bestsellers__grid" data-anim="cards">
			<?php foreach ($products as $product) : ?>
				<?php include('../components/elements/_product-card.php') ?>
			<?php endforeach; ?>
		</div>
		<div class="bestsellers__more">
			<a class="btn" href="shop.php">Go to shop</a>
		</div>
	</div>
</section>
