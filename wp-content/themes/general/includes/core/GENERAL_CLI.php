<?php
/**
 * Class GENERAL_CLI
 */

class GENERAL_CLI {

	public string $template_page_json = <<<JSON
				{
					"page_title": "%2",
					"menu_title": "%2",
					"menu_slug": "%1",
					"capability": "manage_options",
					"icon_url": "dashicons-schedule",
					"autoload": true,
					"redirect": false
				}
				JSON;
	public string $template_block_json = <<<JSON
				{
					"name": "acf/%1",
					"title": "%2",
					"description": "",
					"version": "1.0.0",
					"category": "%3",
					"icon": "admin-page",
					"example": {
						"attributes": {
							"mode": "preview",
							"data": { "preview": true }
						}
					},
					"acf": {
						"mode": "edit",
						"postTypes": "%4",
						"renderTemplate": "index.php"
					},
					"custom_scripts": [ "/js/%1.min.js" ],
					"custom_styles": [ "/css/%1.min.css" ]
				}
				JSON;

	public string $name = '';

	/**
	 * Create new acf block
	 *
	 * @param array<int, string> $args
	 * @param array<string, mixed> $assoc_args
	 */
	public function add_block( $args, $assoc_args ): void {
		if ( empty( $args ) ) {
			WP_CLI::warning( 'No arguments' );
			die();
		}

		$name       = sanitize_title( str_replace( [ '-', '_' ], ' ', $args[0] ) );
		$this->name = $name;

		$folder = THEME_ACF_BLOCKS . '/' . $name;

		if ( is_dir( $folder ) ) {
			WP_CLI::warning( 'This block already exists' );
			die();
		}

		mkdir( $folder );

		$title      = ucfirst( str_replace( [ '-', '_' ], ' ', $args[0] ) );
		$category   = ( ! empty( $assoc_args['category'] ) && is_string( $assoc_args['category'] ) ) ? $assoc_args['category'] : 'sections';
		$post_types = ( ! empty( $assoc_args['post_types'] ) && is_string( $assoc_args['post_types'] ) ) ? $assoc_args['post_types'] : 'page,post';
		$post_types_json = json_encode( explode( ',', $post_types ) );

		$block_json = str_replace(
			[ '%1', '%2', '%3', '"%4"' ],
			[ $name, $title, $category, is_string( $post_types_json ) ? $post_types_json : '[]' ],
			$this->template_block_json );

		file_put_contents( $folder . '/block.json', $block_json );

		$this->create_image( $folder . '/preview.jpg' );

		if ( function_exists( 'acf_update_setting' ) && ! empty( $assoc_args['fields'] ) ) {
			$group_fields = $this->parse_group( $assoc_args['fields'], '', 'block' );
			if ( ! empty( $group_fields ) ) {
				acf_update_setting( 'save_json', $folder );

				$group_array = [
					'key'      => 'group_block_' . $name,
					'title'    => $title,
					'fields'   => $group_fields,
					'location' => array(
						array(
							array(
								'param'    => 'block',
								'operator' => '==',
								'value'    => 'acf/' . $name,
							),
						),
					),
				];

				acf_import_field_group( $group_array );
			}
		}

		WP_CLI::success( 'Create block ' . $title );
	}

	/**
	 * Create new template for page with ACF fields and page
	 *
	 * @param array<int, string> $args
	 * @param array<string, mixed> $assoc_args
	 */
	public function add_page( $args, $assoc_args ): void {
		if ( empty( $args ) ) {
			WP_CLI::warning( 'No arguments' );
			die();
		}

		$name       = sanitize_title( $args[0] );
		$this->name = $name;

		$folder = THEME_ACF_PAGES . '/' . $name;

		if ( is_dir( $folder ) ) {
			WP_CLI::warning( 'This page with this template already exists' );
			die();
		}

		mkdir( $folder );

		$title = ucfirst( str_replace( [ '-', '_' ], ' ', $args[0] ) );

		$content = <<<PHP
			<?php
			/**
			*
			* Template Name: {{template_name}}
			*/
			get_header();
			if ( have_posts() ) :
				while ( have_posts() ) : the_post();
					the_title();
					the_content();
					get_template_part('acf', 'pages/{{template_folder}}/index');
				endwhile;
			endif;
			get_footer();
			
			PHP;

		$content = str_replace( [ '{{template_name}}', '{{template_folder}}' ], [ $title, $name ], $content );

		file_put_contents( $folder . '/template.php', $content );

		// Create page with template
		wp_insert_post( array(
			'post_content'  => "",
			'post_title'    => $title,
			'post_name'     => $name,
			'post_type'     => 'page',
			'post_status'   => 'publish',
			'page_template' => 'acf-pages/' . $name . '/template.php'
		) );

		if ( function_exists( 'acf_update_setting' ) && ! empty( $assoc_args['fields'] ) ) {
			$group_fields = $this->parse_group( $assoc_args['fields'], '', 'page' );
			if ( ! empty( $group_fields ) ) {
				acf_update_setting( 'save_json', $folder );

				$group_array = [
					'key'      => 'group_page_' . $name,
					'title'    => $title,
					'fields'   => $group_fields,
					'location' => array(
						array(
							array(
								'param'    => 'page_template',
								'operator' => '==',
								'value'    => "acf-pages/{$name}/template.php",
							),
						),
					),
				];

				acf_import_field_group( $group_array );
			}
		}

		WP_CLI::success( 'Template ' . $title . 'was created ' );
	}

	/**
	 * @param int $length
	 */
	function group_code( $length = 13 ): string {
		$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
		$code       = '';
		for ( $i = 0; $i < $length; $i ++ ) {
			$code .= $characters[ rand( 0, strlen( $characters ) - 1 ) ];
		}

		return $code;
	}

	/**
	 * @param string $filename
	 */
	public function create_image( string $filename ): string {
		$image    = imagecreatetruecolor( 100, 100 );
		if ( false === $image ) {
			return $filename;
		}
		$bg_color = imagecolorallocate( $image, 236, 236, 241 );
		if ( false !== $bg_color ) {
			imagefill( $image, 0, 0, $bg_color );
		}
		imagejpeg( $image, $filename );
		imagedestroy( $image );

		return $filename;
	}

	/**
	 * @param string $data
	 * @param string $parent
	 * @param string $prefix
	 * @return array<int, mixed>
	 */
	public function parse_group( $data, $parent = '', $prefix = '' ): array {
		$pattern = '/(?:\[.*?\]|[^,])+/';
		preg_match_all( $pattern, $data, $matches );

		$array = [];
		foreach ( $matches[0] as $item ) {
			$array[] = $this->parse_field( $item, $parent, $prefix );
		}

		return $array;
	}
	/**
	 * @param string $item
	 * @param string $parent
	 * @param string $prefix
	 * @return array<string, mixed>
	 */
	public function parse_field( $item, $parent, $prefix = '' ): array {
		$field = explode( ':', $item, 2 );

		$array = [];

		if ( ! empty( $field ) ) {
			$name = $field[0];

			$array['key']   = 'field_' . $this->group_code();
			$array['label'] = ucfirst( str_replace( [ '-', '_' ], ' ', $name ) );
			$array['name']  = str_replace( '-', '_', sanitize_title( $prefix . '-' . $this->name . '-' . $name ) );

			if ( ! empty( $parent ) ) {
				$array['parent'] = $parent;
			}

			if ( count( $field ) == 1 ) {
				$array['type'] = 'text';
			} elseif ( count( $field ) == 2 ) {
				$delimiter_pos = strpos( $field[1], '[' );

				if ( $delimiter_pos !== false ) {
					$type      = sanitize_title( substr( $field[1], 0, $delimiter_pos ) );
					$list_text = substr( $field[1], $delimiter_pos + 1, - 1 );

					$array['type'] = $type;
					if ( $type == 'repeater' ) {
						$array['sub_fields'] = $this->parse_group( $list_text, $array['key'] );
					} elseif ( $type == 'select' || $type == 'checkbox' || $type == 'radio' ) {
						$array['choices'] = explode( ',', $list_text );
					}
				} else {
					$type          = sanitize_title( $field[1] );
					$array['type'] = $type;

					if ( $type == 'image' ) {
						$array['return_format'] = 'id';
						$array['preview_size']  = 'thumbnail';
					} else if ( $type == 'editor' ) {
						$array['type']         = 'wysiwyg';
						$array['tabs']         = 'all';
						$array['toolbar']      = 'basic';
						$array['media_upload'] = 0;
						$array['delay']        = 1;
					} else if ( $type == 'true_false' ) {
						$array['default_value'] = 'all';
						$array['ui_on_text']    = "";
						$array['ui_off_text']   = "";
						$array['ui']            = 1;
					} else if ( $type == 'true_false' ) {
						$array['return_format'] = "array";
					}
				}
			}
		}

		return $array;
	}


	/**
	 * @return void
	 */
	function create_site_archive(): void {
		$base_dir = ABSPATH;
		$temp_dir = $base_dir . 'temp_site_archive';
		$archive_name = 'site-archive.zip';
		$zip_file = $base_dir . $archive_name;
		$db_dump_file = $temp_dir . '/database.sql';
		$json_file = $temp_dir . '/wpmigrate-export.json';
		$files_dir = $temp_dir . '/files';

		$this->delete_file($zip_file);
		$this->delete_directory($temp_dir);

		if (!$this->create_directory($files_dir)) {
			WP_CLI::error('Failed to create temporary directory structure.');
			return;
		}

		$wp_content_subdirs = ['uploads', 'themes', 'plugins'];
		foreach ($wp_content_subdirs as $subdir) {
			$source = $base_dir . 'wp-content/' . $subdir;
			$destination = $files_dir . '/wp-content/' . $subdir;

			if (!$this->copy_directory_safely($source, $destination)) {
				continue;
			}
		}

		if (!$this->create_database_dump($db_dump_file)) {
			$this->cleanup_and_exit($temp_dir, 'Failed to create database dump.');
			return;
		}

		if (!$this->create_wpmigrate_json($json_file)) {
			$this->cleanup_and_exit($temp_dir, 'Failed to create wpmigrate-export.json.');
			return;
		}

		if (!$this->create_archive($temp_dir, $zip_file)) {
			$this->cleanup_and_exit($temp_dir, 'Failed to create archive.');
			return;
		}

		$this->delete_directory($temp_dir);
		WP_CLI::success("Archive {$archive_name} successfully created.");
	}

	/**
	 * @param string $file
	 * @return void
	 */
	protected function delete_file($file): void {
		if (file_exists($file) && !unlink($file)) {
			WP_CLI::warning("Failed to delete file: $file");
		}
	}

	/**
	 * @param string $directory
	 * @return bool
	 */
	protected function create_directory($directory): bool {
		return is_dir($directory) || mkdir($directory, 0755, true);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	protected function copy_directory_safely($source, $destination): bool {
		if (!is_dir($source)) {
			WP_CLI::warning("Source directory does not exist: $source");
			return false;
		}

		return $this->copy_directory($source, $destination);
	}

	/**
	 * @param string $source
	 * @param string $zip_file
	 * @return bool
	 */
	protected function create_archive($source, $zip_file): bool {
		$command = sprintf(
			'cd %s && zip -r %s .',
			escapeshellarg($source),
			escapeshellarg($zip_file)
		);
		exec($command, $output, $return_var);
		return $return_var === 0 && file_exists($zip_file);
	}

	/**
	 * @param string $temp_dir
	 * @param string $error_message
	 * @return void
	 */
	protected function cleanup_and_exit($temp_dir, $error_message): void {
		$this->delete_directory($temp_dir);
		WP_CLI::error($error_message);
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	protected function create_wpmigrate_json($file): bool {
		$services = $this->get_services();

		$data = [
			'name' => get_bloginfo('name'),
			'domain' => site_url(),
			'path' => ABSPATH,
			'wpVersion' => get_bloginfo('version'),
			'services' => $services,
		];

		return file_put_contents($file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)) !== false;
	}

	/**
	 * @param string $file
	 * @return bool
	 */
	protected function create_database_dump($file): bool {
		$host = DB_HOST;
		$user = DB_USER;
		$pass = DB_PASSWORD;
		$db_name = DB_NAME;

		$command = sprintf(
			'mysqldump -h %s -u %s -p%s %s > %s',
			escapeshellarg($host),
			escapeshellarg($user),
			escapeshellarg($pass),
			escapeshellarg($db_name),
			escapeshellarg($file)
		);

		exec($command, $output, $return_var);
		return $return_var === 0 && file_exists($file);
	}

	/**
	 * @param string $source
	 * @param string $destination
	 * @return bool
	 */
	protected function copy_directory($source, $destination): bool {
		if (!is_dir($destination)) {
			if (!mkdir($destination, 0755, true)) {
				WP_CLI::error("Failed to create destination directory: $destination");
				return false;
			}
		}

		$directory = opendir($source);
		if (false === $directory) {
			return false;
		}
		while (($file = readdir($directory)) !== false) {
			if ($file === '.' || $file === '..') {
				continue;
			}

			$sourcePath = $source . DIRECTORY_SEPARATOR . $file;
			$destinationPath = $destination . DIRECTORY_SEPARATOR . $file;

			if (is_dir($sourcePath)) {
				if (!$this->copy_directory($sourcePath, $destinationPath)) {
					WP_CLI::warning("Failed to copy directory: $sourcePath");
					closedir($directory);
					return false;
				}
			} else {
				if (!copy($sourcePath, $destinationPath)) {
					WP_CLI::warning("Failed to copy file: $sourcePath");
					closedir($directory);
					return false;
				}
			}
		}

		closedir($directory);
		return true;
	}

	/**
	 * @param string $directory
	 * @return bool
	 */
	protected function delete_directory($directory): bool {
		if (!is_dir($directory)) {
			return false;
		}

		$iterator = new RecursiveIteratorIterator(
			new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS),
			RecursiveIteratorIterator::CHILD_FIRST
		);

		foreach ($iterator as $item) {
			$func = $item->isDir() ? 'rmdir' : 'unlink';
			if (!$func($item)) {
				WP_CLI::warning("Failed to delete item: $item");
				return false;
			}
		}

		return rmdir($directory);
	}

	/**
	 * @return array<string, array<string, mixed>>
	 */
	protected function get_services(): array {
		global $wpdb;

		$services = [
			'php' => [
				'name' => 'php',
				'version' => function_exists('phpversion') ? phpversion() : '',
			],
		];

		return array_merge(
			$services,
			$this->get_db(mysqli_get_server_info($wpdb->dbh)),
			$this->get_server($_SERVER['SERVER_SOFTWARE'])
		);
	}

	/**
	 * @param string $mysql_version
	 * @return array<string, array<string, mixed>>
	 */
	protected function get_db($mysql_version): array {
		return [
			'mysql' => [
				'name' => 'mysql',
				'version' => $mysql_version,
			],
		];
	}

	/**
	 * @param string $server
	 * @return array<string, array<string, mixed>>
	 */
	protected function get_server($server): array {
		if (empty($server)) {
			return [];
		}

		$server_divided = explode('/', $server);
		$type = strtolower($server_divided[0]);
		$server_info = ['name' => $type];

		if (count($server_divided) > 1) {
			$after_type = explode(' ', $server_divided[1]);
			$version = reset($after_type);
			$server_info['version'] = $version;
		}

		return [$type => $server_info];
	}


}


/**
 * Initial custom command WP_CLI
 * Registers our command when cli get initialized.
 */
if ( class_exists( 'WP_CLI' ) ) {
	add_action( 'cli_init', function () {
		WP_CLI::add_command( 'general', 'GENERAL_CLI' );
	} );
}
