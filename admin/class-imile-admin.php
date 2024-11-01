<?php


/**
 * The admin-specific functionality of the plugin.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/admin
 */

/**
 * The admin-specific functionality of the plugin.
 *
 * Defines the plugin name, version, and two examples hooks for how to
 * enqueue the admin-specific stylesheet and JavaScript.
 *
 * @package    Imile
 * @subpackage Imile/admin
 * @author     iMile <imile@gmail.com>
 */
class Imile_Admin {

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
	 * @param      string    $plugin_name       The name of this plugin.
	 * @param      string    $version    The version of this plugin.
	 */
	public function __construct( $plugin_name, $version ) {

		$this->plugin_name = $plugin_name;
		$this->version = $version;

	}

	/**
	 * Register the stylesheets for the admin area.
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

		wp_enqueue_style( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'css/imile-admin.css', array(), $this->version, 'all' );

	}

	/**
	 * Register the JavaScript for the admin area.
	 *
	 * @since    1.0.0
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

		wp_enqueue_script( $this->plugin_name, plugin_dir_url( __FILE__ ) . 'js/imile-admin.js', array( 'jquery' ), time(), false );

		wp_localize_script( $this->plugin_name, 'myAjax', array(
			'ajaxurl' => admin_url( 'admin-ajax.php' ), // The URL to WordPress Ajax handler
		) );

	}

	public function woocommerce_saved_order_items( $order_id, $order ){
	    if ( ! $order_id )
		    return;
		if ( !is_admin() )
			return; 
			
		$order_meta = get_post_meta( $order_id, 'imile_create_shipping_response', true );

		if(!empty($order_meta)){
			$ordernumber = json_decode($order_meta, true);
		}

		$order = wc_get_order($order_id);
		
		$order_data = $order->get_data();
		
		$api = new Imile_Api();
		$optionKey = "woocommerce_imile_shipping_method_settings";
		$accessToken = $api->getAccessToken($optionKey);
		
		$param = $api->getOrderData($order_id, $order);

		$current_user = wp_get_current_user();
		$param["consignorContact"] = $current_user->display_name;
		update_post_meta( $order_id, 'rtu__t', json_encode($accessToken) );
		if(!empty($order_meta) && isset($ordernumber["expressNo"])){
		    if($accessToken){

				$removeKeys = ["orderType","oldExpressNo","deliveryType","consignorCountry","consignor","totalCount","totalWeight","totalVolume","currency","isPickUp","consignorLongitude","consignorLatitude","consigneeLatitude","consigneeLongitude","serviceTime","consigneeCountry","declareValue"];

				foreach($removeKeys as $key){
					unset($param[$key]);
				}

				$param["batterType"] = "Normal";
				$param["orderDescription"] = "";
				$param["consigneeMobile"] = $param["consigneePhone"];
				$param["consignorMobile"] = $param["consignorPhone"];

				$response = $api->updateShippingOrder($optionKey, $param, $accessToken);
				if($response)
					update_post_meta( $order_id, 'imile_update_shipping_response', json_encode($response) );
			}
		}else{

		    if($accessToken){
				//update_post_meta( $order_id, 'rtu__', json_encode($param) );
				$response = $api->createShippingOrder($optionKey, $param, $accessToken);

				if($response)
					update_post_meta( $order_id, 'imile_create_shipping_response', json_encode($response) );
			}
		}
	}
	
	

	public function woocommerce_process_shop_order_meta($order_id, $order){
		if ( !is_admin() )
			return; 

		// Get an instance of the WC_Order object
		$order = wc_get_order( $order_id );
		// Get an instance of the WC_Order object
		$order_data = $order->get_data();
		
		update_post_meta( $order_id, 'imile_check_shipping_response__', json_encode($order_data) );
		return;

		$api = new Imile_Api();
		$optionKey = "woocommerce_imile_shipping_method_settings";
		$accessToken = $api->getAccessToken($optionKey);

		if ( $order->has_status('cancelled') ) {
			if($accessToken){
				$param = [ "orderCode" => $order_id ];
				$response = $api->removeShippingOrder($optionKey, $param, $accessToken);
				if($response)
					update_post_meta( $order_id, 'imile_delete_shipping_response', json_encode($response) );
					update_post_meta( $order_id, 'imile_delete_shipping_response', json_encode($order) );
			}
			return;
		}

		$param = $api->getOrderData($order_id, $order);
		if(!empty($order_meta)){
		    if($accessToken){

				$removeKeys = ["orderType","oldExpressNo","deliveryType","consignorCountry","consignor","totalCount","totalWeight","totalVolume","currency","isPickUp","consignorLongitude","consignorLatitude","consigneeLatitude","consigneeLongitude","serviceTime","consigneeCountry","declareValue"];

				foreach($removeKeys as $key){
					unset($param[$key]);
				}

				$param["batterType"] = "Normal";
				$param["orderDescription"] = "";
				$param["consigneeMobile"] = $param["consigneePhone"];
				$param["consignorMobile"] = $param["consignorPhone"];

				$response = $api->updateShippingOrder($optionKey, $param, $accessToken);
				if($response)
					update_post_meta($order_id, 'imile_update_shipping_response', json_encode($response));
			}
		}else{
		    if($accessToken){
				$response = $api->createShippingOrder($optionKey, $param, $accessToken);
				if($response)
					update_post_meta($order_id, 'imile_create_shipping_response', json_encode($response)      );
			}
		}
	}

	public function add_meta_box(){
		add_meta_box(
			"imile_meta_box_shop_order", 
			esc_html__('iMile', 'imile' ), 
			[$this, "order_tracking"], 
			"woocommerce_page_wc-orders", 
			"normal", 
			"core"
		);
		
		add_meta_box(
			"imile_meta_box_order_response", 
			esc_html__('iMile API Response', 'imile_api_response' ), 
			[$this, "order_response"], 
			"woocommerce_page_wc-orders", 
			"normal", 
			"core"
		);	
		
		add_meta_box(
			"imile_meta_box_shop_order", 
			esc_html__('iMile', 'imile' ), 
			[$this, "shop_order_tracking"], 
			"shop_order", 
			"normal", 
			"core"
		);
		
		add_meta_box(
			"imile_meta_box_order_response", 
			esc_html__('iMile API Response', 'imile_api_response' ), 
			[$this, "shop_order_response"], 
			"shop_order", 
			"normal", 
			"core"
		);		
		
	}


	public function order_tracking(){
	    
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/imile-admin-display.php';
	}
	
	public function order_response(){
	    
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/imile-admin-response.php';
	}
	
	public function shop_order_tracking(){
	    
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/imile-admin-display-shop-order.php';
	}
	
	public function shop_order_response(){
	    
		require_once plugin_dir_path( dirname( __FILE__ ) ) . 'admin/partials/imile-admin-response-shop-order.php';
	}	

	public function imile_print_invoice(){
        check_ajax_referer('imile-data', 'nonce_data' );
		$api = new Imile_Api();

		$optionKey = "woocommerce_imile_shipping_method_settings";
		$accessToken = $api->getAccessToken($optionKey);

		// retrieve post_id, and sanitize it to enhance security
		$order_id = intval($_POST['order_id'] );

		// Check if the input was a valid integer
		if ( $order_id == 0 ) {
			$response = array(
				'status'  => 'success', // You can use 'error' if something went wrong
				'imiledata' => "Invalid Input"
			);
			wp_send_json( $response );
			die();
		}

		$shipping_r = get_post_meta($order_id, 'imile_create_shipping_response', true );
		$result = [];
		if(!empty($shipping_r)){
			$ordernumber = json_decode($shipping_r, true);
			if($accessToken){
				$param = [
					"orderCode" => $ordernumber["expressNo"],
					"orderCodeType" => "2"
				];
				$result = $api->reprintInvoice($optionKey, $param, $accessToken);
			}
		}

		$response = array(
			'status'  => 'success', // You can use 'error' if something went wrong
			'imiledata' => $result
		);
		wp_send_json( $response );
	}

	public function bulk_actions_edit_shop_order( $actions ){
		$actions['update_imile_shipping'] = esc_html__( 'Assign Imile Shipping', 'imile' );
    	return $actions;
	}

	public function handle_bulk_actions_edit_shop_order( $redirect_to, $action, $post_ids ){
	    
		if ( $action !== 'update_imile_shipping' )
        	return $redirect_to; // Exit

		foreach ($post_ids as $order_id) {
			$order = wc_get_order( $order_id );

			$order_meta = get_post_meta( $order_id, 'imile_create_shipping_response', true );
			if(!empty($order_meta)){
				$ordernumber = json_decode($order_meta, true);
			}
		
			// Get an instance of the WC_Order object
			$order_data = $order->get_data();

			$api = new Imile_Api();
			$optionKey = "woocommerce_imile_shipping_method_settings";
			$accessToken = $api->getAccessToken($optionKey);

			$param = $api->getOrderData($order_id, $order);
			if(!empty($order_meta) && isset($ordernumber["expressNo"])){
				if($accessToken){

					$removeKeys = ["orderType","oldExpressNo","deliveryType","consignorCountry","consignor","totalCount","totalWeight","totalVolume","currency","isPickUp","consignorLongitude","consignorLatitude","consigneeLatitude","consigneeLongitude","serviceTime","consigneeCountry","declareValue"];

					foreach($removeKeys as $key){
						unset($param[$key]);
					}

					$param["batterType"] = "Normal";
					$param["orderDescription"] = "";
					$param["consigneeMobile"] = $param["consigneePhone"];
					$param["consignorMobile"] = $param["consignorPhone"];

					$response = $api->updateShippingOrder($optionKey, $param, $accessToken);

					if($response)
						update_post_meta( $order_id, 'imile_update_shipping_response', json_encode($response) );
						
					if(isset($response['expressNo'])){
					    $returnarg = array('updated_imile_shipping' => '1');

					}else{
					    $returnarg = array('updated_imile_shipping' => '0');

					}						
				}
			}else{
				if($accessToken){
					$response = $api->createShippingOrder($optionKey, $param, $accessToken);
			
					if($response)
						update_post_meta( $order_id, 'imile_create_shipping_response', json_encode($response) );
						
					if(isset($response['expressNo'])){
					    $returnarg = array('added_imile_shipping' => '1');

					}else{
					    $returnarg = array('added_imile_shipping' => '0');

					}	
				}
			}
			
			

		}

		return $redirect_to = add_query_arg($returnarg, $redirect_to );
	}
	
	



	public function woocommerce_order_status_cancelled( $order_id ){
		if (!$order_id) return;
		$order_meta = get_post_meta( $order_id, 'imile_create_shipping_response', true );
		if(!empty($order_meta)){
			$ordernumber = json_decode($order_meta, true);
		}
		
		$order = wc_get_order( $order_id );
		$api = new Imile_Api();
		$optionKey = "woocommerce_imile_shipping_method_settings";
		$accessToken = $api->getAccessToken($optionKey);

		if ( $order->has_status('cancelled') ) {
			if($accessToken){
				$param = [ "orderCode" => $order_id ];
				$response = $api->removeShippingOrder($optionKey, $param, $accessToken);
				if($response)
					update_post_meta( $order_id, 'imile_delete_shipping_response', json_encode($response) );
					//update_post_meta( $order_id, 'imile_delete_shipping_response', json_encode($order) );
			}
			return;
		}
	}

	public function admin_notices_shipping(){

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

	public function manage_edit_shop_order_columns($columns){
		$reordered_columns = array();
		foreach( $columns as $key => $column){
			$reordered_columns[$key] = $column;
			if( $key ==  'order_status' ){
				$reordered_columns['imile_awb_id'] = esc_html__( 'AWB ID','imile');
			}
		}
		return $reordered_columns;
	}
	
	public function custom_orders_list_column_content( $column, $post_id ){
		switch ( $column ){
			case 'imile_awb_id':
				// Get custom post meta data
				
				$shipping_r = get_post_meta($post_id->ID, 'imile_create_shipping_response', true );
				
				 $nonce = wp_create_nonce('imile-data');
			
				if(!empty($shipping_r)){
					$ordernumber = json_decode($shipping_r, true);
					if(isset($ordernumber["expressNo"])){
						printf( '<a href="javascript:;" class="imile_show_invoice" data-nonce="%s" data-order_id="%s">%s</a>', esc_attr($nonce), esc_attr($post_id->ID),  esc_html($ordernumber["expressNo"])); 
					}else{
						echo esc_html__("NIL", "imile");	
					}
				}
				else
					echo esc_html__("NIL", "imile");

			break;
		}
	}
	
	public function custom_orders_list_column_content_shop_order( $column, $post_id ){
		switch ( $column ){
			case 'imile_awb_id':
				// Get custom post meta data
				$nonce = wp_create_nonce('imile-data');
				$shipping_r = get_post_meta( $post_id, 'imile_create_shipping_response', false );
				//print_r($orderNumber);
				if(!empty($shipping_r)){
					$ordernumber = json_decode($shipping_r[0], true);
					if(isset($ordernumber["expressNo"])){
						//printf( '<a href="javascript:;" class="imile_show_invoice" data-nonce="%s" data-order_id="%s">' . __( $ordernumber["expressNo"] ) . '</a>',  $post_id,  ); 
printf( '<a href="javascript:;" class="imile_show_invoice" data-nonce="%s" data-order_id="%s">%s</a>', esc_attr($nonce), esc_attr($post_id),  esc_html($ordernumber["expressNo"])); 
					}else{
						echo esc_html__("NIL", "imile");	
					}
				}
				else
					echo esc_html__("NIL", "imile");

			break;
		}
	}	
	
}
