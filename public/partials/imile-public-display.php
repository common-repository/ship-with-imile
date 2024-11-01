<?php

/**
 * Provide a public-facing view for the plugin
 *
 * This file is used to markup the public-facing aspects of the plugin.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/public/partials
 */
?>

<h3><?php echo esc_html__('Order Tracking', 'imile'); ?></h3>
<?php
$api = new Imile_Api();
$optionKey = "woocommerce_imile_shipping_method_settings";
$accessToken = $api->getAccessToken($optionKey);

if($accessToken){
    $shipping_r = get_post_meta( $order_id, 'imile_create_shipping_response', true );
    $result = [];
    if(!empty($shipping_r)){
        $ordernumber = json_decode($shipping_r, true);
        if($accessToken){
            $param = [
                "orderType"=>"1",
                "orderNo" => $ordernumber["expressNo"], 
                "language"=>"2"
            ];
            $result = $api->imilegetTrackOrder($optionKey, $param, $accessToken);
            if(!empty($result["locus"])){
                foreach ($result["locus"] as $locus) {
                    echo "<p>",esc_html($locus["latestStatus"]),': ',esc_html($locus["locusDetailed"]),"</p><br/>";
                }
            }
        }
    }
}
?>