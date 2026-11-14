<?php
namespace Che\PreviewACF;

/**
 * Class ACF_Preview
 *
 * Provides functionality to enhance the ACF (Advanced Custom Fields) user interface by
 * enqueueing custom scripts and styles for the ACF field group editor screen.
 */

if ( class_exists( 'ACF' ) ) {
	class PreviewACF {
		/**
		 * Plugin version.
		 */
		const VERSION = '1.1.0-beta';

		/**
		 * Constructor. Adds WordPress hooks.
		 */
		function __construct() {
			add_action( 'init', [ $this, 'init' ] );
			add_action( 'acf/field_group/admin_enqueue_scripts', [ $this, 'field_group_scripts' ] );
			add_action( 'acf/input/admin_enqueue_scripts', [ $this, 'input_scripts' ] );
			add_action( 'wp_ajax_preview_acf_quick_edit', [ $this, 'preview_acf_quick_edit' ] );

			add_filter( 'script_loader_tag', [ $this, 'script_tag' ], 10, 2 );
		}

		/**
		 * Registers styles and scripts for ACF preview functionality.
		 *
		 * @return void
		 */
		public function init() : void {
			wp_register_style( 'che-preview-acf', THEME_ASSETS_URL . '/backend/css/preview-acf.min.css', [ 'acf-input' ], self::VERSION );
			wp_register_script( 'che-preview-acf', THEME_ASSETS_URL . '/backend/js/preview-acf/preview-acf.js', [
				'jquery',
				'acf-input'
			], self::VERSION, true );
			wp_register_script( 'che-preview-acf-quick-edit', THEME_ASSETS_URL . '/backend/js/preview-acf/quick-edit.js', [ 'acf-input' ], self::VERSION, true );
		}

		/**
		 * Enqueues styles and scripts for ACF field group editor.
		 *
		 * @return void
		 */
		public function field_group_scripts() : void {
			wp_deregister_style( 'che-preview-acf' );
			wp_enqueue_style( 'che-preview-acf', THEME_ASSETS_URL . '/backend/css/preview-acf.min.css', [
				'acf-input',
				'acf-field-group'
			], self::VERSION );
			wp_enqueue_script( 'che-preview-acf' );
		}

		/**
		 * Enqueues styles and scripts for ACF input fields.
		 *
		 * @return void
		 */
		public function input_scripts() : void {
			wp_enqueue_style( 'che-preview-acf' );
			wp_enqueue_script( 'che-preview-acf-quick-edit' );
			wp_localize_script( 'che-preview-acf-quick-edit', 'previewAcf', [
				'url'   => admin_url( 'admin-ajax.php' ),
				'nonce' => wp_create_nonce( 'preview-acf' ),
			] );
		}

		/**
		 * Modifies the script tag to use type="module" for specific scripts.
		 *
		 * @param string $tag    HTML script tag.
		 * @param string $handle Script handle.
		 *
		 * @return string Modified script tag.
		 */
		public function script_tag( string $tag, string $handle ) : string {
			$modules = [ 'che-preview-acf', 'che-preview-acf-quick-edit' ];

			if ( ! in_array( $handle, $modules ) ) {
				return $tag;
			}

			return str_replace( '<script', '<script type="module"', $tag );
		}

		/**
		 * Recursively processes ACF fields and updates their width.
		 *
		 * @param array<int, array<string, mixed>> $fields ACF fields.
		 * @param int $parent_id Parent field ID.
		 * @param array<string, mixed> $changed_fields Modified fields with new widths.
		 *
		 * @return array<int, array<string, mixed>> Updated fields.
		 */
		private function get_fields( array $fields, int $parent_id, array $changed_fields ) : array {
			foreach ( $fields as &$field ) {
				$field['ID']     = $this->get_field_id_by_key( (string) $field['key'] );
				$field['parent'] = $parent_id;

				if ( ! empty( $field['layouts'] ) && is_array( $field['layouts'] ) ) {
					foreach ( $field['layouts'] as $key => $layout ) {
						if ( isset( $layout['sub_fields'] ) && is_array( $layout['sub_fields'] ) ) {
							$field['layouts'][ $key ]['sub_fields'] = $this->get_fields( $layout['sub_fields'], (int)$field['ID'], $changed_fields );
						}
					}
				}

				if ( ! empty( $field['sub_fields'] ) && is_array( $field['sub_fields'] ) ) {
					$field['sub_fields'] = $this->get_fields( $field['sub_fields'], (int)$field['ID'], $changed_fields );
				}

				if ( in_array( $field['key'], array_keys( $changed_fields ) ) ) {
					$field['wrapper']['width'] = $changed_fields[ $field['key'] ];

					acf_update_field( $field );
				}
			}

			return $fields;
		}

		/**
		 * Retrieves the field ID by its key.
		 *
		 * @param string $field_key ACF field key.
		 *
		 * @return int Field ID.
		 */
		private function get_field_id_by_key( string $field_key ) : int {
			global $wpdb;

			return intval( $wpdb->get_var( $wpdb->prepare( "SELECT ID FROM $wpdb->posts WHERE post_name = %s", $field_key ) ) );
		}

		/**
		 * Handles AJAX request for quick editing ACF fields.
		 *
		 * @return void
		 */
		public function preview_acf_quick_edit() : void {
			if ( empty( $_POST['nonce'] ) || ! wp_verify_nonce( $_POST['nonce'], 'preview-acf' ) ) {
				wp_send_json_error( 'Nonce verification failed', 403 );
			}

			$group_key      = ! empty( $_POST['group_key'] ) ? sanitize_text_field( $_POST['group_key'] ) : '';
			$changed_fields = ! empty( $_POST['fields'] ) && is_array( $_POST['fields'] ) ? array_map( 'sanitize_text_field', $_POST['fields'] ) : [];

			$group_post = acf_get_field_group_post( $group_key );

			if ( ! $group_post || ! $group_post instanceof \WP_Post ) {
				wp_send_json_error( 'Invalid group key', 400 );
			}

			/** @var \WP_Post $group_post */
			$group_id = $group_post->ID;
			$group    = acf_get_field_group( $group_id );

			if ( ! $group || ! is_array( $group ) ) {
				wp_send_json_error( 'Field group not found', 404 );
			}

			/** @var array<string, mixed> $group */
			$fields = acf_get_fields( $group );
			if ( ! is_array( $fields ) ) {
				$fields = [];
			}

			$group['fields'] = $this->get_fields( $fields, (int) $group_id, $changed_fields );

			acf_update_field_group( $group );
			acf_write_json_field_group( $group );

			wp_send_json_success();
		}
	}

	// Initialize the plugin.
	new PreviewACF();
}