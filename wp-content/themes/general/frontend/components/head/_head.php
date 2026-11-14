<meta name="viewport" content="width=device-width, initial-scale=1">
<meta name="format-detection" content="telephone=no">
<link rel="icon" type="image/png" href="assets/img/favicon.png">
<!-- Styles for frontend -->
<link rel="stylesheet" href="../assets/css/common.css" />
<script>
	(function (root) {
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}
		var variant = (location.search.match(/[?&]anim=(a|b)(&|$)/) || [])[1] || 'b';
		root.classList.add('has-anim');
		root.setAttribute('data-anim-variant', variant);
		setTimeout(function () {
			if (!window.animReady) {
				root.classList.remove('has-anim');
			}
		}, 3000);
	})(document.documentElement);
</script>


<!--<link rel="stylesheet" href="assets/css/hero.css" />-->
<!-- Styles for backend -->
<!--<link rel="stylesheet" href="assets/css/app.min.css" />-->