=== iMile Delivery Shipping for Woocommerce ===
Contributors: shipwithimile2804
Donate link: https://www.imile.com/
Tags: iMile, WooCommerce iMile Shipping, Ship to iMile
Requires at least: 4.6
Tested up to: 6.6.1
Requires PHP: 5.3
Stable tag: 1.0.1
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html


The iMile Delivery Plugin Automatically push and create shipments to iMile platform using iMile API.

== Description ==

iMile Delivery is a company that provides logistics and transportation services to businesses and individuals. 
 
iMile offers a range of services, including last mile delivery, same-day delivery and next-day delivery. 

iMile Delivery specializes in e-commerce logistics and has developed a proprietary platform that connects retailers, marketplaces, and logistics service providers to streamline the delivery process.

This plugin basically is meant for eCommerce stores hosted on the woocommerce platform where they can start shipping with iMile.

= Features: =

<ul>
<li>Automatically push and create shipments to iMile platform</li>
<li>Generate & download shipping labels</li>
<li>Track shipment status</li>
<li>Cancel or update shipment information before the shipments get pickup</li>
<li>Assign orders in bulk to iMile</li>
</ul>


== Installation ==

1. Upload the plugin folder to the '/wp-content/plugins/' directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Now you need configure the plugin: Enter your Customer ID, Secret Key, Shipper Name, Shipper Contact Person, Contact Number, Pickup Type and check option "Enable this shipping method".


== Screenshots ==

1. screenshot-1.png
2. screenshot-2.png
3. screenshot-3.png


== Changelog ==

= 1.0 
* Tested with WooCommerce 8.5.2

== Credits ==

We Used some 3rd Party API to get current exchange rates, to get customer's current IP and to sync and push orders details to iMile Plateforms. All API's url and details are mentioned below:

1. https://openapi.52imile.cn - To Sync, Push & Create Shippment for Test Account.
2. https://openapi.imile.com - To Sync, Push & Create Shippment for Live Account.
== Find Terms & Condition on this URL - https://www.imile.com/

3. https://v6.exchangerate-api.com/v6/ - To Get Current exchange rates for different currencies. 
== Find Terms & Condition on this URL - https://www.exchangerate-api.com/terms

4. http://ipinfo.io - To get Time zone of customer through his/her IP address.
== Find Terms & Condition on this URL - https://ipinfo.io/terms-of-service


== Changelog ==

= 1.0.1 =
* Fix AWB column issue.
* bug fixes and enhancements

= 1.0.0 =
* first version!