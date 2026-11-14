<?php
/**
 * Class WP_Logger
 *
 * Creates a folder "/wp-content/logs/" and puts your log in there.
 * Auto-cleaning of files older than three months works.
 *
 * Example: `WP_Logger( $var, '1.log' );`
 */

class WP_Logger {

	private string $file = '';
	public string $path = WP_CONTENT_DIR . '/logs';
	public string $filename = 'log.log';

	public function __construct( mixed $data, string $filename = '' ) {

		if ( $filename ) {
			$this->filename = $filename;
		}

		$this->file = $this->path . '/' . $this->filename;

		if ( is_array( $data ) || is_object( $data ) ) {
			$data = print_r( $data, true );
		}

		$this->create_logs_folder();
		$this->cleanup_logs();

		error_log( date( 'Y-m-d H:i:s' ) . PHP_EOL, 3, $this->file );
		error_log( $data . PHP_EOL, 3, $this->file );
	}

	private function create_logs_folder() : void {
		if ( ! is_dir( $this->path ) ) {
			mkdir( $this->path );

			$htaccess_content = "Deny from all";
			file_put_contents( $this->path . '/.htaccess', $htaccess_content );
		}
	}

	private function cleanup_logs() : void {
		if ( file_exists( $this->file ) ) {
			$file_creation_time = filectime( $this->file );
			$current_time       = time();
			$three_months       = 3 * 30 * 24 * 60 * 60;

			if ( $current_time - $file_creation_time >= $three_months ) {
				unlink( $this->file );
			}
		}
	}
}

if ( ! function_exists( 'WP_Logger' ) ) {
	function WP_Logger( mixed $data, string $filename = '' ) : void {
		new WP_Logger( $data, $filename );
	}
}