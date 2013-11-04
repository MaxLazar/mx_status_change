<?php  if ( ! defined( 'BASEPATH' ) ) exit( 'No direct script access allowed' );

/**
 *  Core Class for ExpressionEngine2
 *
 * @package  ExpressionEngine
 * @subpackage Plugins
 * @category Plugins
 * @author    Max Lazar <max@eec.ms>
 */

class Mx_core {
	var $class;
	var $version;

	public function __construct() {

		$this->EE =& get_instance();

		if ( defined( 'SITE_ID' ) == FALSE )
			define( 'SITE_ID', $this->EE->config->item( 'site_id' ) );

	}

	public function set_options( $props ) {
		$this->class = $props['class'];
		$this->version = $props['version'];
	}

	/**
	 * Saves the specified settings array to the database.
	 *
	 * @since Version 1.0.0
	 * @access protected
	 * @param array   $settings an array of settings to save to the database.
	 * @return void
	 * */
	public function _saveSettingsToDB( $settings ) {
		$this->EE->db->where( 'class', $this->class )
		->update( 'extensions', array( 'settings' => serialize( $settings ) ) );
	}

	/**
	 * Saves the specified settings array to the session.
	 *
	 * @since Version 1.0.0
	 * @access protected
	 * @param array   $settings an array of settings to save to the session.
	 * @param array   $sess     A session object
	 * @return array the provided settings array
	 * */
	public function _saveSettingsToSession( $settings, &$sess = FALSE ) {
		// if there is no $sess passed and EE's session is not instaniated
		if
		( $sess == FALSE && isset( $this->EE->session->cache ) == FALSE )
			return $settings;

		// if there is an EE session available and there is no custom session object
		if
		( $sess == FALSE && isset( $this->EE->session ) == TRUE )
			$sess =& $this->EE->session;

		// Set the settings in the cache
		$sess->cache[$this->class]['settings'] = $settings;

		// return the settings
		return $settings;
	}

	/**
	 * Sets up and subscribes to the hooks specified by the $hooks array.
	 *
	 * @since Version 1.0.0
	 * @access public
	 * @param array   $hooks a flat array containing the names of any hooks that this extension subscribes to. By default, this parameter is set to FALSE.
	 * @return void
	 * @see http://codeigniter.com/user_guide/general/hooks.html
	 * */
	public function _createHooks( $hooks = FALSE ) {
		if ( !$hooks ) {
			$hooks = $this->hooks;
		}

		$hook_template = array(
			'class' => $this->class,
			'settings' =>'',
			'version' => $this->version,
		);

		foreach ( $hooks as $key => $hook ) {
			if ( is_array( $hook ) ) {
				$data['hook'] = $key;
				$data['method'] = ( isset( $hook['method'] ) === TRUE ) ? $hook['method'] : $key;
				$data = array_merge( $data, $hook );
			}
			else {
				$data['hook'] = $data['method'] = $hook;
			}

			$hook = array_merge( $hook_template, $data );
			$hook['settings'] = serialize( $hook['settings'] );
			$this->EE->db->query( $this->EE->db->insert_string( 'exp_extensions', $hook ) );
		}
	}

	/**
	 * Removes all subscribed hooks for the current extension.
	 *
	 * @since Version 1.0.0
	 * @access public
	 * @return void
	 * @see http://codeigniter.com/user_guide/general/hooks.html
	 * */
	public function _deleteHooks( ) {
		$this->EE->db->query( "DELETE FROM `exp_extensions` WHERE `class` = '".$this->class."'" );
	}

	/**
	 * Saves the specified settings array to the database.
	 *
	 * @since Version 1.0.0
	 * @access protected
	 * @param array   $settings an array of settings to save to the database.
	 * @return void
	 * */
	public function _getSettings( $refresh = FALSE ) {
		$settings = FALSE;
		if
		( isset( $this->EE->session->cache[$this->class]['settings'] ) === FALSE || $refresh === TRUE ) {
			$settings_query = $this->EE->db->select( 'settings' )
			->where( 'enabled', 'y' )
			->where( 'class', $this->class )
			->get( 'extensions', 1 );

			if
			( $settings_query->num_rows() ) {
				$settings = unserialize( $settings_query->row()->settings );
				$this->_saveSettingsToSession( $settings );
			}
		}
		else {
			$settings = $this->EE->session->cache[$this->class]['settings'];
		}
		return $settings;
	}

}
?>
