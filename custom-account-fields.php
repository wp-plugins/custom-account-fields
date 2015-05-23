<?php
/*
Plugin Name: Custom account fields
Plugin URI: 
Description: Doplňuje nové a nastavuje existující položky pro zákaznický (uživatelský) účet pro woocommerce. České a slovenské IČ (IČO), DIČ, slovenské IČ DPH nastavení telefonu jako nepovinné položky pro woocommerce. Adds new and sets existing customer account fields for woocommerce. Czech and Slovak IČ(IČO) - Company number, DIČ - VAT number, Slovak IČ DPH - VAT number 2 and phone number isn't required for woocommerce.
Author: Tomáš Slavík
Author URI: http://www.monitom.cz/
Version: 1.2.1
License: GPLv3 or later
*/


if ( !defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) 
	return; // Check if WooCommerce is active

if ( !class_exists( 'mtCustomAccountFields' ) ) :

final class mtCustomAccountFields {

	/**
	 * @var string
	 */
	public $version = '1.2.1';

	/**
	 * @The single instance of the class
	 */
	protected static $_instance = null;

	/**
	 * Instance
	 *
	 * Ensures only one instance of class is loaded or can be loaded.
	 *
	 * @static
	 * @return mtCustomAccountFieldse instance
	 */
	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	/**
	 * Cloning is forbidden.
	 */
	public function __clone() {
		_doing_it_wrong( __FUNCTION__, __( 'Cloning is forbidden', 'custom-account-fields' ) );
	}

	/**
	 * Unserializing instances of this class is forbidden.
	 */
	public function __wakeup() {
		_doing_it_wrong( __FUNCTION__, __( 'Unserializing instances of this class is forbidden.', 'custom-account-fields') );
	}

	/**
	 * Constructor.
	 */
	public function __construct() {
		
		load_plugin_textdomain( 'custom-account-fields', false, dirname( plugin_basename( __FILE__ ) ) . '/languages/' );
		
		// hooks
		add_filter( 'woocommerce_billing_fields' , array( $this, 'woocommerce_billing_fields' ), 10, 2 );
		add_filter( 'woocommerce_my_account_my_address_formatted_address', array( $this, 'woocommerce_my_account_my_address_formatted_address' ), 10, 3 );
		add_filter( 'woocommerce_localisation_address_formats', array( $this, 'woocommerce_localisation_address_formats' ) );
		add_filter( 'woocommerce_formatted_address_replacements', array( $this, 'woocommerce_formatted_address_replacements' ), 10, 2 );
		add_filter( 'woocommerce_order_formatted_billing_address', array( $this, 'woocommerce_order_formatted_billing_address' ), 10, 2 );

		add_action( 'wp_enqueue_scripts', array( $this, 'wp_enqueue_scripts' ) );
		
		// admin (backend) part
		add_filter( 'woocommerce_customer_meta_fields', array( $this, 'woocommerce_customer_meta_fields' ) );
		add_filter( 'woocommerce_admin_billing_fields', array( $this, 'woocommerce_admin_billing_fields' ) );
		
				
	}
	
	public function woocommerce_billing_fields( $fields, $country ) {
		// add company identification number (ico) 
		$fields['billing_company_number'] = array(
		'label'     => __('Company number', 'custom-account-fields'),
		'placeholder'   => '',
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
		 );

		// add vat_number (dic)
		$fields['billing_vat_number'] = array(
		'label'     => __('VAT number', 'custom-account-fields'),
		'placeholder'   => '',
		'required'  => false,
		'class'     => array('form-row-wide'),
		'clear'     => true
		 );

//		if ($country === 'SK')
		{
			// add vat_number 2 (ic dph)
			$fields['billing_vat_number_2'] = array(
			'label'     => __('VAT number 2', 'custom-account-fields'),
			'placeholder'   => '',
			'required'  => false,
			'class'     => array('form-row-wide'),
			'clear'     => true
			 );
		}		
		// change phone item required state
		$fields['billing_phone']['required'] = false;

		// Enqueue scripts
		wp_enqueue_script( 'caf-address' );
		 
		return $fields;
	}

	public function woocommerce_my_account_my_address_formatted_address( $fields, $customer_id, $name ) {
		return $fields += array(
			'company_number' => get_user_meta( $customer_id, $name . '_company_number', true ),
			'vat_number' => get_user_meta( $customer_id, $name . '_vat_number', true ),
			'vat_number_2' => get_user_meta( $customer_id, $name . '_vat_number_2', true ));
	}

	public function woocommerce_localisation_address_formats($address_formats) {
		$address_formats['CZ'] .= "\n{company_number}\n{vat_number}";
		$address_formats['SK'] .= "\n{company_number}\n{vat_number}\n{vat_number_2}";
		return $address_formats;
	}

	public function woocommerce_formatted_address_replacements( $replace, $args) {
		return $replace += array(
			'{company_number}' => (isset($args['company_number']) && $args['company_number'] != '' ) ?  __('Company number: ', 'custom-account-fields') .$args['company_number'] : '',
     	    '{vat_number}' => (isset($args['vat_number']) && $args['vat_number'] != '') ?  __('VAT number: ', 'custom-account-fields') . $args['vat_number'] : '',
			'{vat_number_2}' => (isset($args['vat_number_2']) && $args['vat_number_2'] != '') ?  __('VAT number 2: ', 'custom-account-fields') . $args['vat_number_2'] :'',
			'{company_number_upper}' => strtoupper((isset($args['company_number']) && $args['company_number'] != '') ?__('Company number: ', 'custom-account-fields') . $args['company_number'] : '' ),
			'{vat_number_upper}' => strtoupper((isset($args['vat_number']) && $args['vat_number'] != '') ? __('VAT number: ', 'custom-account-fields') . $args['vat_number'] : ''),
			'{vat_number_upper_2}' => strtoupper((isset($args['vat_number_2']) && $args['vat_number_2'] != '') ? __('VAT number 2: ', 'custom-account-fields') . $args['vat_number_2'] : '') ,
		);
	}

	public function woocommerce_order_formatted_billing_address($address, $order) {
		return $address += array(
					'company_number'	=> $order->billing_company_number,
					'vat_number'	=> $order->billing_vat_number,
					'vat_number_2'	=> $order->billing_vat_number_2);
	}
	
	public function plugin_url() {
		return untrailingslashit( plugins_url( '/', __FILE__ ) );
	}


	public function wp_enqueue_scripts() {
		$suffix               = defined( 'SCRIPT_DEBUG' ) && SCRIPT_DEBUG ? '' : '.min';
		$assets_path          = str_replace( array( 'http:', 'https:' ), '', $this->plugin_url() ) . '/assets/';
		$frontend_script_path = $assets_path . 'js/frontend/';

		// Register any scripts for later use, or used as dependencies
		wp_register_script( 'caf-address', $frontend_script_path . 'address' . $suffix . '.js', array( 'jquery' ), $this->version, true );
		
		// Queue frontend scripts conditionally	
		if ( is_checkout() ) {
			wp_enqueue_script( 'caf-address' );
		}
		
		// Variables for JS scripts

	}
	
	public function woocommerce_customer_meta_fields($fields) {
		$fields['billing']['fields'] += array(
			'billing_company_number' => array(
				'label' => __('Company number', 'custom-account-fields'),
				'description' => ''
			),	
			'billing_vat_number' => array(
				'label' => __('VAT number', 'custom-account-fields'),
				'description' => ''
			),	
			'billing_vat_number_2' => array(
				'label' => __('VAT number 2', 'custom-account-fields'),
				'description' => ''
			) );
		return $fields;
	}
	
	public function woocommerce_admin_billing_fields ($fields) {
		return $fields += array(
			'company_number' => array(
				'label'     => __('Company number', 'custom-account-fields'),
				'show'   => false
			),
			'vat_number' => array(
				'label'     => __('VAT number', 'custom-account-fields'),
				'show'   => false
			),
			'vat_number_2' => array(
				'label'     => __('VAT number 2', 'custom-account-fields'),
				'show'   => false
			) );
				
	}
}	
endif;

mtCustomAccountFields::instance();

?>