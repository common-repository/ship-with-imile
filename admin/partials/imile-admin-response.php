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


$orderId = imile_request_parameter('id');
?>

<h3><?php echo esc_html__('iMile Response', 'imile'); ?></h3>
<?php

    $shipping_r = get_post_meta($orderId, 'imile_create_shipping_response', true );
    
    if(!empty($shipping_r)){
      $ordernumber = json_decode($shipping_r, true);
         
				echo '<div id="myModal2"><table class="popuptbl"><tbody>';
                foreach ($ordernumber as $key=>$value) {
					if($key!="imileAwb"){
                      echo "<tr><td>".esc_html($key)."</td><td>".esc_html($value)."</td></tr>";
					}
                }
				echo "</tbody></table></div>";

        
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