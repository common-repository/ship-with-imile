<?php
class Imile_Api{
	public function __construct() {
		$this->testURL = "https://openapi.52imile.cn";
		$this->liveURL = "https://openapi.imile.com";
	}
    public function send_post_request($args){
		$credentials = get_option( $args["key"] );
		
		if(isset($credentials["enabled"]) && $credentials["enabled"] == "yes"){
			//$userIP = $_SERVER['REMOTE_ADDR'];
			$userIP = filter_input(INPUT_SERVER, 'REMOTE_ADDR', FILTER_VALIDATE_IP); 
			$timezoneOffset = $this->getTimezoneOffsetByIP($userIP);

			if($credentials["methodmode"] == "live"){
				$url = $this->liveURL . $args["path"];
			}else{
				$url = $this->testURL . $args["path"];
			}

			if(isset($args["addphonenumber"])){
				$args["params"]["consignorPhone"] = $credentials["contactNumber"];
			}

			$headers = $args["headers"];

			$customerID = $credentials["customerid"];
			$signature = $credentials["secretkey"];

			$body = [
				"customerId" => $customerID,
				"sign" => $signature,
				"signMethod" => "Simplekey",
				"format" => "json",
				"version" => "1.0.0",
				"timestamp" => time(),
				"timeZone" => $timezoneOffset,
				"param"    => $args["params"]
			];
			if($args["auth"]){
				$body["accessToken"] = $args["accessToken"];
			}
			update_option("api_json", json_encode($body));
			$args = array(
				'headers' => $headers,
			   	'body' => json_encode($body),
				'method' => 'POST',
				'data_format' => 'body',
			);

			$response = wp_remote_post($url, $args);
			
			if (is_wp_error($response)) {
				// Handle error here
				return ["status" => 0, "message" => 'wp failed.'];
			} else {
				// Process the response here
				//$response_code = wp_remote_retrieve_response_code($response);
				
				$responceData = json_decode(wp_remote_retrieve_body( $response ), TRUE );
				return ["status" => 1, "result" => $responceData, "message" => ""];
			}


		}
	}

	public function getTimezoneOffsetByIP($ip) {
		$apiUrl = "http://ipinfo.io/".$ip."/json";
		$args = array();
		$response = wp_remote_post($apiUrl, $args);

        $offset = null;
		if (is_wp_error($response)) {
			// Handle error here
			$offset = null;
		} else {

			$data = json_decode(wp_remote_retrieve_body( $response ), TRUE );
			
			if (isset($data['timezone'])) {
				$timezone = new DateTimeZone($data['timezone']);
				$now = new DateTime('now', $timezone);
				$offset = $timezone->getOffset($now) / 3600; // Convert to hours
			}
		}
		
		if ($offset !== null) {
			$timezoneOffset = ($offset > 0 ? "+".floor($offset) : floor($offset));
		}else{
			$timezoneOffset = "";
		}
		return $timezoneOffset;		

	}

	public function getAccessToken( $key ){
		$path = "/auth/accessToken/grant";
		$headers = ['Content-Type' => 'application/json'];
		$params = ["grantType" => 'clientCredential'];
		$data = [
			"key" => $key,
			"auth" => false,
			"path" => $path,
			"headers" => $headers,
			"params" => $params
		];

		$res = $this->send_post_request( $data );
		
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return $result["data"]["accessToken"];
			}
		}
		return false;
	}

	public function createShippingOrder($key, $params, $accessToken){
		$path = "/client/order/createB2cOrder";
		$headers = ['Content-Type' => 'application/json'];
		$data = [
			"accessToken" => $accessToken,
			"key" => $key,
			"auth" => true,
			"path" => $path,
			"headers" => $headers,
			"addphonenumber" => true,
			"params" => $params
		];
		
		$res = $this->send_post_request( $data );
		
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return $result["data"];
			}else{
				return $result;
			}
		}

		return false;
	}
	
	public function updateShippingOrder($key, $params, $accessToken){
		$path = "/client/order/modifyOrder";
		$headers = ['Content-Type' => 'application/json'];
		$data = [
			"accessToken" => $accessToken,
			"key" => $key,
			"auth" => true,
			"path" => $path,
			"headers" => $headers,
			"addphonenumber" => true,
			"params" => $params
		];
		
		$res = $this->send_post_request( $data );
		//return $res;
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return isset($result["data"]) ? $result["data"] : $result;
			}else{
				return $result;
			}
		}

		return false;
	}
	
	public function removeShippingOrder($key, $params, $accessToken){
		$path = "/client/order/deleteOrder";
		$headers = ['Content-Type' => 'application/json'];
		$data = [
			"accessToken" => $accessToken,
			"key" => $key,
			"auth" => true,
			"path" => $path,
			"headers" => $headers,
			"addphonenumber" => true,
			"params" => $params
		];
		
		$res = $this->send_post_request( $data );
		//return $res;
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return isset($result["data"]) ? $result["data"] : $result;
			}else{
				return $result;
			}
		}

		return false;
	}

	public function reprintInvoice($key, $params, $accessToken){
		$path = "/client/order/reprintOrder";
		$headers = ['Content-Type' => 'application/json'];
		$data = [
			"accessToken" => $accessToken,
			"key" => $key,
			"auth" => true,
			"path" => $path,
			"headers" => $headers,
			"addphonenumber" => true,
			"params" => $params
		];
		
		$res = $this->send_post_request( $data );
		//return $res;
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return isset($result["data"]) ? $result["data"] : $result;
			}else{
				return $result;
			}
		}

		return false;
	}

	public function imilegetTrackOrder($key, $params, $accessToken){
		$path = "/client/track/getOne";
		$headers = ['Content-Type' => 'application/json'];
		$data = [
			"accessToken" => $accessToken,
			"key" => $key,
			"auth" => true,
			"path" => $path,
			"headers" => $headers,
			"addphonenumber" => true,
			"params" => $params
		];
		
		$res = $this->send_post_request( $data );
		//return $res;
		if($res["status"]){
			$result = $res["result"];
			if($result["code"] == 200){
				return isset($result["data"]) ? $result["data"] : $result;
			}else{
				return $result;
			}
		}

		return false;
	}
	
	public function getOrderData($order_id, $order){

		$items = $order->get_items();

		$countProducts = 0;

		$product_details = [];

		$order_weight = 0;
		$total_volume = 0;

		$prodcutName = '';
		$i=1;
		foreach ( $items as $item_id => $item ) {

			// Get the product object
			$product = $item->get_product();

			$sku = $product->get_sku();
			$quantity = $item->get_quantity();
			$price = $product->get_price();

			if ( ! $product->is_virtual() ) {
                $order_weight += (float) $product->get_weight() * (float) $quantity;
            }

			$product_volume = (float) get_post_meta( $item->get_product_id(), '_item_volume', true );
    		$total_volume  += $product_volume * $quantity;

			$product_details[] = [
				"skuName"       =>  $item->get_name(),
				"skuNo"         =>  $sku,
				"skuDesc"       =>  "",
				"skuQty"        =>  $quantity,
				"skuGoodsValue" =>  $price,
				"skuUrl"        =>  ""
			];
			if($i==1){
				$prodcutName .= $item->get_name().'*'.$quantity;
			 }else{
				$prodcutName .= '+'.$item->get_name().'*'.$quantity;
			 }
		    $countProducts += $quantity;
		}
		
		if($order->get_shipping_postcode() == ""){
			// Get customer billing information details
			$order_details["first_name"] = $order->get_billing_first_name();
			$order_details["last_name"]  = $order->get_billing_last_name();
			$order_details["company"]    = $order->get_billing_company();
			$order_details["address_1"]  = $order->get_billing_address_1();
			$order_details["address_2"]  = $order->get_billing_address_2();
			$order_details["city"]       = $order->get_billing_city();
			$order_details["state"]      = $order->get_billing_state();
			$order_details["postcode"]   = $order->get_billing_postcode();
			$order_details["country"]    = $order->get_billing_country();
			$order_details["phone"]    = $order->get_billing_phone();
		}else{
			// Get customer shipping information details
			$order_details["first_name"] = !empty($order->get_shipping_first_name()) ? $order->get_shipping_first_name() : $order->get_billing_first_name();
			$order_details["last_name"]  = !empty($order->get_shipping_last_name()) ? $order->get_shipping_last_name() : $order->get_billing_last_name();
			$order_details["company"]    = !empty($order->get_shipping_company()) ? $order->get_shipping_company() : $order->get_billing_company();
			$order_details["address_1"]  = !empty($order->get_shipping_address_1()) ? $order->get_shipping_address_1() : $order->get_billing_address_1();
			$order_details["address_2"]  = !empty($order->get_shipping_address_2()) ? $order->get_shipping_address_2() : $order->get_billing_address_2();
			$order_details["city"]       = !empty($order->get_shipping_city()) ? $order->get_shipping_city() : $order->get_billing_city();
			$order_details["state"]      = !empty($order->get_shipping_state()) ? $order->get_shipping_state() : $order->get_billing_state();
			$order_details["postcode"]   = !empty($order->get_shipping_postcode()) ? $order->get_shipping_postcode() : $order->get_billing_postcode();
			$order_details["country"]    = !empty($order->get_shipping_country()) ? $order->get_shipping_country() : $order->get_billing_country();
			$order_details["phone"]    = !empty($order->get_shipping_phone()) ? $order->get_shipping_phone() : $order->get_billing_phone();
		}
		if(empty($order_details["phone"])){
		    if(empty($order->get_shipping_phone())){
		        $order_details["phone"] = '0000000000';
		    }else{
		         $order_details["phone"] = $order->get_shipping_phone();
		    }
		}
	
		$order_details["country"] = WC()->countries->countries[$order_details["country"]];
		$states = WC()->countries->get_states( $order_details["country"] );
		$order_details["state"]  = ! empty( $states[ $order_details["state"] ] ) ? $states[ $order_details["state"] ] : '';	
				
		$currentCurreny = get_woocommerce_currency();
		$shippingCurrency = $this->imileCountry($order_details['country']);
		
		$order_total = $order->get_total();
		
		
		
        $order_total = $this->convertCurrency($order_total,$currentCurreny,$shippingCurrency['code']);

		if($order->get_payment_method() == "cod"){
			$paymentMethodCode = 200;
			$collectingMoney = $order_total;
		}else{
			$paymentMethodCode = 100;
			$collectingMoney = 0;
		}
				

		$current_user = wp_get_current_user();
		
		$imile_settings = get_option("woocommerce_imile_shipping_method_settings");

		$store_name = !empty($imile_settings['shipperName']) ? $imile_settings['shipperName'] : get_bloginfo( 'name' );
		
		$author_user_email = get_the_author_meta('user_email');
		$author_display_name = !empty($imile_settings['shipperContactPerson']) ? $imile_settings['shipperContactPerson'] : get_the_author_meta('display_name');
		$consigner_phone = !empty($imile_settings['contactNumber']) ? $imile_settings['contactNumber'] : '0000000000';
		
		if($imile_settings['pickupType']==1){
			$pickDate = date("Y-m-d");
		}else{
			$pickDate = "";
		}
		
		$store_address     = get_option( 'woocommerce_store_address' );
		$store_address_2   = get_option( 'woocommerce_store_address_2' );
		$store_city        = get_option( 'woocommerce_store_city' );
		$store_postcode    = get_option( 'woocommerce_store_postcode' );
		// The country/state
		$store_raw_country = get_option( 'woocommerce_default_country' );

		// Split the country/state
		$split_country = explode( ":", $store_raw_country );

		// Country and state separated:
		$store_country = WC()->countries->countries[$split_country[0]];
		$states = WC()->countries->get_states( $split_country[0] );
		$store_state  = isset($split_country[1]) && isset( $states[ $split_country[1] ] ) ? $states[ $split_country[1] ] : '';
		

        $consignorCountry = $this->imileCountry($store_country);
        $consigneeCountry = $this->imileCountry($order_details['country']);
        
		$lang = get_locale();
		
		if($lang=="ar"){
			$consignorCountry = $this->imileCountryar($store_country);
			$consigneeCountry = $this->imileCountryar($order_details['country']);
		}        

		$param = array(
			"isPickUp"             => $imile_settings['pickupType'],
			"pickDate"             => $pickDate,
			"orderCode"            => $order_id,
			"orderType"            => "100",
			"oldExpressNo"         => " ",
			"deliveryType"         => "Delivery",
			"consignor"            => $store_name,
			"consignorContact"     => $author_display_name,
			"consignorPhone"       => $consigner_phone,
			"consignorCountry"     => $consignorCountry['value'],
			"consignorProvince"    => $store_state,
			"consignorCity"        => $store_city,
			"consignorArea"        => $store_postcode,
			"consignorAddress"     => $store_address,
			"consignorLongitude"   => "",
			"consignorLatitude"    => "",
			"consigneeContact"     => $order_details["first_name"].' '.$order_details["last_name"],
			"consigneePhone"       => $order_details["phone"],
			"consigneeLatitude"    => "",
			"consigneeLongitude"   => "",
			"serviceTime"          => "",
			"consigneeCountry"     => $consigneeCountry['value'],
			"consigneeProvince"    => $order_details['state'],
			"consigneeCity"        => $order_details['city'],
			"consigneeArea"        => $order_details['postcode'],
			"consigneeAddress"     => $order_details['address_1'],
			"paymentMethod"        => $paymentMethodCode,
			"goodsValue"           => $order_total,
			"declareValue"         => $order_total,
			"collectingMoney"      => $collectingMoney,
			"totalCount"           => "1",
			"totalWeight"          => $order_weight,
			"totalVolume"          => $total_volume,
			"skuTotal"             => $countProducts,
			"skuName"              => $prodcutName,
			"deliveryRequirements" => "",
			"currency"             => "Local",
			"skuDetailList"	=> $product_details
		);
	
		return $param;
	}
	
    public function convertCurrency($amount, $from_currency, $to_currency)
    {
        $api_key = "5642313aab99339f19d9b869";
    
        $base_currency = strtoupper($from_currency);
        $target_currency = strtoupper($to_currency);
        $amount = urlencode($amount);
    
        // API endpoint URL
        $api_url = "https://v6.exchangerate-api.com/v6/".$api_key."/pair/".$base_currency."/".$target_currency."/".$amount;

        // If not using Guzzle, you can use the file_get_contents function
        //$response = file_get_contents($api_url);
        $response = wp_remote_get($api_url);

		if ( is_array( $response ) && ! is_wp_error( $response ) ) {
			$data = json_decode($response["body"], true);
	   
			if (isset($data['result']) && $data['result']=='success') {
				$converted_amount = $data["conversion_result"];
				return $converted_amount;
			} else {
				return $amount;
			}
		}else{
			return $amount;
		}
    
    }
        
    public function imileCountry($country){
    
        $imileCurrency = array(
            "China"                => array("code"=>"CNY","value"=>"CHN"),
            "United Arab Emirates" => array("code"=>"AED","value"=>"UAE"),
            "Saudi Arabia"         => array("code"=>"SAR","value"=>"KSA"), 
            "Oman"                 => array("code"=>"OMR","value"=>"OMN"),
            "Jordan"               => array("code"=>"JOD","value"=>"JOR"),
            "Kuwait"               => array("code"=>"KWD","value"=>"KWT"),
            "Mexico"               => array("code"=>"MXN","value"=>"MEX"),
            "Morocco"              => array("code"=>"MAD","value"=>"MAR"),
            "Bahrain"              => array("code"=>"BHD","value"=>"BHR"),
            "Qatar"                => array("code"=>"QAR","value"=>"QAT"),
        );

        return $imileCurrency[$country];
    }
    
    public function imileCountryar($country){
    
        $imileCurrency = array(
            "الصين"                => array("code"=>"CNY","value"=>"CHN"),
            "الإمارات العربية المتحدة" => array("code"=>"AED","value"=>"UAE"),
            "المملكة العربية السعودية"         => array("code"=>"SAR","value"=>"KSA"), 
            "عمان"                 => array("code"=>"OMR","value"=>"OMN"),
            "الأردن"               => array("code"=>"JOD","value"=>"JOR"),
            "الكويت"               => array("code"=>"KWD","value"=>"KWT"),
            "المكسيك"               => array("code"=>"MXN","value"=>"MEX"),
            "المغرب"              => array("code"=>"MAD","value"=>"MAR"),
            "البحرين"              => array("code"=>"BHD","value"=>"BHR"),
            "قطر"                => array("code"=>"QAR","value"=>"QAT"),
        );

        return $imileCurrency[$country];
    }    
	
}