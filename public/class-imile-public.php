<?php

/**
 * The public-facing functionality of the plugin.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/public
 */

/**
 * The public-facing functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the public-facing stylesheet and JavaScript.
 *
 * @package    Imile
 * @subpackage Imile/public
 * @author     iMile <imile@gmail.com>
 */
class Imile_Public {

	/**
	 * The ID of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $plugin_name    The ID of this plugin.
	 */
	private $plugin_name;

	/**
	 * The version of this plugin.
	 *
	 * @since    1.0.1
	 * @access   private
	 * @var      string    $version    The current version of this plugin.
	 */
	private $version;

	/**
	 * Initialize the class and set its properties.
	 *
	 * @since    1.0.1
	 * @param      string    $plugin_name       The name of the plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the public-facing side of the site.
	 *
	 * @since    1.0.1
	 */
	public function enqueue_styles() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Imile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Imile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/imile-public.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the public-facing side of the site.
	 *
	 * @since    1.0.1
	 */
	public function enqueue_scripts() {

		/**
		 * This function is provided for demonstration purposes only.
		 *
		 * An instance of this class should be passed to the run() function
		 * defined in Imile_Loader as all of the hooks are defined
		 * in that particular class.
		 *
		 * The Imile_Loader will then create the relationship
		 * between the defined hooks and the functions defined in this
		 * class.
		 */

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/imile-public.js', array( 'jquery' ), $this->version, false );

	}

	public function woocommerce_thankyou( $order_id ){
		if ( ! $order_id )
			return;

		// Allow code execution only once 
		if( !get_post_meta( $order_id, '_thankyou_action_done', true ) ) {

			// Get an instance of the WC_Order object
			$order = wc_get_order( $order_id );

			$api = new Imile_Api();
			$param = $api->getOrderData($order_id, $order);
			$optionKey = "woocommerce_imile_shipping_method_settings";
			$accessToken = $api->getAccessToken($optionKey);
			
			
			//update_post_meta( $order_id, 'rt__t', json_encode($accessToken) );
			if($accessToken){
				//update_post_meta( $order_id, 'rt__', json_encode($param) );
				$response = $api->createShippingOrder($optionKey, $param, $accessToken);
				if($response){
					update_post_meta( $order_id, 'imile_create_shipping_response', json_encode($response) );
					
					if($response['expressNo']){
					    wc_print_notice( esc_html__("Order has been successful assign to iMile for shipping.", "imile"), "success" );
					}else{
					    wc_print_notice(esc_html__("Order has been not assign to iMile for shipping Please contact to store owner.", "imile"), "error" );
					}
				}
			}else{
			    wc_print_notice(esc_html__("Order has been not assign to iMile for shipping. Empty access Token. Please contact to store owner.", "imile"), "error" );
			}
		}
	}

	public function woocommerce_order_details_after_order_table( $order ){
		$order_id =  $order->get_ID();
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'public/partials/imile-public-display.php';
	}
	
	public function imile_notices_shipping(){

        if(isset($_REQUEST['updated_imile_shipping'])){
            if($_REQUEST['updated_imile_shipping']==1){
                $strpl = esc_html__( 'Imile shipping assigned successfully.','imile');
                $class = "updated";
            }else{
                $class = "error";
                $strpl = esc_html__( 'Order has been not assign to iMile for shipping.','imile');
            }
        }
        
        if(isset($_REQUEST['added_imile_shipping'])){
            if($_REQUEST['added_imile_shipping']==1){
                $strpl = esc_html__( 'Imile shipping assigned successfully.','imile');
                $class = "updated";
            }else{
                $strpl = esc_html__( 'Order has been not assign to iMile for shipping.','imile');
                $class = "error";
            }
        }
        
		$count = intval( $_REQUEST['processed_count'] );
		
		echo '<div id="message" class="',esc_attr($class),' fade"><p>',esc_html($strpl),'</p></div>';
	}	

	 /**
     * Callback for Order.
     *
     * @since 1.0.0
     * @param object $order             Main order.
     */	
	public function imilecallback() { 
        global $woocommerce, $post;

		global $wp_filesystem;
		if (empty($wp_filesystem)) {
			require_once (ABSPATH . '/wp-admin/includes/file.php');
			WP_Filesystem();
		}

        //$json = file_get_contents('php://input');
		$json =  $wp_filesystem->get_contents('php://input');

        $js = stripslashes($json);
        $order_data = json_decode($js, true);
		
		if($order_data){
            $waybillno = $order_data['waybillNo']; 
            $order_id = $order_data['orderCode'];
            $imileStatuscode = $order_data['orderStatus'];
            $comment = $order_data['lastProblemStatus'];
            
            $order_status = 'pending';
			
            if($imileStatuscode=='2302' || $imileStatuscode=='100' || $imileStatuscode=='200'){
                $order_status = 'processing';
                $comment = "Pickup done from pickup location.";
            }
            
            if($imileStatuscode=='300' || $imileStatuscode=='301' || $imileStatuscode=='800'){
                $order_status = 'processing';
                $comment = "The shipment has been scheduled for delivery.";
            }            
            
            if($imileStatuscode=='400' || $imileStatuscode=='500'){
                $order_status = 'processing';
                $comment = "The shipment assigned to DA, waiting for delivery.";
            }   

            if($imileStatuscode=='1600'){
                $order_status = 'failed';
                $comment = "Driver failed delivery the shipment to customer and already take the shipment back to warehouse. This will be taken again for customer delivery. This is not a terminal status.";
            }   

            if($imileStatuscode=='1200'){
                $order_status = 'completed';
                $comment = "Shipment delivered to customer.";
            }
            
            if($imileStatuscode=='1300'){
                $order_status = 'on-hold';
                $comment = "Driver got the return shipment from customer.";
            }
            
            if($imileStatuscode=='1400'){
                $order_status = 'refunded';
                $comment = "Driver returns the COD to customer successfully.";
            }
            
            if($imileStatuscode=='2203'){
                $order_status = 'failed';
                $comment = "Failed order.";
            }            

            if($imileStatuscode=='2403'){
                $order_status = 'cancelled';
                $comment = "Order cancelled.";
            }           

            if($imileStatuscode=='302'){
                $order_status = 'on-hold';
                $comment = "The shipment has been scheduled for delivery again.";
            }
                $order = wc_get_order($order_id);		
                $order_info = $order->get_data();	
                $order->update_status($order_status,$comment); // Processing.
			
			    header( 'HTTP/1.1 200 OK' );
				die();
		}
		
    }	

}
