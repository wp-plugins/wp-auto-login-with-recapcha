<?php
/**
 *
 * Plugin Name: WP Auto Login With Recaptcha
 * Plugin URI: http://www.wordpress.org/plugins/wp-auto-login-with-recaptcha
 * Description: Easily login & redirect after user register to desired location.Also, integrate recaptcha with password field &  email verification
 * Version: 1.0.0
 * Author: Neil Gurung
 * Author URI: http://www.neil.com.np/
 * Text Domain:autoLogin
 * 
 */

if ( ! defined( 'ABSPATH' ) ) {
	header( 'Status: 403 Forbidden' );
	header( 'HTTP/1.1 403 Forbidden' );
	die( 'Access Forbidden' );
}

// Plugin Text Domain
!defined('TEXT_DOMAIN') ? define('TEXT_DOMAIN', 'autoLogin' ) : null;
// Plugin dir path
!defined('PLUGIN_DIR_PATH') ? define('PLUGIN_DIR_PATH', plugin_dir_path(__FILE__) ) : null;


include_once( ABSPATH . 'wp-admin/includes/plugin.php' );

/**
 * Handles the file handling for image uploader
 *
 * @package     wp-auto-login
 * @copyright   Copyright (c) 2015, Neil
 * @license     http://opensource.org/licenses/gpl-2.0.php GNU Public License
 * @since       1.0
*/

class WP_AUTO_LOGIN_WITH_RECAPTCHA 
{
	private $_sitekey = '';
	public  $redirectUrl = '';
	public  $passwordModule = '';
	public  $recaptchaModule = '';
	/**
     * A reference to an instance of this class.
     */
	public static $instance;


	 /**
     * Returns an instance of this class. 
     */
	 public static function get_instance() {
	 	if( null == self::$instance ) {
	 		self::$instance = new WP_AUTO_LOGIN_WITH_RECAPTCHA();
	 	} 

	 	return self::$instance;

	 } 

    /**
	 * 
	 * This is the constructor which is used to intialize all the attributes & call 
	 * necessary methods to automatically login after register users
	 */

    public function __construct()
    {

    	$this->redirectUrl   = is_null(get_option('login_redirect_uri')) ? site_url() : get_option('login_redirect_uri');
    	
    	// include files for WP LOGIN ADMIN  SETTING
    	$this->_include_files();

		//load google script
    	add_action( 'init', array( $this , 'neil_load_scripts' ) );

    	//load style sheets
    	add_action( 'init', array( $this , 'neil_load_styles' ) );

    	$this->_hook_call_for_autoLogin();

    	$this->_hook_call_for_passwordModule();

    	$this->_hook_call_for_recaptchaModule();
	    
	    $this->_create_shortCodes();
    	
    }

    /**
     * 
	 *	This method is used to create shortcode for include form 
	 *	for frontend Image uploader. It can be used by :
	 *	[neil_upload]
	 *   @access private
	 *   @author Neil
	 *   @since 1.0
	 *   @return void
	 */

    private function _include_files() { 
		
		include_once( PLUGIN_DIR_PATH . 'admin/wp-auto-login-settings.php' );
	}

	
	/**
	 *
	 *	This is static method used to load scripts required
	 * 	for  auto login plugin
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	

	function neil_load_scripts() { 
		wp_enqueue_script('jquery');
		wp_enqueue_script('recaptcha-script-js', 'https://www.google.com/recaptcha/api.js' );
		if(is_admin())
			wp_enqueue_script('custom-admin-js', plugins_url('assets/js/custom-admin.js',__FILE__),array('jquery') );
	}


	/**
	*
	* This method is used to enqueue all styles
	*  @access public
	*  @since 1.0
	*  @return void
	*/

	public function neil_load_styles() { 
		// tell WordPress to load the Smoothness theme from Google CDN
	    wp_enqueue_style('custom-style-css',  plugins_url('assets/css/style.css',__FILE__) , array(), '1.0.0', false);
		
	}


	/**
	 *
	 *	This is method used to function used to call all hook for auto login
	 * 	after user registered
	 * 	@access private
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	private function _hook_call_for_autoLogin()
	{ 
		$autoLoginStatus = get_option('auto_login');
		$autoLoginStatus = !is_null($autoLoginStatus) && $autoLoginStatus == 'Y' ? true : false ;
		if($autoLoginStatus) 
		{
			add_action('init', array($this,'neil_start_output_buffer' ));
			//add_action('user_register', array($this,'neil_validate_password'),7);
	    	add_action('user_register', array($this,'neil_auto_login_after_register'),8);
	    	add_action('wp_footer', array($this,'neil_end_output_buffer' ));
	    }
    	
	}


	/**
	 *
	 *	This is method used to function used to call all hook for auto login
	 * 	after user registered
	 * 	@access private
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	private function _hook_call_for_passwordModule()
	{ 
		$pswStatus = get_option('password');
		$this->passwordModule = !is_null($pswStatus[0]) && $pswStatus[0] == 'Y' ? true : false ;
		
		if($this->passwordModule)
    	{
    		add_action('register_form', array($this,'neil_add_password_field'),9);
	    	add_action('register_post' , array($this,'neil_validate_password'),7,3);
	    	add_filter('wp_authenticate_user',array($this,'neil_authenticate_user_login'));
    		add_filter( 'gettext', array( $this,'neil_edit_password_email_text' ));
	    }
    	
	}

	/**
	 *
	 *	This method is used to call all hook for intregating
	 * 	recaptcha module
	 * 	@access private
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	private function _hook_call_for_recaptchaModule()
	{ 
		$recaptchaStatus = get_option('recaptcha');
    	$this->_sitekey = get_option('sitekey');
    	$this->recaptchaModule = !is_null($recaptchaStatus[0]) && $recaptchaStatus[0] == 'Y' && !empty($this->_sitekey[0]) ? true : false ;
		
		if($this->recaptchaModule)
    	{
	       	add_action('register_post' , array($this,'neil_recaptcha_validate'),7,3);
	       	add_action('register_form', array($this,'neil_recapcha_integrate'),9);
	    }
    	
	}



	/**
	 *
	 *	This method is  used to create two shortcodes for
	 * 	integrating recapcha ([recapcha]) & password ([password])
	 * 	field to other form
	 * 	@access private
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	private function _create_shortCodes()
	{ 
		
		add_shortcode('password', array($this,'neil_add_password_field'),9);
    	add_shortcode('recapcha' , array($this,'neil_recapcha_integrate'),7,3);
    }


	/**
	 *
	 *	This function  is used  to auto login user 
	 * 	after user register
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	

	public function neil_auto_login_after_register($user_id) 
	{

		$userdata = array();
		$userdata['ID'] = $user_id;
		if ( $_POST['password'] !== '' ) {
			$userdata['user_pass'] = $_POST['password'];
		}
		$new_user_id = wp_update_user( $userdata );

			//do_action('neil_send_activation_link' , $user_id);
		if($acivationModule && $passwordModule){
			$this->neil_send_activation_link($userdata);
			$isActivated = get_user_meta($user_id,'_dsp_confirm');
			$isActivated =  $isActivated[0];
				//var_dump($isActivated);die;
			if(is_null($isActivated) || $isActivated == 'false' ){
		    			$user = new WP_Error( 'denied', __("<strong>ERROR</strong>: You need to activate your account.".$value."") );//create an error
		        	    remove_action('authenticate', 'wp_authenticate_username_password', 20); //key found - don't proceed!
		        	    return false;
		    }
		}
    	wp_set_current_user($user_id);
    	wp_set_auth_cookie($user_id);
     	// You can change home_url() to the specific URL,such as wp_redirect( 'http://www.wpcoke.com' );
    	wp_redirect( $this->redirectUrl );
    	exit;

    }

	
	/**
	 *
	 *	This function is used to start output buffering during 
	 *	wordpress initilize
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	

	public function neil_start_output_buffer() {
		ob_start();
	}


    /**
	 *
	 *	This function is used to end & flush output buffering
	 *	wordpress initilize
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	
	 
	public function neil_end_output_buffer() {
    	ob_end_flush();
    }


	/**
	 *
	 *	This is filter used to add the password field in  user 
	 *	registration page
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

	public function neil_add_password_field() { 
		?>
		<p>
			<label for="password"><?php echo __('Password',TEXT_DOMAIN) ?><br/>
				<input id="password" class="input" type="password" tabindex="30" size="25" value="" name="password" />
			</label>
		</p>
		<p>
			<label for="repeat_password"><?php echo __('Repeat password',TEXT_DOMAIN) ?><br/>
				<input id="repeat_password" class="input" type="password" tabindex="40" size="25" value="" name="repeat_password" />
			</label>
		</p>
		<?php
	}


	/**
	 *
	 *	This method is used to display recaptcha module
	 *	worpdress registration section
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */


	function neil_recapcha_integrate() { 
		?>
		<p>
			<label for="are_you_human" style="font-size:11px"><?php echo __('Sorry, but we must check if you are human. What is the name of website you are registering for?',TEXT_DOMAIN) ?><br/>
				<div class="g-recaptcha" data-sitekey="<?php echo $this->_sitekey;?>"></div>
			</label>
		</p>
		<?php

	}


    /**
	 *
	 *	This is filter used to validate the password field in  user 
	 *	registration page
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return HTML formatted output to show password field in the registration form
	 * 	
	 */
	
	public function neil_validate_password($login, $email, $errors){
		if ( $_POST['password'] !== $_POST['repeat_password'] ) {
			$errors->add( 'passwords_not_matched', "<strong>ERROR</strong>: Passwords must match" );
		}
		if ( strlen( $_POST['password'] ) < 8 ) {
			$errors->add( 'password_too_short', "<strong>ERROR</strong>: Passwords must be at least eight characters long" );
		}
		
		
	}

    /**
	 *
	 *	This is function used to validate the recaptcha module
	 *	registration page
	 *	@param Array
	 *	@param String
	 *	@param Array  $errors
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	
	public function neil_recaptcha_validate($login, $email, $errors)
	{
		if(isset($_POST['g-recaptcha-response']))
			$captcha=$_POST['g-recaptcha-response'];

		if(!$captcha){
			$errors->add( 'not_human', "<strong>ERROR</strong>: Your name is Bot? James Bot? Check bellow the form, there's a Back to [sitename] link." );
			exit;
		}
		$response=json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=YOUR SECRET KEY&response=".$captcha."&remoteip=".$_SERVER['REMOTE_ADDR']), true);
		if($response['success'] == false)
		{
			$errors->add( 'spammer','<h2>You are spammer ! Get the @$%K out</h2>' );
		}
	}


	/**
	 *
	 *	This is function used to send the activation link mail
	 *	to activate the user
	 *	@param Array
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */


	public function neil_send_activation_link($userdata){
		extract($userdata);
		$hash = base64_encode($ID . ',' . $user_pass);
		add_user_meta($ID,'_dsp_confirm','false');
		add_user_meta( $ID, 'hash', $hash );
		$user_info = get_userdata($ID);
		$to = $user_info->user_email;           
		$subject = 'Member Verification'; 
		$message = 'Hello,';
		$message .= "\n\n";
		$message .= 'Welcome to the ' . get_bloginfo('name'). 'Site';
		$message .= "\n\n";
		$message .= 'Username: '.$user_info->user_login;
		$message .= "\n";
		$message .= 'Password: '. $user_pass;
		$message .= "\n\n";
		$message .= 'Please click this link to activate your account:';
		$message .= '<a href="'.home_url('/').'activate?id='.$ID.'&key='.$hash . '">Activate</a>';
		$message  = apply_filters('neil_activation_message',$message,$user_info);
		$subject  = apply_filters('neil_subject_change',$subject);
		$headers = 'From: ' . get_bloginfo('admin_email') . "\r\n";  
		wp_mail($to, $subject, $message, $headers); 
	}


    
	/**
	 *
	 *	This method is used to authenticate user 
	 *	wheather or not user activated via email
	 *	@param Array
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
	
	public function neil_authenticate_user_login($user) {
		if(!empty($user)){
			$userDetails = $user->data;
			$userId = $userDetails->ID;
			$isActivated = get_user_meta($userId,'_dsp_confirm');
			$isActivated =  $isActivated[0];
			if($isActivated == 'false'){
    			$user = new WP_Error( 'denied', __("<strong>ERROR</strong>: First you need to activate your account") );//create an error
	        	remove_action('authenticate', 'wp_authenticate_username_password', 20); //key found - don't proceed!
	        }
	    }
	    return $user;
	}

    
    /**
	 *
	 *	This method is used to editing WordPress 
	 *	registration confirmation message
	 *	@param String
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */
    
    function neil_edit_password_email_text ( $text ) 
    {
    	if ( $text == 'A password will be e-mailed to you.' )
    	{
    		$text = __('If you leave password fields empty one will be generated for you. Password must be at least eight characters long.',TEXT_DOMAIN);
    	}
    	return $text;
    }

   
    /**
	 *
	 *	This method is used to for activate user in WordPress 
	 *	from mail activation link
	 * 	@access public
	 * 	@author Neil
	 * 	@since  1.0
	 * 	@return void
	 * 	
	 */

    public function neil_check_for_activation_link()
    {
    	if(!isset($_REQUEST['id']) && !isset($_REQUEST['key']))
    		return false;

    	$userId = $_REQUEST['id'];
    	$key = $_REQUEST['key'];
    	$hashKey = get_user_meta( $userId, 'hash');
    	$hashKey = $hashKey[0];
    	if($hashKey != $key)
    	{   
    	    $user = new WP_Error( 'denied', __("<strong>ERROR</strong>: Wrong hash key " . $key . " send"));//create an error
    	    return false;
    	}
    	
    	update_user_meta($userId,'_dsp_confirm','true');
    	echo __( 'Your Profile activated Successfully , Thank you ! ',TEXT_DOMAIN );
    	return true;
    }
}

add_action( 'plugins_loaded', array( 'WP_AUTO_LOGIN_WITH_RECAPTCHA', 'get_instance' ) );
add_action('init',array('WP_AUTO_LOGIN_WITH_RECAPTCHA','neil_check_for_activation_link'));