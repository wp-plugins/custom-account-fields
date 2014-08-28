<?php
/*
Plugin Name: Custom account fields
Plugin URI: 
Description: České IČ (IČO), DIČ, nastavení telefonu jako nepovinné položky pro woocommerce. Doplňuje nové a nastavuje existující položky pro zákaznický (uživatelský) účet pro woocommerce. Czech IČ(IČO) - Company number, DIČ - VAT number and phone number isn't required for woocommerce. It adds new and sets existing customer account fields for woocommerce.
Author: Tomáš Slavík
Author URI: http://www.monitom.cz/
Version: 1.0
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
	public $version = '1.0';

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

		 // change phone item required state
		 $fields['billing_phone']['required'] = false;

		 return $fields;
	}

	public function woocommerce_my_account_my_address_formatted_address( $fields, $customer_id, $name ) {
		return $fields += array(
			'company_number' => get_user_meta( $customer_id, $name . '_company_number', true ),
			'vat_number' => get_user_meta( $customer_id, $name . '_vat_number', true ));
	}

	public function woocommerce_localisation_address_formats($address_formats) {
		$address_formats['CZ'] .= "{company_number}, {vat_number}";
		return $address_formats;
	}

	public function woocommerce_formatted_address_replacements( $replace, $args) {
		extract( $args );
		return $replace += array(
			'{company_number}'       => $company_number == '' ? '' : __('Company number: ', 'custom-account-fields') . $company_number,
			'{vat_number}'       => $vat_number == '' ? '' : __('VAT number: ', 'custom-account-fields') . $vat_number,
			'{company_number_upper}' => strtoupper($company_number == '' ? '' : __('Company number: ', 'custom-account-fields') . $company_number),
			'{vat_number_upper}' => strtoupper($vat_number == '' ? '' : __('VAT number: ', 'custom-account-fields') . $vat_number),
		);
	}

	public function woocommerce_order_formatted_billing_address($address, $order) {
		return $address += array(
					'company_number'	=> $order->billing_company_number,
					'vat_number'	=> $order->billing_vat_number);
	}
}
endif;

mtCustomAccountFields::instance();

?>