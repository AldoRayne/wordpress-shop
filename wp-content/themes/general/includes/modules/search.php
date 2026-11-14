<?php
/**
 * Customize search results.
 *
 * @param \WP_Query $query
 */
add_action( 'pre_get_posts', static function ( $query ) {
	if ( ! $query->is_search() ) {
		return;
	}

	$query->set( 'meta_query', [
		'relation' => 'OR',
		[
			'key'     => 'exclude_from_search',
			'compare' => 'NOT EXISTS',
			'type'    => 'NUMERIC',
		],
		[
			'key'     => 'exclude_from_search',
			'value'   => 1,
			'compare' => '!=',
			'type'    => 'NUMERIC',
		],
	] );
} );
