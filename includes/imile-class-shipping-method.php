<?php

/**
 * The file that defines the core plugin class
 *
 * A class definition that includes attributes and functions used across both the
 * public-facing side of the site and the admin area.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/includes
 */

/**
 * The core plugin class.
 *
 * This is used to define internationalization, admin-specific hooks, and
 * public-facing site hooks.
 *
 * Also maintains the unique identifier of this plugin as well as the current
 * version of the plugin.
 *
 * @since      1.0.1
 * @package    Imile
 * @subpackage Imile/includes
 * @author     iMile <imile@gmail.com>
 */
 
defined( 'ABSPATH' ) || exit;
 
class imile_Shipping_Method extends WC_Shipping_Method {
  /**
   * Shipping class
   */
  public function __construct($instance_id = 0) {
    // These title description are display on the configuration page
    $this->id = 'imile_shipping_method';
    $this->method_title = esc_html__('iMile Shipping', 'imile' );
    $this->method_description = esc_html__('iMile WooCommerce Shipping', 'imile' );
    $this->instance_id        = absint( $instance_id );

    // Method with all the options fields.
    $this->init_form_fields();

    // Load the settings.
    $this->init_settings();
    $this->title = esc_html__('iMile Shipping', 'imile' );
    $this->description = esc_html__('iMile WooCommerce Shipping', 'imile' );

    
    $this->supports = [
        'settings',
        'shipping-zones',
        'instance-settings',
        //'global-instance',
    ];    
    $this->enabled = $this->get_option( 'enabled' );
    $this->customerid = $this->get_option( 'customerid' );
    $this->secretkey = $this->get_option( 'secretkey' );
    $this->methodmode = $this->get_option( 'methodmode' );
    
    // This action hook saves the settings.
    add_action( 'woocommerce_update_options_shipping_' . $this->id, array( $this, 'process_admin_options' ) );     
   }

   public function init_form_fields() {
      //$api = new Imile_Api();
      //$api->getAccessToken("woocommerce_imile-shipping-method_settings");
      //echo "hello";
     $form_fields = array(
       'enabled' => array(
          'title'   => esc_html__('Enable/Disable', 'imile' ),
          'type'    => 'checkbox',
          'label'   => esc_html__('Enable this shipping method', 'imile'  ),
          'default' => esc_html__('no', 'imile'  ),
		  'class'   =>  'test'
       ),
       'secretkey' => array(
          'title'       => esc_html__('Secret Key', 'imile' ),
          'type'        => 'text',
          'description' => esc_html__('Enter Secret Key', 'imile'  ),
          'default'     => esc_html__('', 'imile' ),
          'desc_tip'    => true
       ),
       'customerid' => array(
          'title'       => esc_html__('Customer Id', 'imile' ),
          'type'        => 'text',
          'description' => esc_html__('Enter Customer Id', 'imile'  ),
          'default'     => esc_html__('', 'imile' ),
          'desc_tip'    => true
       ),
       'shipperName' => array(
         'title'       => esc_html__('Shipper Name', 'imile' ),
         'type'        => 'text',
         'description' => esc_html__('Shippers Company name', 'imile'  ),
         'default'     => esc_html__('', 'imile' ),
         'desc_tip'    => true,
       ),	
       'shipperContactPerson' => array(
         'title'       => esc_html__('Shipper Contact Person', 'imile' ),
         'type'        => 'text',
         'description' => esc_html__('Shippers Full Name', 'imile'  ),
         'default'     => esc_html__('', 'imile' ),
         'desc_tip'    => true,
       ),
       'contactNumber' => array(
         'title'       => esc_html__('Contact Number', 'imile' ),
         'type'        => 'text',
         'description' => esc_html__('Contact Number', 'imile'  ),
         'default'     => esc_html__('', 'imile' ),
         'desc_tip'    => true,
       ),	   
       'pickupType' => array(
            'title'       => esc_html__('Pickup Type', 'imile' ),
            'label'       => esc_html__('Pickup Type', 'imile' ),
            'type'        => 'select',
            'placeholder' => esc_html__('Select Pickup Type'),
            'class'       => array('input-select'),
            'options'     => array('0'=>'No','1' => 'Yes'),
       ),	   
       'methodmode' => array(
            'title'       => esc_html__('Shipping Mode', 'imile' ),
            'label'       => esc_html__('Shipping Mode', 'imile' ),
            'type'        => 'select',
            'placeholder' => esc_html__('Select Shipping Mode'),
            'class'       => array('input-select'),
            'options'     => array(''=> 'Select Shipping Mode','test'=>'Test Mode','live' => 'Live Mode'),
        ),
     );
	 
      $this->form_fields = $form_fields;
   }
   
	/**
	 * Get setting form fields for instances of this shipping method within zones.
	 *
	 * @return array
	 */
	public function get_instance_form_fields() {
		return parent::get_instance_form_fields();
	}

}

