<?php

/**
 * Adds a WordPress shortcode [br] to insert an HTML line break.
 *
 * @return string HTML line break (<br>)
 */
add_shortcode( 'br', function () {
	return '<br>';
} );