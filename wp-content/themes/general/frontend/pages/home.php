<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>BasicStore</title>
    <?php include('../components/head/_head.php') ?>
    <link rel="stylesheet" href="../assets/libs/swiper/swiper.min.css">
    <link rel="stylesheet" href="../assets/css/element-product_card.css">
    <link rel="stylesheet" href="../assets/css/element-post_card.css">
    <!--    add styles for sections here-->
    <link rel="stylesheet" href="../assets/css/section-hero.css">
    <link rel="stylesheet" href="../assets/css/section-categories.css">
    <link rel="stylesheet" href="../assets/css/section-bestsellers.css">
    <link rel="stylesheet" href="../assets/css/section-banners.css">
    <link rel="stylesheet" href="../assets/css/section-reviews.css">
    <link rel="stylesheet" href="../assets/css/section-posts.css">
    <link rel="stylesheet" href="../assets/css/section-features.css">
    <link rel="stylesheet" href="../assets/css/section-subscribe.css">

</head>
<body class="home">
<?php include('../components/header/_header.php') ?>
<main>
    <?php include('../components/sections/hero.php') ?>
    <?php include('../components/sections/categories.php') ?>
    <?php include('../components/sections/bestsellers.php') ?>
    <?php include('../components/sections/banners.php') ?>
    <?php include('../components/sections/reviews.php') ?>
    <?php include('../components/sections/posts.php') ?>
    <?php include('../components/sections/features.php') ?>
    <?php include('../components/sections/subscribe.php') ?>
</main>

<?php include('../components/footer/_footer.php') ?>
<?php include('../components/scripts/_scripts.php') ?>
<script src="../assets/libs/swiper/swiper.min.js"></script>
<!--add scripts for sections here-->
<script src="../assets/js/section-hero.js"></script>
<script src="../assets/js/section-categories.js"></script>
<script src="../assets/js/section-reviews.js"></script>
</body>
</html>
