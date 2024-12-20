<?php
/**
 * Plugin Name: WP CLI CDN Command
 * Description: A custom WP CLI command to interact with CDN using s3cmd.
 * Version: 1.1.0
 * Author: Your Name
 */

if ( ! defined( 'WP_CLI' ) || ! WP_CLI ) {
    return;
}

class WP_CLI_CDN_Command {

    private $cdn_bucket;

    public function __construct() {
        $this->cdn_bucket = get_option( 'wp_cdn_bucket', false );
    }

    /**
     * Validates if the CDN bucket is configured.
     */
    private function validate_cdn_bucket() {
        if ( ! $this->cdn_bucket ) {
            WP_CLI::error( 'CDN bucket is not configured. Run `wp cdn configure` to set it up.' );
        }
    }

    /**
     * Custom method to prompt user input for CLI.
     */
    private function prompt_user( $message ) {
        fwrite( STDOUT, "$message: " );
        return trim( fgets( STDIN ) );
    }

    /**
     * Runs a quick diagnostic to determine if the system is properly configured.
     *
     * ## EXAMPLES
     *
     *     wp cdn status
     *
     * @when after_wp_load
     */
    public function status() {
        $this->validate_cdn_bucket();

        // Check if s3cmd is installed.
        exec( 'command -v s3cmd', $output, $return_var );
        if ( $return_var !== 0 ) {
            WP_CLI::error( 's3cmd is not installed. Please install it and try again.' );
        }

        // Check if s3cmd has access to the cloud storage.
        exec( 's3cmd ls', $output, $return_var );
        if ( $return_var !== 0 ) {
            WP_CLI::error( 's3cmd is installed but cannot access cloud storage. Please check your configuration.' );
        }

        WP_CLI::success( 's3cmd is properly installed and configured.' );
    }

    /**
     * Configures the CDN bucket.
     *
     * ## EXAMPLES
     *
     *     wp cdn configure
     *
     * @when after_wp_load
     */
    public function configure() {
        $bucket = $this->prompt_user( 'Enter your CDN bucket name' );
        update_option( 'wp_cdn_bucket', $bucket );
        $this->cdn_bucket = $bucket; // Update the property after configuration
        WP_CLI::success( 'CDN bucket configured successfully.' );
    }

    /**
     * Uploads a local folder to the remote CDN.
     *
     * ## OPTIONS
     *
     * <folder>
     * : The folder under /wp-content/uploads to upload to the CDN.
     *
     * ## EXAMPLES
     *
     *     wp cdn put <folder>
     *
     * @when after_wp_load
     */
    public function put( $args, $assoc_args ) {
        $this->validate_cdn_bucket();
        list( $folder ) = $args;
        $local_path = WP_CONTENT_DIR . "/uploads/" . $folder;

        if ( ! is_dir( $local_path ) ) {
            WP_CLI::error( "The folder '{$folder}' does not exist in /wp-content/uploads." );
        }

        $remote_path = "s3://{$this->cdn_bucket}/uploads/{$folder}";
        $command = "s3cmd put {$local_path}/ {$remote_path} --recursive --acl-public";
        exec( $command, $output, $return_var );

        if ( $return_var !== 0 ) {
            WP_CLI::error( 'Failed to upload folder to the CDN. Please check your s3cmd configuration.' );
        }

        WP_CLI::success( "Folder '{$folder}' successfully uploaded to the CDN." );
    }

    /**
     * Downloads a remote folder from the CDN to the local system.
     *
     * ## OPTIONS
     *
     * <folder>
     * : The folder under /wp-content/uploads to download from the CDN.
     *
     * ## EXAMPLES
     *
     *     wp cdn get <folder>
     *
     * @when after_wp_load
     */
    public function get( $args, $assoc_args ) {
        $this->validate_cdn_bucket();
        list( $folder ) = $args;
        $local_path = WP_CONTENT_DIR . "/uploads/" . $folder;
        $remote_path = "s3://{$this->cdn_bucket}/uploads/{$folder}";

        $command = "s3cmd sync {$remote_path}/ {$local_path}";
        exec( $command, $output, $return_var );

        if ( $return_var !== 0 ) {
            WP_CLI::error( 'Failed to download folder from the CDN. Please check your s3cmd configuration.' );
        }

        WP_CLI::success( "Folder '{$folder}' successfully downloaded from the CDN." );
    }
}

WP_CLI::add_command( 'cdn', 'WP_CLI_CDN_Command' );
