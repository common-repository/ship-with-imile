<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       https://www.imile.com
 * @since      1.0.1
 *
 * @package    Imile
 * @subpackage Imile/admin/partials
 */
defined( 'ABSPATH' ) || exit;

global $wpdb, $wp_query;


$nonce = wp_create_nonce('imile-data'); 

function imile_request_parameter( $key, $default = '' ) {
    // If not request set
    if ( ! isset( $_REQUEST[ $key ] ) || empty( $_REQUEST[ $key ] ) ) {
        return $default;
    }
 
    // Set so process it
    return wp_kses_post( (string) wp_unslash( $_REQUEST[ $key ] ) );
}

$orderId = imile_request_parameter('id');
?>
<!-- This file should primarily consist of HTML with a little bit of PHP. -->

<button class="imile_show_invoice" data-order_id="<?php echo esc_attr($orderId); ?>" data-nonce="<?php echo esc_attr( $nonce ); ?>"><?php echo esc_html__('Download Imile Label', 'imile'); ?></button>

<h3><?php echo esc_html__('Order Tracking', 'imile'); ?></h3>
<?php
$api = new Imile_Api();
$optionKey = "woocommerce_imile_shipping_method_settings";
$accessToken = $api->getAccessToken($optionKey);

if($accessToken){
    
    
    
    $shipping_r = get_post_meta($orderId, 'imile_create_shipping_response', true );
    
    $result = [];
    if(!empty($shipping_r)){
        $ordernumber = json_decode($shipping_r, true);
        if($accessToken && isset($ordernumber["expressNo"])){
            $param = [
                "orderType"=>"1",
                "orderNo" => $ordernumber["expressNo"], 
                "language"=>"2"
            ];
            $result = $api->imilegetTrackOrder($optionKey, $param, $accessToken);
           
            if(!empty($result["locus"])){
				echo '<div id="myModal2"><table class="popuptbl"><thead><th>',esc_html__("Date", "imile"),'</th><th>',esc_html__('Status', 'imile'),'</th></thead><tbody>';
                foreach ($result["locus"] as $locus) {
					
                    echo "<tr><td>".esc_html($locus["latestStatusTime"])."</td><td>".esc_html($locus["locusDetailed"])."</td></tr>";
                }
				echo "</tbody></table></div>";
            }
        }
    }
}

?>
<style>
#myModal2 table, #myModal2 td, #myModal2 th {
    border: 1px solid #ddd;
    text-align: left;
    white-space: nowrap;
}
#myModal2 table {
    border-collapse: collapse;
    width: 100%;
}
#myModal2 table tbody tr:nth-child(odd) {
    background: #e2e2e2;
}	
#myModal2 th, #myModal2 td {
    padding: 15px;
}	
</style>