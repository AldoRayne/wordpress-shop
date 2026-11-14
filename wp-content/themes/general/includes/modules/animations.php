<?php
/**
 * Scroll animations bootstrap (frontend/components/head/_head.php).
 *
 * Flags <html> before first paint so the hero can start hidden; skipped for
 * prefers-reduced-motion and undone if GSAP never reports ready.
 *
 * @package CHE
 */

add_action(
	'wp_head',
	function (): void {
		?>
<script>
	(function (root) {
		if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
			return;
		}
		root.classList.add('has-anim');
		setTimeout(function () {
			if (!window.animReady) {
				root.classList.remove('has-anim');
			}
		}, 3000);
	})(document.documentElement);
</script>
		<?php
	},
	1
);
