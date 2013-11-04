<?php

if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

require_once PATH_THIRD . 'mx_status_change/config.php';

/**
 *  MX MSM Themes Class for ExpressionEngine2
 *
 * @package  ExpressionEngine
 * @subpackage Plugins
 * @category Plugins
 * @author    Max Lazar <max@eec.ms>
 */

/* !TODO


*/

class Mx_status_change_ext {

	var $settings        = array();
	var $name   = MX_STATUS_CHANGE_NAME;
	var $version  = MX_STATUS_CHANGE_VER;
	var $description = MX_STATUS_CHANGE_DESC;
	var $settings_exist = 'n';
	var $docs_url  = MX_STATUS_CHANGE_DOCS;
	/**
	 * Defines the ExpressionEngine hooks that this extension will intercept.
	 *
	 * @since Version 1.0.1
	 * @access private
	 * @var mixed an array of strings that name defined hooks
	 * @see http://codeigniter.com/user_guide/general/hooks.html
	 * */

	private $hooks = array( 'entry_submission_ready' => 'entry_submission_ready' );

	// -------------------------------
	// Constructor
	// -------------------------------


	public function __construct( $settings=FALSE ) {

		$this->EE =& get_instance();

		if
		( isset( $this->EE->mx_core ) === FALSE ) {
			$this->EE->load->library( 'mx_core' );
		}

		$this->EE->mx_core->set_options( array( 'class' => __CLASS__, 'version' => MX_STATUS_CHANGE_VER ) );

		// define a constant for the current site_id rather than calling $PREFS->ini() all the time
		if
		( defined( 'SITE_ID' ) == FALSE )
			define( 'SITE_ID', $this->EE->config->item( 'site_id' ) );

		// set the settings for all other methods to access
		$this->settings = ( $settings == FALSE ) ? $this->EE->mx_core->_getSettings() : $this->EE->mx_core->_saveSettingsToSession( $settings );
	}


	/**
	 * Prepares and loads the settings form for display in the ExpressionEngine control panel.
	 *
	 * @since Version 1.0.0
	 * @access public
	 * @return void
	 * */
	public function settings_form() {

		$this->EE->lang->loadfile( 'mx_status_change' );

		$this->EE->load->model( 'site_model' );
		$this->EE->load->model( 'admin_model' );
		$this->EE->load->model('channel_model');
		$this->EE->load->model('status_model');

		// Create the variable array
		$vars = array(
			'addon_name' => MX_STATUS_CHANGE_NAME,
			'error' => FALSE,
			'input_prefix' => __CLASS__,
			'message' => FALSE,
			'settings_form' =>FALSE,
			'language_packs' => ''

		);

		$vars['channel_data'] = $this->EE->channel_model->get_channels()->result();
		$statuses = $this->EE->status_model->get_statuses();
		
		$vars['settings'] = $this->settings;
		$vars['settings_form'] = TRUE;
		$vars['site_data'] = $this->EE->site_model->get_site();

		if ( $new_settings = $this->EE->input->post( __CLASS__ ) ) {

			$vars['settings'] = $new_settings;

			$this->EE->mx_core->_saveSettingsToDB( $new_settings );

			$this->_ee_notice( $this->EE->lang->line( 'extension_settings_saved_success' ) );
		}

		return $this->EE->load->view( 'form_settings', $vars, true );

	}

	/**
	 * _ee_notice function.
	 *
	 * @access private
	 * @param mixed   $msg
	 * @return void
	 */
	function _ee_notice( $msg ) {
		$this->EE->javascript->output( array(
				'$.ee_notice("'.$this->EE->lang->line( $msg ).'",{type:"success",open:true});',
				'window.setTimeout(function(){$.ee_notice.destroy()}, 3000);'
			) );
	}


	// END


	/**
	 * sessions_end function.
	 *
	 * @access public
	 * @param mixed   $data
	 * @return void
	 */
	function entry_submission_ready( &$meta, &$data, $autosave ) {
		

		if (!$autosave) {	

			$status_prev = 0;

			if ($data['entry_id'] != 0) {

				$result = $this->EE->db->query("SELECT status
								FROM  exp_channel_titles
								WHERE  entry_id = '" . $data['entry_id'] . "'");

				foreach ($result->result_array() as $row)
				{
					$status_prev = $row['status'];
				}
			}

			if ($meta['status'] != $status_prev && $meta['status'] == "closed" ) 
			{

				$this->EE->api_channel_entries->meta['expiration_date'] = $this->EE->localize->now + 30*60*60*24; // 30 days +
			}

		}
		
		
		return;
	}

	// --------------------------------
	//  Activate Extension
	// --------------------------------

	function activate_extension() {
		$this->EE->mx_core->_createHooks( $this->hooks );
	}

	// END

	// --------------------------------
	//  Update Extension
	// --------------------------------

	function update_extension( $current='' ) {

		if ( $current == '' or $current == $this->version ) {
			return FALSE;
		}

		if ( $current < '2.0.1' ) {
			// Update to next version
		}

		$this->EE->db->query( "UPDATE exp_extensions SET version = '".$this->EE->db->escape_str( $this->version )."' WHERE class = '".get_class( $this )."'" );
	}
	// END

	// --------------------------------
	//  Disable Extension
	// --------------------------------

	function disable_extension() {

		$this->EE->db->delete( 'exp_extensions', array( 'class' => get_class( $this ) ) );
	}

	// END
}

/* End of file ext.mx_status_change.php */
/* Location: ./system/expressionengine/third_party/mx_status_change/ext.mx_status_change.php */
