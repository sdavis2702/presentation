<?php
/**
 * theme licensing with Easy Digital Downloads and the Software
 * Licensing extension: 
 *
 * http://buildwpyourself.com/edd-software-licensing
 */

define( 'PRESENTATION_SL_STORE_URL', 'http://buildwpyourself.com' );
define( 'PRESENTATION_DOWNLOAD_TITLE', 'Presentation' );


/***********************************************
* updater class
***********************************************/

class PRESENTATION_SL_Theme_Updater {
	private $remote_api_url;
	private $request_data;
	private $response_key;
	private $theme_slug;
	private $license_key;
	private $version;
	private $author;

	function __construct( $args = array() ) {
		$args = wp_parse_args( $args, array(
			'remote_api_url' => 'http://easydigitaldownloads.com',
			'request_data'   => array(),
			'theme_slug'     => get_stylesheet(),
			'item_name'      => '',
			'license'        => '',
			'version'        => '',
			'author'         => ''
		) );
		extract( $args );

		$theme                = wp_get_theme( sanitize_key( $theme_slug ) );
		$this->license        = $license;
		$this->item_name      = $item_name;
		$this->version        = ! empty( $version ) ? $version : $theme->get( 'Version' );
		$this->theme_slug     = sanitize_key( $theme_slug );
		$this->author         = $author;
		$this->remote_api_url = $remote_api_url;
		$this->response_key   = $this->theme_slug . '-update-response';


		add_filter( 'site_transient_update_themes', array( &$this, 'theme_update_transient' ) );
		add_filter( 'delete_site_transient_update_themes', array( &$this, 'delete_theme_update_transient' ) );
		add_action( 'load-update-core.php', array( &$this, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( &$this, 'delete_theme_update_transient' ) );
		add_action( 'load-themes.php', array( &$this, 'load_themes_screen' ) );
	}

	function load_themes_screen() {
		add_thickbox();
		add_action( 'admin_notices', array( &$this, 'update_nag' ) );
	}

	function update_nag() {
		$theme = wp_get_theme( $this->theme_slug );

		$api_response = get_transient( $this->response_key );

		if( false === $api_response )
			return;

		$update_url = wp_nonce_url( 'update.php?action=upgrade-theme&amp;theme=' . urlencode( $this->theme_slug ), 'upgrade-theme_' . $this->theme_slug );
		$update_onclick = ' onclick="if ( confirm(\'' . esc_js( __( "Updating this theme will lose any customizations you have made. 'Cancel' to stop, 'OK' to update." ) ) . '\') ) {return true;}return false;"';

		if ( version_compare( $this->version, $api_response->new_version, '<' ) ) {

			echo '<div id="update-nag">';
				printf( '<strong>%1$s %2$s</strong> is available. <a href="%3$s" class="thickbox" title="%4s">Check out what\'s new</a> or <a href="%5$s"%6$s>update now</a>.',
					$theme->get( 'Name' ),
					$api_response->new_version,
					'#TB_inline?width=640&amp;inlineId=' . $this->theme_slug . '_changelog',
					$theme->get( 'Name' ),
					$update_url,
					$update_onclick
				);
			echo '</div>';
			echo '<div id="' . $this->theme_slug . '_' . 'changelog" style="display:none;">';
				echo wpautop( $api_response->sections['changelog'] );
			echo '</div>';
		}
	}

	function theme_update_transient( $value ) {
		$update_data = $this->check_for_update();
		if ( $update_data ) {
			$value->response[ $this->theme_slug ] = $update_data;
		}
		return $value;
	}

	function delete_theme_update_transient() {
		delete_transient( $this->response_key );
	}

	function check_for_update() {

		$theme = wp_get_theme( $this->theme_slug );

		$update_data = get_transient( $this->response_key );
		if ( false === $update_data ) {
			$failed = false;

			$api_params = array(
				'edd_action' 	=> 'get_version',
				'license' 		=> $this->license,
				'name' 			=> $this->item_name,
				'slug' 			=> $this->theme_slug,
				'author'		=> $this->author
			);

			$response = wp_remote_post( $this->remote_api_url, array( 'timeout' => 15, 'sslverify' => false, 'body' => $api_params ) );

			// make sure the response was successful
			if ( is_wp_error( $response ) || 200 != wp_remote_retrieve_response_code( $response ) ) {
				$failed = true;
			}

			$update_data = json_decode( wp_remote_retrieve_body( $response ) );

			if ( ! is_object( $update_data ) ) {
				$failed = true;
			}

			// if the response failed, try again in 30 minutes
			if ( $failed ) {
				$data = new stdClass;
				$data->new_version = $this->version;
				set_transient( $this->response_key, $data, strtotime( '+30 minutes' ) );
				return false;
			}

			// if the status is 'ok', return the update arguments
			if ( ! $failed ) {
				$update_data->sections = maybe_unserialize( $update_data->sections );
				set_transient( $this->response_key, $update_data, strtotime( '+12 hours' ) );
			}
		}

		if ( version_compare( $this->version, $update_data->new_version, '>=' ) ) {
			return false;
		}

		return (array) $update_data;
	}
}


/***********************************************
* the updater
***********************************************/

$presentation_license = trim( get_option( 'presentation_license_key' ) );

$edd_updater = new PRESENTATION_SL_Theme_Updater( array( 
		'remote_api_url' 	=> PRESENTATION_SL_STORE_URL,
		'version' 			=> PRESENTATION_VERSION,
		'license' 			=> $presentation_license,
		'item_name' 		=> PRESENTATION_DOWNLOAD_TITLE,
		'author'			=> PRESENTATION_AUTHOR
	)
);


/***********************************************
* add menu item
***********************************************/

function presentation_license_menu() {
	add_theme_page( PRESENTATION_NAME, PRESENTATION_NAME, 'manage_options', 'presentation-license', 'presentation_license_page' );
}
add_action('admin_menu', 'presentation_license_menu');


/***********************************************
* theme settings page
***********************************************/

function presentation_license_page() {
	$license 	= get_option( 'presentation_license_key' );
	$status 	= get_option( 'presentation_license_key_status' );
	?>
	<div class="wrap">
		<h2><?php echo PRESENTATION_NAME . __( ' Settings', 'presentation' ); ?></h2>
		<form method="post" action="options.php">
		
			<?php settings_fields('presentation_license'); ?>
			
			<table class="form-table">
				<tbody>
					<tr valign="top">	
						<th scope="row" valign="top">
							<?php _e('License Key', 'presentation'); ?>
						</th>
						<td>
							<input id="presentation_license_key" name="presentation_license_key" type="text" class="regular-text" value="<?php esc_attr_e( $license ); ?>" />
							<label for="presentation_license_key"><?php _e( 'Enter your license key', 'presentation' ); ?></label>
						</td>
					</tr>
					<?php if( false !== $license ) { ?>
						<tr valign="top">	
							<th scope="row" valign="top">
								<?php _e('Activate License', 'presentation'); ?>
							</th>
							<td>
								<?php if( $status !== false && $status == 'valid' ) { ?>
									<span style="color:green;"><?php _e('active', 'presentation'); ?></span>
									<?php wp_nonce_field( 'presentation_nonce', 'presentation_nonce' ); ?>
									<input type="submit" class="button-secondary" name="presentation_license_deactivate" value="<?php _e('Deactivate License', 'presentation'); ?>"/>
								<?php } else {
									wp_nonce_field( 'presentation_nonce', 'presentation_nonce' ); ?>
									<input type="submit" class="button-secondary" name="presentation_license_activate" value="<?php _e('Activate License', 'presentation'); ?>"/>
								<?php } ?>
							</td>
						</tr>
					<?php } ?>
				</tbody>
			</table>	
			<?php submit_button(); ?>
		
		</form>
	<?php
}

function presentation_register_option() {
	// settings page license option
	register_setting('presentation_license', 'presentation_license_key', 'presentation_sanitize_license' );
}
add_action('admin_init', 'presentation_register_option');


/***********************************************
* get rid of the local license status option
* when adding a new one
***********************************************/

function presentation_sanitize_license( $new ) {
	$old = get_option( 'presentation_license_key' );
	if( $old && $old != $new ) {
		delete_option( 'presentation_license_key_status' ); // new license has been entered, so must reactivate
	}
	return $new;
}


/***********************************************
* activate license key
***********************************************/

function presentation_activate_license() {

	if( isset( $_POST['presentation_license_activate'] ) ) { 
	 	if( ! check_admin_referer( 'presentation_nonce', 'presentation_nonce' ) ) 	
			return; // get out if we didn't click the Activate button

		global $wp_version;

		$license = trim( get_option( 'presentation_license_key' ) );

		$api_params = array( 
			'edd_action' => 'activate_license', 
			'license' => $license, 
			'item_name' => urlencode( PRESENTATION_DOWNLOAD_TITLE ) 
		);

		$response = wp_remote_get( add_query_arg( $api_params, PRESENTATION_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		if ( is_wp_error( $response ) )
			return false;

		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "active" or "inactive"

		update_option( 'presentation_license_key_status', $license_data->license );

	}
}
add_action('admin_init', 'presentation_activate_license');


/***********************************************
* deactivate license key
***********************************************/

function presentation_deactivate_license() {

	// listen for our activate button to be clicked
	if( isset( $_POST['presentation_license_deactivate'] ) ) {

		// run a quick security check 
	 	if( ! check_admin_referer( 'presentation_nonce', 'presentation_nonce' ) ) 	
			return; // get out if we didn't click the Activate button

		// retrieve the license from the database
		$license = trim( get_option( 'presentation_license_key' ) );


		// data to send in our API request
		$api_params = array( 
			'edd_action'=> 'deactivate_license', 
			'license' 	=> $license, 
			'item_name' => urlencode( PRESENTATION_DOWNLOAD_TITLE ) // the name of our product in EDD
		);

		// Call the custom API.
		$response = wp_remote_get( add_query_arg( $api_params, PRESENTATION_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

		// make sure the response came back okay
		if ( is_wp_error( $response ) )
			return false;

		// decode the license data
		$license_data = json_decode( wp_remote_retrieve_body( $response ) );

		// $license_data->license will be either "deactivated" or "failed"
		if( $license_data->license == 'deactivated' )
			delete_option( 'presentation_license_key' );

	}
}
add_action('admin_init', 'presentation_deactivate_license');


/***********************************************
* is license valid?
***********************************************/

function presentation_check_license() {

	global $wp_version;

	$license = trim( get_option( 'presentation_license_key' ) );

	$api_params = array( 
		'edd_action' => 'check_license', 
		'license' => $license, 
		'item_name' => urlencode( PRESENTATION_DOWNLOAD_TITLE ) 
	);

	$response = wp_remote_get( add_query_arg( $api_params, PRESENTATION_SL_STORE_URL ), array( 'timeout' => 15, 'sslverify' => false ) );

	if ( is_wp_error( $response ) )
		return false;

	$license_data = json_decode( wp_remote_retrieve_body( $response ) );

	if( $license_data->license == 'valid' ) {
		echo 'valid'; exit;
		// this license is still valid
	} else {
		echo 'invalid'; exit;
		// this license is no longer valid
	}
}