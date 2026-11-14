<?php
/**
 * Class CONTENT_CLI
 *
 * WP-CLI plumbing for the `/content` agent: writes ACF field VALUES onto a
 * specific block instance already living in a post's `post_content`. ACF
 * blocks here are registered via "blocks in block.json" (see acf-blocks/index.php),
 * which means field values are serialized inside the block's own attributes
 * (`data.<field_name>` / `data._<field_name>`) directly in post_content —
 * there is no post-meta storage to write through update_field()/acf_update_value().
 *
 * Confirmed by reading ACF Pro's own source (pro/blocks.php, includes/local-meta.php):
 * a block's `attrs.data` array is registered as ACF's literal in-memory backing
 * store for `acf/pre_load_metadata`/`acf/pre_update_metadata`, so every field
 * type's real load_value()/update_value() convention applies here exactly as it
 * would to postmeta (repeater flat-key rows, gallery ID arrays, etc.) — the field
 * types handled below mirror those conventions rather than guessing.
 */

class CONTENT_CLI {

	/**
	 * Fill in ACF field values on a real block instance.
	 *
	 * ## OPTIONS
	 *
	 * <block>
	 * : Block folder name under acf-blocks/ (e.g. block-cta).
	 *
	 * --post_id=<id>
	 * : The post/page containing the target block instance.
	 *
	 * --fields-file=<path>
	 * : Path to a JSON file mapping ACF field names to values.
	 *
	 * [--instance=<n>]
	 * : Which occurrence of the block to target if it appears more than once. Default 0.
	 *
	 * [--insert]
	 * : If no instance exists yet on the post, insert a new one instead of erroring.
	 *
	 * [--position=<position>]
	 * : append|prepend, only used with --insert. Default append.
	 *
	 * [--overwrite]
	 * : Replace fields that already have a non-empty value. Without this, they are skipped.
	 *
	 * [--dry-run]
	 * : Resolve everything (including live lookups for relationship/taxonomy/post_object/user
	 * fields) and print a per-field report, but make no changes at all — no attachment is
	 * uploaded, no post is updated.
	 *
	 * @param array<int, string>   $args
	 * @param array<string, mixed> $assoc_args
	 */
	public function fill_block_content( $args, $assoc_args ): void {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( 'Missing <block> argument.' );
		}
		$block = $args[0];

		if ( empty( $assoc_args['post_id'] ) || empty( $assoc_args['fields-file'] ) ) {
			WP_CLI::error( 'Both --post_id and --fields-file are required.' );
		}

		$post_id      = (int) $assoc_args['post_id'];
		$instance     = isset( $assoc_args['instance'] ) ? (int) $assoc_args['instance'] : 0;
		$do_insert    = isset( $assoc_args['insert'] );
		$position     = ( ! empty( $assoc_args['position'] ) && is_string( $assoc_args['position'] ) ) ? $assoc_args['position'] : 'append';
		$do_overwrite = isset( $assoc_args['overwrite'] );
		$dry_run      = isset( $assoc_args['dry-run'] );

		$field_defs = $this->get_field_defs( (string) $block );

		$fields_file = (string) $assoc_args['fields-file'];
		if ( ! file_exists( $fields_file ) ) {
			WP_CLI::error( "Fields file not found: {$fields_file}" );
		}
		$raw    = file_get_contents( $fields_file );
		$fields = is_string( $raw ) ? json_decode( $raw, true ) : null;
		if ( ! is_array( $fields ) ) {
			WP_CLI::error( 'Fields file does not contain a valid JSON object.' );
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			WP_CLI::error( "Post {$post_id} not found." );
			return;
		}

		$block_name = 'acf/' . $block;
		$blocks     = parse_blocks( $post->post_content );
		$matches    = $this->find_block_nodes( $blocks, $block_name );

		$target_index = null;
		if ( isset( $matches[ $instance ] ) ) {
			$target_index = $matches[ $instance ];
		} elseif ( ! $do_insert ) {
			$count = count( $matches );
			WP_CLI::error( "No instance {$instance} of {$block_name} found on post {$post_id} ({$count} instance(s) present). Pass --insert to add a new one." );
		}

		$report              = [];
		$created_attachments = [];

		if ( null !== $target_index ) {
			$node = $this->get_node_by_path( $blocks, $target_index );
			$data = ( isset( $node['attrs']['data'] ) && is_array( $node['attrs']['data'] ) ) ? $node['attrs']['data'] : [];

			foreach ( $fields as $name => $value ) {
				if ( ! isset( $field_defs[ $name ] ) ) {
					$valid = implode( ', ', array_keys( $field_defs ) );
					WP_CLI::error( "Unknown field '{$name}'. Valid fields: {$valid}" );
				}
				$this->apply_field( $name, $value, $field_defs[ $name ], $data, $do_overwrite, $dry_run, $created_attachments, $report );
			}

			if ( ! $dry_run ) {
				$this->set_node_by_path( $blocks, $target_index, $data );
			}
		} else {
			$data = [];
			foreach ( $fields as $name => $value ) {
				if ( ! isset( $field_defs[ $name ] ) ) {
					$valid = implode( ', ', array_keys( $field_defs ) );
					WP_CLI::error( "Unknown field '{$name}'. Valid fields: {$valid}" );
				}
				$this->apply_field( $name, $value, $field_defs[ $name ], $data, true, $dry_run, $created_attachments, $report );
			}

			if ( ! $dry_run ) {
				$new_block = [
					'blockName'    => $block_name,
					'attrs'        => [
						'name' => $block_name,
						'data' => $data,
						'mode' => 'preview',
					],
					'innerBlocks'  => [],
					'innerHTML'    => '',
					'innerContent' => [ null ],
				];

				if ( 'prepend' === $position ) {
					array_unshift( $blocks, $new_block );
				} else {
					$blocks[] = $new_block;
				}
			}
		}

		if ( ! empty( $report ) ) {
			WP_CLI::log( "Field report:\n - " . implode( "\n - ", $report ) );
		}

		if ( $dry_run ) {
			$action = ( null !== $target_index ) ? 'update' : 'insert a new instance of';
			WP_CLI::success( "Dry run — would {$action} {$block_name} on post {$post_id}. No changes made." );
			return;
		}

		$new_content = serialize_blocks( $blocks );
		// wp_insert_post()/wp_update_post() always wp_unslash() incoming data (it assumes
		// $_POST-style slashed input); without wp_slash() here, the literal backslashes in
		// serialize_blocks()'s </> escaping get stripped, corrupting the block JSON.
		// wp_slash()'s own return type is declared as string|array (not generic on its
		// input), so PHPStan can't narrow it back to array on its own even though the
		// argument here is always an array literal — the (array) cast is a no-op at
		// runtime and only exists to satisfy that.
		wp_update_post( (array) wp_slash( [
			'ID'           => $post_id,
			'post_content' => $new_content,
		] ) );

		$edit_link = get_edit_post_link( $post_id, 'raw' );
		WP_CLI::log( 'Edit link: ' . ( is_string( $edit_link ) ? $edit_link : '(none)' ) );
		if ( ! empty( $created_attachments ) ) {
			WP_CLI::log( 'Attachments created: ' . implode( ', ', $created_attachments ) );
		}

		WP_CLI::success( "Updated {$block_name} on post {$post_id}." );
	}

	/**
	 * Remove one or all instances of an ACF block from a post's content. Used to strip
	 * dead/duplicate block instances after a block's markup has been hardcoded directly
	 * into a PHP template (e.g. acf/banner-standard moved into single.php) and the
	 * content-embedded copies are now pure dead weight.
	 *
	 * ## OPTIONS
	 *
	 * <block>
	 * : Block folder name under acf-blocks/ (e.g. banner-standard).
	 *
	 * --post_id=<id>
	 * : The post/page containing the target block instance(s).
	 *
	 * [--instance=<n>]
	 * : Remove only this specific occurrence by index. Without this, ALL matching
	 * instances found on the post are removed.
	 *
	 * [--dry-run]
	 * : Report which instance(s) would be removed (index path + a short content
	 * preview), but make no changes at all.
	 *
	 * @param array<int, string>   $args        Positional arguments.
	 * @param array<string, mixed> $assoc_args  Associative arguments.
	 */
	public function remove_block_content( $args, $assoc_args ): void {
		if ( empty( $args[0] ) ) {
			WP_CLI::error( 'Missing <block> argument.' );
		}
		$block = $args[0];

		if ( empty( $assoc_args['post_id'] ) ) {
			WP_CLI::error( '--post_id is required.' );
		}

		$post_id = (int) $assoc_args['post_id'];
		$dry_run = isset( $assoc_args['dry-run'] );

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			WP_CLI::error( "Post {$post_id} not found." );
			return;
		}

		$block_name = 'acf/' . $block;
		$blocks     = parse_blocks( $post->post_content );
		$matches    = $this->find_block_nodes( $blocks, $block_name );

		if ( isset( $assoc_args['instance'] ) ) {
			$instance = (int) $assoc_args['instance'];
			if ( ! isset( $matches[ $instance ] ) ) {
				$count = count( $matches );
				WP_CLI::error( "No instance {$instance} of {$block_name} found on post {$post_id} ({$count} instance(s) present)." );
			}
			$paths = array( $matches[ $instance ] );
		} else {
			$paths = $matches;
		}

		if ( empty( $paths ) ) {
			WP_CLI::error( "No instances of {$block_name} found on post {$post_id}. Nothing to do." );
		}

		// Process deepest/highest indices first so removing one node never shifts the
		// index of a path not yet processed (paths may differ in depth, so a plain
		// numeric sort on the last segment alone isn't enough — compare element by element).
		usort(
			$paths,
			function ( array $a, array $b ): int {
				$len = max( count( $a ), count( $b ) );
				for ( $i = 0; $i < $len; $i++ ) {
					$a_value = $a[ $i ] ?? -1;
					$b_value = $b[ $i ] ?? -1;
					if ( $a_value !== $b_value ) {
						return $b_value <=> $a_value;
					}
				}
				return 0;
			}
		);

		foreach ( $paths as $path ) {
			$node     = $this->get_node_by_path( $blocks, $path );
			$preview  = mb_substr( serialize_block( $node ), 0, 80 );
			$path_str = implode( '.', $path );
			WP_CLI::log( ( $dry_run ? 'WOULD-REMOVE' : 'REMOVE' ) . " {$block_name} at path [{$path_str}]: {$preview}" );

			if ( ! $dry_run ) {
				$this->remove_node_by_path( $blocks, $path );
			}
		}

		if ( $dry_run ) {
			WP_CLI::success( 'Dry run — would remove ' . count( $paths ) . " instance(s) of {$block_name} from post {$post_id}. No changes made." );
			return;
		}

		$new_content = serialize_blocks( $blocks );
		// wp_insert_post()/wp_update_post() always wp_unslash() incoming data (it assumes
		// $_POST-style slashed input); without wp_slash() here, the literal backslashes in
		// serialize_blocks()'s </> escaping get stripped, corrupting the block JSON.
		// wp_slash()'s own return type is declared as string|array (not generic on its
		// input), so PHPStan can't narrow it back to array on its own even though the
		// argument here is always an array literal — the (array) cast is a no-op at
		// runtime and only exists to satisfy that.
		wp_update_post( (array) wp_slash( [
			'ID'           => $post_id,
			'post_content' => $new_content,
		] ) );

		WP_CLI::success( 'Removed ' . count( $paths ) . " instance(s) of {$block_name} from post {$post_id}." );
	}

	/**
	 * Create a new post with optional content (converted from HTML to core Gutenberg
	 * blocks, or written verbatim as already-built block markup), an excerpt, one or
	 * more categories, and a featured image.
	 *
	 * ## OPTIONS
	 *
	 * --title=<title>
	 * : Post title.
	 *
	 * [--post_type=<type>]
	 * : Post type. Default: post.
	 *
	 * [--status=<status>]
	 * : Post status. Default: publish.
	 *
	 * [--excerpt=<text>]
	 * : Post excerpt.
	 *
	 * [--category=<name-or-id>]
	 * : Comma-separated category names/slugs/IDs. Unlike apply_reference_field()'s
	 * taxonomy handling (never guessed — SKIP-UNRESOLVED on a miss), an unresolved
	 * name here is auto-created as a new term: a missing category on a brand-new
	 * post is low-risk and expected, unlike silently mis-linking an existing post.
	 *
	 * [--content-file=<path>]
	 * : Path to the post content source. Interpreted per --content-format.
	 *
	 * [--content-format=<format>]
	 * : html|blocks. Default: html. "html" runs the file's raw HTML through
	 * convert_html_to_blocks() (paragraph/heading/image/list/quote, sideloading
	 * every <img> into the Media Library via resolve_attachment()). "blocks" writes
	 * the file's content verbatim as already-built Gutenberg block markup.
	 *
	 * [--featured-image=<source>]
	 * : Local path, URL, or existing attachment ID for the featured image.
	 *
	 * [--dry-run]
	 * : Resolve everything (categories, content conversion, featured image) and
	 * report it, but make no changes at all.
	 *
	 * @param array<int, string>   $args
	 * @param array<string, mixed> $assoc_args
	 */
	public function create_post( $args, $assoc_args ): void {
		if ( empty( $assoc_args['title'] ) || ! is_string( $assoc_args['title'] ) ) {
			WP_CLI::error( '--title is required.' );
		}
		$title = $assoc_args['title'];

		$post_type = ( ! empty( $assoc_args['post_type'] ) && is_string( $assoc_args['post_type'] ) ) ? $assoc_args['post_type'] : 'post';
		$status    = ( ! empty( $assoc_args['status'] ) && is_string( $assoc_args['status'] ) ) ? $assoc_args['status'] : 'publish';
		$excerpt   = ( ! empty( $assoc_args['excerpt'] ) && is_string( $assoc_args['excerpt'] ) ) ? $assoc_args['excerpt'] : '';
		$dry_run   = isset( $assoc_args['dry-run'] );

		$report              = [];
		$created_attachments = [];

		$content = '';
		if ( ! empty( $assoc_args['content-file'] ) && is_string( $assoc_args['content-file'] ) ) {
			$format  = ( ! empty( $assoc_args['content-format'] ) && is_string( $assoc_args['content-format'] ) ) ? $assoc_args['content-format'] : 'html';
			$content = $this->build_post_content( $assoc_args['content-file'], $format, $dry_run, $created_attachments, $report );
		}

		$term_ids = [];
		if ( ! empty( $assoc_args['category'] ) && is_string( $assoc_args['category'] ) ) {
			foreach ( array_filter( array_map( 'trim', explode( ',', $assoc_args['category'] ) ) ) as $category ) {
				$term_id = $this->resolve_category_term( $category, $dry_run );
				if ( null !== $term_id ) {
					$term_ids[] = $term_id;
				}
			}
		}

		$featured_id = null;
		if ( ! empty( $assoc_args['featured-image'] ) && is_string( $assoc_args['featured-image'] ) ) {
			$featured_id = $this->resolve_attachment( 'featured_image', $assoc_args['featured-image'], $dry_run, $created_attachments, $report );
			if ( null !== $featured_id ) {
				$report[] = "featured_image: " . ( $dry_run ? 'WOULD-WRITE' : 'WRITE' ) . " (attachment #{$featured_id})";
			}
		}

		if ( ! empty( $report ) ) {
			WP_CLI::log( "Field report:\n - " . implode( "\n - ", $report ) );
		}

		if ( $dry_run ) {
			WP_CLI::success( "Dry run — would create {$post_type} \"{$title}\" (status: {$status}). No changes made." );
			return;
		}

		// wp_slash()'s own return type is declared as string|array (not generic on its
		// input), so PHPStan can't narrow it back to array on its own even though the
		// argument here is always an array literal — the (array) cast is a no-op at
		// runtime and only exists to satisfy that.
		$post_id = wp_insert_post( (array) wp_slash( [
			'post_title'   => $title,
			'post_content' => $content,
			'post_excerpt' => $excerpt,
			'post_status'  => $status,
			'post_type'    => $post_type,
		] ) );

		// Branching on instanceof (rather than is_wp_error(), whose real core body isn't a
		// trivial instanceof passthrough) so PHPStan narrows $post_id to a plain int below.
		if ( $post_id instanceof WP_Error ) {
			WP_CLI::error( "Failed to create post — {$post_id->get_error_message()}" );
			return;
		}
		if ( ! $post_id ) {
			WP_CLI::error( 'Failed to create post — unknown error' );
			return;
		}

		if ( ! empty( $term_ids ) ) {
			wp_set_object_terms( $post_id, $term_ids, 'category' );
		}

		if ( null !== $featured_id ) {
			set_post_thumbnail( $post_id, $featured_id );
		}

		$edit_link = get_edit_post_link( $post_id, 'raw' );
		WP_CLI::log( 'Edit link: ' . ( is_string( $edit_link ) ? $edit_link : '(none)' ) );
		if ( ! empty( $created_attachments ) ) {
			WP_CLI::log( 'Attachments created: ' . implode( ', ', $created_attachments ) );
		}

		WP_CLI::success( "Created {$post_type} #{$post_id} \"{$title}\"." );
	}

	/**
	 * Write post_content on an already-existing post — converting HTML to core
	 * Gutenberg blocks (or using already-built block markup verbatim) — either
	 * replacing the post's content outright or appending/prepending to what's
	 * already there. Complements fill_block_content(), which only ever touches
	 * ACF field values on a specific block instance, never the post's own
	 * standalone post_content.
	 *
	 * ## OPTIONS
	 *
	 * --post_id=<id>
	 * : The post to update.
	 *
	 * --content-file=<path>
	 * : Path to the post content source. Interpreted per --content-format.
	 *
	 * [--content-format=<format>]
	 * : html|blocks. Default: html. See create_post's --content-format.
	 *
	 * [--mode=<mode>]
	 * : replace|append|prepend. Default: replace.
	 *
	 * [--dry-run]
	 * : Resolve everything and report it — including a preview of the existing
	 * content that --mode=replace would discard — but make no changes at all.
	 *
	 * @param array<int, string>   $args
	 * @param array<string, mixed> $assoc_args
	 */
	public function set_post_content( $args, $assoc_args ): void {
		if ( empty( $assoc_args['post_id'] ) || empty( $assoc_args['content-file'] ) ) {
			WP_CLI::error( 'Both --post_id and --content-file are required.' );
		}

		$post_id = (int) $assoc_args['post_id'];
		$mode    = ( ! empty( $assoc_args['mode'] ) && is_string( $assoc_args['mode'] ) ) ? $assoc_args['mode'] : 'replace';
		$dry_run = isset( $assoc_args['dry-run'] );

		if ( ! in_array( $mode, [ 'replace', 'append', 'prepend' ], true ) ) {
			WP_CLI::error( "Invalid --mode '{$mode}'. Use replace, append, or prepend." );
		}

		$post = get_post( $post_id );
		if ( ! $post instanceof WP_Post ) {
			WP_CLI::error( "Post {$post_id} not found." );
			return;
		}

		$format = ( ! empty( $assoc_args['content-format'] ) && is_string( $assoc_args['content-format'] ) ) ? $assoc_args['content-format'] : 'html';

		$report              = [];
		$created_attachments = [];

		$new_content = $this->build_post_content( (string) $assoc_args['content-file'], $format, $dry_run, $created_attachments, $report );

		if ( 'replace' === $mode ) {
			$final_content = $new_content;
		} elseif ( 'append' === $mode ) {
			$final_content = trim( $post->post_content ) . "\n\n" . $new_content;
		} else {
			$final_content = $new_content . "\n\n" . trim( $post->post_content );
		}

		if ( ! empty( $report ) ) {
			WP_CLI::log( "Field report:\n - " . implode( "\n - ", $report ) );
		}

		if ( $dry_run ) {
			$existing_length = mb_strlen( $post->post_content );
			$preview         = mb_substr( trim( $post->post_content ), 0, 80 );
			WP_CLI::log( "Existing post_content: {$existing_length} char(s), starts: \"{$preview}\"" );
			WP_CLI::success( "Dry run — would {$mode} post_content on post {$post_id}. No changes made." );
			return;
		}

		// wp_slash()'s own return type is declared as string|array (not generic on its
		// input), so PHPStan can't narrow it back to array on its own even though the
		// argument here is always an array literal — the (array) cast is a no-op at
		// runtime and only exists to satisfy that.
		wp_update_post( (array) wp_slash( [
			'ID'           => $post_id,
			'post_content' => $final_content,
		] ) );

		$edit_link = get_edit_post_link( $post_id, 'raw' );
		WP_CLI::log( 'Edit link: ' . ( is_string( $edit_link ) ? $edit_link : '(none)' ) );
		if ( ! empty( $created_attachments ) ) {
			WP_CLI::log( 'Attachments created: ' . implode( ', ', $created_attachments ) );
		}

		WP_CLI::success( "Updated post_content on post {$post_id} ({$mode})." );
	}

	/**
	 * Dispatch a single field into $data (by reference) by ACF field type, appending one or
	 * more human-readable lines to $report. Overwrite-protection is checked once here, generically,
	 * for every type (a repeater's own storage key holds its row count, so the same check works
	 * for it too without special-casing).
	 *
	 * @param array<string, mixed> $field   full ACF field definition (from group JSON)
	 * @param array<string, mixed> $data
	 * @param array<int, int>      $created_attachments
	 * @param array<int, string>   $report
	 */
	private function apply_field( string $storage_key, mixed $value, array $field, array &$data, bool $overwrite, bool $dry_run, array &$created_attachments, array &$report ): void {
		if ( ! $overwrite && array_key_exists( $storage_key, $data ) && ! empty( $data[ $storage_key ] ) ) {
			$report[] = "{$storage_key}: SKIP-EXISTING (already has a value, pass --overwrite to replace)";
			return;
		}

		switch ( $field['type'] ) {
			case 'repeater':
				$this->apply_repeater( $storage_key, $value, $field, $data, $dry_run, $created_attachments, $report );
				break;

			case 'gallery':
				$this->apply_gallery( $storage_key, $value, $field, $data, $dry_run, $created_attachments, $report );
				break;

			case 'image':
			case 'file':
				$this->apply_attachment_field( $storage_key, $value, $field, $data, $dry_run, $created_attachments, $report );
				break;

			case 'relationship':
			case 'post_object':
			case 'taxonomy':
			case 'user':
				$this->apply_reference_field( $storage_key, $value, $field, $data, $dry_run, $report );
				break;

			default:
				// text, textarea, wysiwyg, true_false, number, email, url, select, checkbox,
				// radio, range, color_picker, date_*, oembed, password, google_map, link, ...:
				// the fields-file is trusted to already hold the correct raw ACF storage shape.
				$data[ $storage_key ]       = $value;
				$data[ '_' . $storage_key ] = $field['key'];
				$report[]                   = "{$storage_key}: " . ( $dry_run ? 'WOULD-WRITE' : 'WRITE' );
		}
	}

	/**
	 * Repeater: `data.<key>` = row count, each subfield flattened to `data.<key>_<i>_<subname>`.
	 * Recurses through apply_field(), so nested repeaters/images/references inside rows work for free.
	 *
	 * @param array<string, mixed> $field
	 * @param array<string, mixed> $data
	 * @param array<int, int>      $created_attachments
	 * @param array<int, string>   $report
	 */
	private function apply_repeater( string $storage_key, mixed $value, array $field, array &$data, bool $dry_run, array &$created_attachments, array &$report ): void {
		if ( ! is_array( $value ) ) {
			$report[] = "{$storage_key}: SKIP-INVALID (expected an array of row objects for a repeater)";
			return;
		}

		$rows = array_values( $value );

		$sub_fields = [];
		foreach ( ( $field['sub_fields'] ?? [] ) as $sub_field ) {
			if ( ! empty( $sub_field['name'] ) ) {
				$sub_fields[ $sub_field['name'] ] = $sub_field;
			}
		}

		$data[ $storage_key ]       = count( $rows );
		$data[ '_' . $storage_key ] = $field['key'];
		$report[]                   = "{$storage_key}: " . ( $dry_run ? 'WOULD-INSERT-ROWS ' : 'WRITE ' ) . count( $rows ) . ' row(s)';

		foreach ( $rows as $i => $row ) {
			if ( ! is_array( $row ) ) {
				continue;
			}
			foreach ( $row as $sub_name => $sub_value ) {
				if ( ! isset( $sub_fields[ $sub_name ] ) ) {
					$valid = implode( ', ', array_keys( $sub_fields ) );
					WP_CLI::error( "Unknown sub-field '{$sub_name}' in repeater '{$storage_key}'. Valid: {$valid}" );
				}
				$sub_storage_key = "{$storage_key}_{$i}_{$sub_name}";
				// Rows are always freshly written here (no pre-existing per-row value to
				// protect), so subfields always overwrite regardless of the repeater's own flag.
				$this->apply_field( $sub_storage_key, $sub_value, $sub_fields[ $sub_name ], $data, true, $dry_run, $created_attachments, $report );
			}
		}
	}

	/**
	 * Gallery: plain (non-flattened) array of attachment-ID strings.
	 *
	 * @param array<string, mixed> $field
	 * @param array<string, mixed> $data
	 * @param array<int, int>      $created_attachments
	 * @param array<int, string>   $report
	 */
	private function apply_gallery( string $storage_key, mixed $value, array $field, array &$data, bool $dry_run, array &$created_attachments, array &$report ): void {
		$sources = is_array( $value ) ? $value : [ $value ];
		$ids     = [];

		foreach ( $sources as $source ) {
			$id = $this->resolve_attachment( "{$storage_key}[]", (string) $source, $dry_run, $created_attachments, $report );
			if ( null !== $id ) {
				$ids[] = (string) $id;
			}
		}

		$data[ $storage_key ]       = $ids;
		$data[ '_' . $storage_key ] = $field['key'];
		// In dry-run, resolve_attachment() never returns a real ID (it only logs WOULD-UPLOAD /
		// SKIP-UNRESOLVED per item), so a "WRITE N of M" rollup here would misleadingly read "0 of M".
		// The per-item lines already tell the full story in that mode.
		if ( ! $dry_run ) {
			$report[] = "{$storage_key}: WRITE " . count( $ids ) . ' of ' . count( $sources ) . ' item(s)';
		}
	}

	/**
	 * image/file: single attachment ID.
	 *
	 * @param array<string, mixed> $field
	 * @param array<string, mixed> $data
	 * @param array<int, int>      $created_attachments
	 * @param array<int, string>   $report
	 */
	private function apply_attachment_field( string $storage_key, mixed $value, array $field, array &$data, bool $dry_run, array &$created_attachments, array &$report ): void {
		$id = $this->resolve_attachment( $storage_key, (string) $value, $dry_run, $created_attachments, $report );
		if ( null === $id ) {
			return; // resolve_attachment() already appended the report line.
		}
		$data[ $storage_key ]       = $id;
		$data[ '_' . $storage_key ] = $field['key'];
		$report[]                   = "{$storage_key}: WRITE (attachment #{$id})";
	}

	/**
	 * Resolve an image/file/gallery-item value (existing attachment ID, URL, or local file path)
	 * to an attachment ID. In --dry-run mode, a non-numeric source is reported as a hypothetical
	 * upload and never actually touches the Media Library.
	 *
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function resolve_attachment( string $storage_key, string $value, bool $dry_run, array &$created_attachments, array &$report ): ?int {
		if ( is_numeric( $value ) ) {
			return (int) $value;
		}

		if ( $dry_run ) {
			if ( preg_match( '#^https?://#i', $value ) ) {
				$report[] = "{$storage_key}: WOULD-UPLOAD (URL) '{$value}'";
			} elseif ( file_exists( $value ) ) {
				$report[] = "{$storage_key}: WOULD-UPLOAD '{$value}'";
			} else {
				$report[] = "{$storage_key}: SKIP-UNRESOLVED (file not found: '{$value}')";
			}
			return null;
		}

		if ( preg_match( '#^https?://#i', $value ) ) {
			if ( ! function_exists( 'media_sideload_image' ) ) {
				require_once ABSPATH . 'wp-admin/includes/media.php';
				require_once ABSPATH . 'wp-admin/includes/file.php';
				require_once ABSPATH . 'wp-admin/includes/image.php';
			}
			$id = media_sideload_image( $value, 0, null, 'id' );
			if ( is_wp_error( $id ) || ! is_numeric( $id ) ) {
				$report[] = "{$storage_key}: SKIP-UNRESOLVED (could not sideload '{$value}')";
				return null;
			}
			$created_attachments[] = (int) $id;
			return (int) $id;
		}

		if ( ! file_exists( $value ) ) {
			$report[] = "{$storage_key}: SKIP-UNRESOLVED (file not found: '{$value}')";
			return null;
		}

		if ( ! function_exists( 'wp_insert_attachment' ) ) {
			require_once ABSPATH . 'wp-admin/includes/file.php';
			require_once ABSPATH . 'wp-admin/includes/image.php';
		}

		$contents = file_get_contents( $value );
		if ( false === $contents ) {
			$report[] = "{$storage_key}: SKIP-UNRESOLVED (could not read '{$value}')";
			return null;
		}

		$upload = wp_upload_bits( basename( $value ), null, $contents );
		if ( ! empty( $upload['error'] ) ) {
			$report[] = "{$storage_key}: SKIP-UNRESOLVED (upload failed: {$upload['error']})";
			return null;
		}

		$filetype      = wp_check_filetype( $upload['file'], null );
		$attachment_id = wp_insert_attachment(
			[
				'post_mime_type' => $filetype['type'],
				'post_title'     => sanitize_file_name( basename( $value ) ),
				'post_content'   => '',
				'post_status'    => 'inherit',
			],
			$upload['file']
		);

		if ( ! is_int( $attachment_id ) ) {
			$report[] = "{$storage_key}: SKIP-UNRESOLVED (could not create attachment for '{$value}')";
			return null;
		}

		$metadata = wp_generate_attachment_metadata( $attachment_id, $upload['file'] );
		wp_update_attachment_metadata( $attachment_id, $metadata );
		$created_attachments[] = $attachment_id;

		return $attachment_id;
	}

	/**
	 * relationship/post_object/taxonomy/user: best-effort resolution by ID or by name/slug/login/email
	 * against the live site (all read-only lookups — safe under --dry-run too). Ambiguous or
	 * not-found items are reported and simply omitted, never guessed at.
	 *
	 * @param array<string, mixed> $field
	 * @param array<string, mixed> $data
	 * @param array<int, string>   $report
	 */
	private function apply_reference_field( string $storage_key, mixed $value, array $field, array &$data, bool $dry_run, array &$report ): void {
		$type  = $field['type'];
		$items = is_array( $value ) ? $value : [ $value ];

		$resolved   = [];
		$unresolved = [];

		foreach ( $items as $item ) {
			$id = $this->resolve_reference( $type, $field, $item );
			if ( null === $id ) {
				$unresolved[] = $item;
			} else {
				$resolved[] = $id;
			}
		}

		foreach ( $unresolved as $item ) {
			$report[] = "{$storage_key}: SKIP-UNRESOLVED (could not resolve '{$item}' to a single {$type})";
		}

		if ( empty( $resolved ) ) {
			return;
		}

		if ( 'relationship' === $type ) {
			$data[ $storage_key ] = array_map( 'strval', $resolved );
		} elseif ( 'taxonomy' === $type ) {
			$multi                = in_array( $field['field_type'] ?? '', [ 'checkbox', 'multi_select' ], true );
			$data[ $storage_key ] = $multi ? array_map( 'intval', $resolved ) : (int) $resolved[0];
		} else { // post_object, user
			$multi                = ! empty( $field['multiple'] );
			$data[ $storage_key ] = $multi ? array_map( 'strval', $resolved ) : (string) $resolved[0];
		}

		$data[ '_' . $storage_key ] = $field['key'];
		$word                       = $dry_run ? 'WOULD-WRITE' : 'WRITE';
		$report[]                   = "{$storage_key}: {$word} (resolved " . count( $resolved ) . ' of ' . count( $items ) . ' reference(s))';
	}

	/**
	 * @param array<string, mixed> $field
	 * @param mixed                $item
	 */
	private function resolve_reference( string $type, array $field, $item ): ?int {
		if ( is_numeric( $item ) ) {
			return (int) $item;
		}
		$item = (string) $item;

		switch ( $type ) {
			case 'relationship':
			case 'post_object':
				$post_types = ! empty( $field['post_type'] ) ? (array) $field['post_type'] : [ 'any' ];
				$posts      = get_posts( [
					'title'          => $item,
					'post_type'      => $post_types,
					'post_status'    => 'any',
					'posts_per_page' => 2,
				] );
				return ( 1 === count( $posts ) && $posts[0] instanceof WP_Post ) ? (int) $posts[0]->ID : null;

			case 'taxonomy':
				$taxonomy = (string) ( $field['taxonomy'] ?? '' );
				$term     = get_term_by( 'name', $item, $taxonomy );
				if ( ! $term ) {
					$term = get_term_by( 'slug', sanitize_title( $item ), $taxonomy );
				}
				return $term instanceof WP_Term ? (int) $term->term_id : null;

			case 'user':
				$user = get_user_by( 'login', $item );
				if ( ! $user ) {
					$user = get_user_by( 'email', $item );
				}
				if ( ! $user ) {
					$user = get_user_by( 'slug', $item );
				}
				return $user ? (int) $user->ID : null;
		}

		return null;
	}

	/**
	 * Build post_content from a file, per --content-format: "html" runs the file's
	 * raw HTML through convert_html_to_blocks(); "blocks" returns the file's content
	 * verbatim as already-built Gutenberg block markup.
	 *
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function build_post_content( string $content_file, string $format, bool $dry_run, array &$created_attachments, array &$report ): string {
		if ( ! file_exists( $content_file ) ) {
			WP_CLI::error( "Content file not found: {$content_file}" );
			return '';
		}

		$raw = file_get_contents( $content_file );
		if ( ! is_string( $raw ) ) {
			WP_CLI::error( "Could not read content file: {$content_file}" );
			return '';
		}

		if ( 'blocks' === $format ) {
			$report[] = 'content: ' . ( $dry_run ? 'WOULD-WRITE' : 'WRITE' ) . ' (verbatim block markup)';
			return $raw;
		}

		if ( 'html' !== $format ) {
			WP_CLI::error( "Invalid --content-format '{$format}'. Use html or blocks." );
		}

		$block_stats = [
			'paragraph' => 0,
			'heading'   => 0,
			'image'     => 0,
			'list'      => 0,
			'quote'     => 0,
			'html'      => 0,
		];
		$warnings = [];

		$markup = $this->convert_html_to_blocks( $raw, $dry_run, $block_stats, $warnings, $created_attachments, $report );

		foreach ( $warnings as $warning ) {
			WP_CLI::warning( $warning );
		}

		$summary = implode(
			', ',
			array_filter(
				array_map(
					static function ( $count, $type ) {
						return $count > 0 ? "{$type}:{$count}" : '';
					},
					$block_stats,
					array_keys( $block_stats )
				)
			)
		);
		$report[] = 'content: ' . ( $dry_run ? 'WOULD-WRITE' : 'WRITE' ) . " (blocks: {$summary})";

		return $markup;
	}

	/**
	 * Resolve a category by numeric term ID or by name/slug, creating it if it
	 * doesn't exist yet — unlike apply_reference_field()'s taxonomy handling
	 * (never guessed, SKIP-UNRESOLVED on a miss), a missing category on a newly
	 * created/updated post is low-risk and expected, so it's auto-created here.
	 */
	private function resolve_category_term( string $name_or_id, bool $dry_run ): ?int {
		if ( is_numeric( $name_or_id ) ) {
			return (int) $name_or_id;
		}

		$term = get_term_by( 'name', $name_or_id, 'category' );
		if ( ! $term ) {
			$term = get_term_by( 'slug', sanitize_title( $name_or_id ), 'category' );
		}
		if ( $term instanceof WP_Term ) {
			WP_CLI::log( "category \"{$name_or_id}\": REUSE existing term #{$term->term_id}" );
			return (int) $term->term_id;
		}

		if ( $dry_run ) {
			WP_CLI::log( "category \"{$name_or_id}\": WOULD-CREATE new term" );
			return null;
		}

		$inserted = wp_insert_term( $name_or_id, 'category' );
		// is_wp_error() doesn't narrow $inserted for PHPStan (its real core body isn't a
		// trivial instanceof passthrough), so branch on instanceof directly instead.
		if ( $inserted instanceof WP_Error ) {
			WP_CLI::warning( "category \"{$name_or_id}\": could not create — {$inserted->get_error_message()}" );
			return null;
		}
		$term_id = (int) $inserted['term_id'];
		WP_CLI::log( "category \"{$name_or_id}\": CREATED new term #{$term_id}" );
		return $term_id;
	}

	/**
	 * Parse `$html` and convert it into a string of core Gutenberg block markup.
	 *
	 * @param array<string, int> $block_stats
	 * @param array<int, string> $warnings
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function convert_html_to_blocks( string $html, bool $dry_run, array &$block_stats, array &$warnings, array &$created_attachments, array &$report ): string {
		if ( '' === trim( $html ) ) {
			return '';
		}

		$dom = new DOMDocument();
		libxml_use_internal_errors( true );
		// The `<?xml encoding="UTF-8">` prefix trick prevents DOMDocument's default
		// Latin-1 assumption from mangling Cyrillic text; wrapping in a root <div>
		// avoids libxml auto-wrapping fragments in <html><body>.
		$dom->loadHTML(
			'<?xml encoding="UTF-8">' . '<div id="che-content-root">' . $html . '</div>',
			LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
		);
		libxml_clear_errors();

		$root = $dom->getElementById( 'che-content-root' );
		if ( ! $root instanceof DOMElement ) {
			return '';
		}

		$blocks = $this->convert_node_list( $root->childNodes, $dry_run, $block_stats, $warnings, $created_attachments, $report );

		return implode( "\n\n", $blocks );
	}

	/**
	 * @param DOMNodeList<DOMNode> $nodes
	 * @param array<string, int>   $block_stats
	 * @param array<int, string>   $warnings
	 * @param array<int, int>      $created_attachments
	 * @param array<int, string>   $report
	 * @return array<int, string>
	 */
	private function convert_node_list( DOMNodeList $nodes, bool $dry_run, array &$block_stats, array &$warnings, array &$created_attachments, array &$report ): array {
		$blocks = [];
		foreach ( $nodes as $node ) {
			$blocks = array_merge(
				$blocks,
				$this->convert_node( $node, $dry_run, $block_stats, $warnings, $created_attachments, $report )
			);
		}
		return $blocks;
	}

	/**
	 * @param array<string, int> $block_stats
	 * @param array<int, string> $warnings
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 * @return array<int, string>
	 */
	private function convert_node( DOMNode $node, bool $dry_run, array &$block_stats, array &$warnings, array &$created_attachments, array &$report ): array {
		if ( XML_TEXT_NODE === $node->nodeType ) {
			$text = trim( $node->textContent );
			if ( '' === $text ) {
				return [];
			}
			++$block_stats['paragraph'];
			return [ '<!-- wp:paragraph --><p>' . esc_html( $text ) . '</p><!-- /wp:paragraph -->' ];
		}

		if ( ! $node instanceof DOMElement ) {
			return [];
		}

		$tag = strtolower( $node->tagName );

		if ( preg_match( '/^h[1-6]$/', $tag ) ) {
			++$block_stats['heading'];
			$level = (int) substr( $tag, 1 );
			$this->sideload_images_in_node( $node, $dry_run, $created_attachments, $report );
			$inner = $this->get_inner_html( $node );
			return [ "<!-- wp:heading {\"level\":{$level}} --><h{$level}>{$inner}</h{$level}><!-- /wp:heading -->" ];
		}

		if ( 'p' === $tag ) {
			return $this->convert_paragraph( $node, $dry_run, $block_stats, $created_attachments, $report );
		}

		if ( 'img' === $tag ) {
			$block = $this->convert_image( $node, $dry_run, $created_attachments, $report );
			if ( null === $block ) {
				return [];
			}
			++$block_stats['image'];
			return [ $block ];
		}

		if ( 'ul' === $tag || 'ol' === $tag ) {
			++$block_stats['list'];
			return [ $this->convert_list( $node, $tag, $dry_run, $created_attachments, $report ) ];
		}

		if ( 'blockquote' === $tag ) {
			++$block_stats['quote'];
			return [ $this->convert_quote( $node, $dry_run, $block_stats, $warnings, $created_attachments, $report ) ];
		}

		if ( 'div' === $tag ) {
			// Generic wrapper: unwrap, recurse into children.
			return $this->convert_node_list( $node->childNodes, $dry_run, $block_stats, $warnings, $created_attachments, $report );
		}

		// Anything unrecognized (table, iframe, video, figure, bare span, ...): never silently drop
		// content — wrap the node's outer HTML in a core/html block and flag it for spot-checking.
		++$block_stats['html'];
		$warnings[] = "Unrecognized tag <{$tag}> — wrapped as core/html for manual review";
		$outer      = $this->get_outer_html( $node );
		return [ '<!-- wp:html -->' . $outer . '<!-- /wp:html -->' ];
	}

	/**
	 * @param array<string, int> $block_stats
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 * @return array<int, string>
	 */
	private function convert_paragraph( DOMElement $p, bool $dry_run, array &$block_stats, array &$created_attachments, array &$report ): array {
		$element_children = [];
		foreach ( $p->childNodes as $child ) {
			if ( XML_TEXT_NODE === $child->nodeType && '' === trim( $child->textContent ) ) {
				continue;
			}
			$element_children[] = $child;
		}

		$only_images = ! empty( $element_children );
		foreach ( $element_children as $child ) {
			if ( ! ( $child instanceof DOMElement && 'img' === strtolower( $child->tagName ) ) ) {
				$only_images = false;
				break;
			}
		}

		if ( $only_images ) {
			$blocks = [];
			foreach ( $element_children as $img ) {
				if ( ! $img instanceof DOMElement ) {
					continue;
				}
				$block = $this->convert_image( $img, $dry_run, $created_attachments, $report );
				if ( null !== $block ) {
					++$block_stats['image'];
					$blocks[] = $block;
				}
			}
			return $blocks;
		}

		// Mixed/other content: keep inline tags (strong/em/a/br/span) as-is, but any <img>
		// found anywhere inside (even nested in <a>/<span>) must still be sideloaded — the
		// "no external image URLs in final content" rule applies regardless of nesting depth.
		$this->sideload_images_in_node( $p, $dry_run, $created_attachments, $report );
		$inner = $this->get_inner_html( $p );
		++$block_stats['paragraph'];
		return [ '<!-- wp:paragraph --><p>' . $inner . '</p><!-- /wp:paragraph -->' ];
	}

	/**
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function convert_image( DOMElement $img, bool $dry_run, array &$created_attachments, array &$report ): ?string {
		$src = trim( (string) $img->getAttribute( 'src' ) );
		if ( '' === $src ) {
			return null;
		}

		$alt = (string) $img->getAttribute( 'alt' );

		if ( $dry_run ) {
			// resolve_attachment() in dry-run mode never returns a real ID (it only appends a
			// WOULD-UPLOAD/SKIP-UNRESOLVED line to $report) — must not be treated as a real failure here.
			$this->resolve_attachment( 'content_image', $src, true, $created_attachments, $report );
			return '<!-- wp:image {"sizeSlug":"large"} --><figure class="wp-block-image size-large"><img src="' . esc_url( $src ) . '" alt="' . esc_attr( $alt ) . '"/></figure><!-- /wp:image -->';
		}

		$id = $this->resolve_attachment( 'content_image', $src, false, $created_attachments, $report );
		if ( null === $id ) {
			return null;
		}

		$image_src = wp_get_attachment_image_src( $id, 'large' );
		$url       = ( is_array( $image_src ) && ! empty( $image_src[0] ) ) ? $image_src[0] : (string) wp_get_attachment_url( $id );

		$figure = '<figure class="wp-block-image size-large"><img src="' . esc_url( $url ) . '" alt="' . esc_attr( $alt ) . '" class="wp-image-' . $id . '"/></figure>';
		return '<!-- wp:image {"id":' . $id . ',"sizeSlug":"large"} -->' . $figure . '<!-- /wp:image -->';
	}

	/**
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function convert_list( DOMElement $list, string $tag, bool $dry_run, array &$created_attachments, array &$report ): string {
		$ordered  = ( 'ol' === $tag ) ? '{"ordered":true}' : '';
		$list_tag = $tag;
		$items    = [];

		foreach ( $list->childNodes as $child ) {
			if ( ! ( $child instanceof DOMElement && 'li' === strtolower( $child->tagName ) ) ) {
				continue;
			}
			$this->sideload_images_in_node( $child, $dry_run, $created_attachments, $report );
			$inner   = $this->get_inner_html( $child );
			$items[] = '<!-- wp:list-item --><li>' . $inner . '</li><!-- /wp:list-item -->';
		}

		$attrs = $ordered ? ' ' . $ordered : '';
		return "<!-- wp:list{$attrs} -->\n<{$list_tag} class=\"wp-block-list\">\n" . implode( "\n", $items ) . "\n</{$list_tag}>\n<!-- /wp:list -->";
	}

	/**
	 * @param array<string, int> $block_stats
	 * @param array<int, string> $warnings
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function convert_quote( DOMElement $quote, bool $dry_run, array &$block_stats, array &$warnings, array &$created_attachments, array &$report ): string {
		$inner_blocks = $this->convert_node_list( $quote->childNodes, $dry_run, $block_stats, $warnings, $created_attachments, $report );
		if ( empty( $inner_blocks ) ) {
			$inner_blocks = [ '<!-- wp:paragraph --><p></p><!-- /wp:paragraph -->' ];
		}
		return "<!-- wp:quote -->\n<blockquote class=\"wp-block-quote\">\n" . implode( "\n", $inner_blocks ) . "\n</blockquote>\n<!-- /wp:quote -->";
	}

	/**
	 * Rewrite every <img> found anywhere inside $node (direct or nested, e.g. inside <a>/<span>)
	 * to point at a sideloaded Media Library attachment, in place. Applied before serializing
	 * innerHTML for paragraph/heading/list-item/quote text content, so the hard "no external
	 * image URLs in final content" rule holds even for images nested deeper than the explicit
	 * "paragraph containing only <img>" case in the conversion table.
	 *
	 * @param array<int, int>    $created_attachments
	 * @param array<int, string> $report
	 */
	private function sideload_images_in_node( DOMElement $node, bool $dry_run, array &$created_attachments, array &$report ): void {
		$imgs = $node->getElementsByTagName( 'img' );
		// Snapshot first: DOMNodeList is live, mutating attributes mid-iteration is fine,
		// but we still avoid surprises by copying to a plain array.
		$img_nodes = [];
		foreach ( $imgs as $img_node ) {
			$img_nodes[] = $img_node;
		}

		foreach ( $img_nodes as $img_node ) {
			if ( ! $img_node instanceof DOMElement ) {
				continue;
			}
			$src = trim( (string) $img_node->getAttribute( 'src' ) );
			if ( '' === $src ) {
				continue;
			}

			if ( $dry_run ) {
				$this->resolve_attachment( 'content_image', $src, true, $created_attachments, $report );
				continue;
			}

			$id = $this->resolve_attachment( 'content_image', $src, false, $created_attachments, $report );
			if ( null === $id ) {
				// Drop the broken/external reference rather than leaving it pointing off-site.
				if ( $img_node->parentNode instanceof DOMNode ) {
					$img_node->parentNode->removeChild( $img_node );
				}
				continue;
			}

			$url = (string) wp_get_attachment_url( $id );
			$img_node->setAttribute( 'src', $url );
			$img_node->setAttribute( 'class', trim( $img_node->getAttribute( 'class' ) . ' wp-image-' . $id ) );
			$img_node->removeAttribute( 'srcset' );
			$img_node->removeAttribute( 'sizes' );
		}
	}

	/**
	 * Serialize a node's children (its innerHTML), trimmed.
	 */
	private function get_inner_html( DOMElement $node ): string {
		$html = '';
		foreach ( $node->childNodes as $child ) {
			$serialized = $node->ownerDocument instanceof DOMDocument ? $node->ownerDocument->saveHTML( $child ) : false;
			if ( is_string( $serialized ) ) {
				$html .= $serialized;
			}
		}
		return trim( $html );
	}

	/**
	 * Serialize a node including its own opening/closing tag (its outerHTML), trimmed.
	 */
	private function get_outer_html( DOMElement $node ): string {
		$serialized = $node->ownerDocument instanceof DOMDocument ? $node->ownerDocument->saveHTML( $node ) : false;
		return is_string( $serialized ) ? trim( $serialized ) : '';
	}

	/**
	 * Read the block's field group JSON and return name => full ACF field definition array
	 * (so sub_fields/post_type/taxonomy/multiple/field_type are available where needed).
	 *
	 * @return array<string, array<string, mixed>>
	 */
	private function get_field_defs( string $block ): array {
		$folder = THEME_ACF_BLOCKS . '/' . $block;
		if ( ! is_dir( $folder ) || ! file_exists( $folder . '/block.json' ) ) {
			WP_CLI::error( "Block '{$block}' not found under acf-blocks/." );
		}

		$group_files = glob( $folder . '/group_*.json' );
		if ( empty( $group_files ) ) {
			WP_CLI::error( "No group_*.json field group found in acf-blocks/{$block}/." );
			return [];
		}

		$raw   = file_get_contents( $group_files[0] );
		$group = is_string( $raw ) ? json_decode( $raw, true ) : null;
		if ( ! is_array( $group ) || empty( $group['fields'] ) ) {
			WP_CLI::error( "Could not parse fields from {$group_files[0]}." );
			return [];
		}

		$defs = [];
		foreach ( $group['fields'] as $field ) {
			if ( empty( $field['name'] ) ) {
				continue;
			}
			$defs[ $field['name'] ] = $field;
		}

		return $defs;
	}

	/**
	 * Recursively find every node matching $block_name, recursing into innerBlocks
	 * (needed to catch a block nested inside core/block patterns).
	 *
	 * @param array<int, array<string, mixed>> $blocks
	 * @param array<int, int>                  $prefix
	 * @return array<int, array<int, int>> list of index-paths into the (possibly nested) $blocks tree
	 */
	private function find_block_nodes( array $blocks, string $block_name, array $prefix = [] ): array {
		$found = [];
		foreach ( $blocks as $i => $node ) {
			$path = array_merge( $prefix, [ $i ] );
			if ( isset( $node['blockName'] ) && $node['blockName'] === $block_name ) {
				$found[] = $path;
			}
			if ( ! empty( $node['innerBlocks'] ) && is_array( $node['innerBlocks'] ) ) {
				$found = array_merge( $found, $this->find_block_nodes( $node['innerBlocks'], $block_name, $path ) );
			}
		}
		return $found;
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks
	 * @param array<int, int>                  $path
	 * @return array<string, mixed>
	 */
	private function get_node_by_path( array &$blocks, array $path ): array {
		$ref = &$blocks;
		foreach ( $path as $depth => $i ) {
			if ( $depth === count( $path ) - 1 ) {
				return $ref[ $i ];
			}
			$ref = &$ref[ $i ]['innerBlocks'];
		}
		return [];
	}

	/**
	 * @param array<int, array<string, mixed>> $blocks
	 * @param array<int, int>                  $path
	 * @param array<string, mixed>             $data
	 */
	private function set_node_by_path( array &$blocks, array $path, array $data ): void {
		$ref = &$blocks;
		foreach ( $path as $depth => $i ) {
			if ( $depth === count( $path ) - 1 ) {
				$ref[ $i ]['attrs']['data'] = $data;
				return;
			}
			$ref = &$ref[ $i ]['innerBlocks'];
		}
	}

	/**
	 * Remove the node at $path from the (possibly nested) $blocks tree, re-indexing the
	 * array at that depth afterward. Mirrors set_node_by_path()'s traversal style but
	 * unset()s the node instead of replacing attrs.data.
	 *
	 * @param array<int, array<string, mixed>> $blocks The (possibly nested) blocks tree, by reference.
	 * @param array<int, int>                  $path   Index-path to the node to remove.
	 */
	private function remove_node_by_path( array &$blocks, array $path ): void {
		$ref = &$blocks;
		foreach ( $path as $depth => $i ) {
			if ( count( $path ) - 1 === $depth ) {
				unset( $ref[ $i ] );
				$ref = array_values( $ref );
				return;
			}
			$ref = &$ref[ $i ]['innerBlocks'];
		}
	}
}

/**
 * Registered as a single leaf command under the existing `general` namespace
 * (owned by GENERAL_CLI) rather than re-declaring `general` as this class —
 * WP-CLI does not merge two different classes registered at the same
 * container path, but it does support adding an individual command to an
 * already-existing container.
 *
 * Priority 20 (after GENERAL_CLI.php's default-priority `cli_init` hook,
 * regardless of include order): `general` must already exist as a
 * class-based container before we add a leaf under it, or WP-CLI's later
 * `add_command( 'general', 'GENERAL_CLI' )` call replaces our auto-vivified
 * container outright, silently dropping this command.
 */
if ( class_exists( 'WP_CLI' ) ) {
	add_action( 'cli_init', function () {
		WP_CLI::add_command( 'general fill_block_content', [ new CONTENT_CLI(), 'fill_block_content' ] );
		WP_CLI::add_command( 'general remove_block_content', [ new CONTENT_CLI(), 'remove_block_content' ] );
		WP_CLI::add_command( 'general create_post', [ new CONTENT_CLI(), 'create_post' ] );
		WP_CLI::add_command( 'general set_post_content', [ new CONTENT_CLI(), 'set_post_content' ] );
	}, 20 );
}
