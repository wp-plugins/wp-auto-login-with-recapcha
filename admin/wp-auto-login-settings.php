<?php 
/**
 * Handles the file upload
 *
 * @package     wp auto login setting
 * @subpackage  admin
 * @copyright   Copyright (c) 2015, Neil
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'Access Forbidden' );
}

class WP_AUTO_LOGIN_SETTINGS {

	private $_adminMenus = array();
	private $_autoLoginOptionNames = array('login_redirect_uri','auto_login');
	private $_registerOptionNames = array('password','recaptcha','sitekey','email_activation'
    	);
	public  $errors = array();
	public  $msgs = array();
	  	
	function __construct() {
		$this->neil_save_form_data();
		$this->neil_save_register_form_data();

		add_filter('admin_menu', array( $this , 'neil_auto_login_admin_menu' ));
	}

	/**
	 *
	 *	This function is used to create admin menus 
	 *	for auto login plugin
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function neil_auto_login_admin_menu(){ 
		$mainMenu = __( 'WP AUTO LOGIN' , TEXT_DOMAIN );
		add_menu_page( $mainMenu, $mainMenu , 'administrator', 'neil-auto-login-sub-page1', array( $this,'neil_auto_login_settings'));
	    $this->neil_init_admin_menu_values();
	    // filter for adding menus in DSP admin section
        //$adminMenuValues = apply_filters('dsp_add_submenu',$adminMenuValues);
        //dsp_debug($adminMenuValues);die;
        foreach ($this->adminMenus as $options) {
        	// Add a submenu to the custom top-level menu: add_submenu_page(parent, page_title, menu_title, capability required, file/handle, [function])
            add_submenu_page( $options['parent'], $options['page_title'], $options['menu_title'], $options['capability'], $options['handle'], $options['func']);
        }
	    
	}


	/**
	 *
	 *	This function is used to initialize admin menus values
	 *	for auto login plugin
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	
	public function neil_init_admin_menu_values ()
	{
		$this->adminMenus = array(
									array(
					                    'parent'     => 'neil-auto-login-sub-page1',
					                    'page_title' => __('Auto Login Setting',TEXT_DOMAIN),
					                    'menu_title' => __('Auto Login Setting',TEXT_DOMAIN), 
					                    'capability' => 'administrator',
					                    'handle'     => 'neil-auto-login-sub-page1', 
					                    'func'       => array( $this,'neil_auto_login_settings')
				                    ),
				                    array(
					                    'parent'     => 'neil-auto-login-sub-page1',
					                    'page_title' => __('Register Form Settings',TEXT_DOMAIN),
					                    'menu_title' => __('Register Form Settinss',TEXT_DOMAIN), 
					                    'capability' => 'administrator',
					                    'handle'     => 'neil-auto-login-sub-page2', 
					                    'func'       => array( $this,'neil_register_form_settings')
				                    )
		           		);
	}

	/**
	 *
	 *	This function is used to initialize admin menus 
	 *	for auto login plugin
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function neil_save_form_data(){ 
		if(empty($_POST) || !isset($_POST['auto-login']) || ( $_POST['auto-login'] != 'auto-login'))
			return false;
		$redirectUrl = isset($_POST['login_redirect_uri']) ? $_POST['login_redirect_uri'] :'';
		empty($redirectUrl) ? array_push( $this->errors , __('Empty redirect Url',TEXT_DOMAIN )) : null;
		if(!empty($this->errors) && count($this->errors) > 0 )
			return false;

		foreach ($this->_autoLoginOptionNames as  $optionName) {
			if ( get_option( $optionName ) !== false ) {

			    // The option already exists, so we just update it.
			    update_option( $optionName, $_POST[$optionName] );

			} else {

			    // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			    $deprecated = null;
			    $autoload = 'no';
			    add_option( $optionName, $_POST[$optionName] , $deprecated, $autoload );
			}

			$this->msgs[] = __( 'Successfully Saved' , TEXT_DOMAIN );
		}
	}


	/**
	 *
	 *	This function is used for passing data to view
	 *	for auto login form
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function neil_auto_login_settings ()
	{
		$data = array  (
						'redirectUrl' => get_option('login_redirect_uri'),
						'autoLogin' => get_option('auto_login'),
						'selectOptions' => array (
													'Y' => __( 'ON', TEXT_DOMAIN ),
													'N' => __( 'OFF', TEXT_DOMAIN ),
											),
						'msgs'	=>  $this->msgs,
						'errors'=>	$this->errors

					);
		$this->view('auto-login-form-setting',$data);
	}


	/**
	 *
	 *	This function is used to save register form data to option page
	 *	for auto login plugin
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function neil_save_register_form_data(){ 
		if(empty($_POST) || !isset($_POST['register-password-field']) || ( $_POST['register-password-field'] != 'true'))
			return false;
		
		foreach ($this->_registerOptionNames as  $optionName) {
			if(!array_key_exists( $optionName , $_POST))
				continue;
			if ( get_option( $optionName ) !== false ) {

			    // The option already exists, so we just update it.
			    update_option( $optionName, $_POST[$optionName] );

			} else {

			    // The option hasn't been added yet. We'll add it with $autoload set to 'no'.
			    $deprecated = null;
			    $autoload = 'no';
			    add_option( $optionName, $_POST[$optionName] , $deprecated, $autoload );
			}

			$this->msgs[] = __( 'Successfully Saved' , TEXT_DOMAIN );
		}
	}


	/**
	 *
	 *	This method is used to pass value into view
	 *	for register form
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */


	public function neil_register_form_settings ()
	{
		$data = array  (
						'password' => get_option('password'),
						'recaptcha' => get_option('recaptcha'),
						'sitekey' => get_option('sitekey'),
						'emailActivation' => get_option('email_activation'),
						'selectOptions' => array (
													'Y' => __( 'ON', TEXT_DOMAIN ),
													'N' => __( 'OFF', TEXT_DOMAIN ),
											),
						'msgs'	=>  $this->msgs,
						'errors'=>	$this->errors


					);
		$this->view('register-form-setting',$data);
	}


	/**
	 *
	 *	This is generic method to include file in view folder
	 *	for register form
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function view ($filename,$data = array())
	{	
		extract($data);
		include_once( PLUGIN_DIR_PATH . '/admin/views/' . $filename .'.php');
	}

	
}

// global variable file handler
$GLOBALS['autoLoginSettings']  = new WP_AUTO_LOGIN_SETTINGS();