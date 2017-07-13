<?php
/**
 * The template for displaying altapay payment form
 *
 *
 * @package Altapay
 */
 
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

get_header();

//$altapay = new WC_Gateway_Altapay();
?>

<div id="primary" class="content-area">
	<main id="main" class="site-main" role="main">
		<ul>
			<li><?php echo __('Order ID', 'woocommerce') ?>: <?php echo esc_html($_POST['shop_orderid']); ?></li>
			<li><?php echo __('Price', 'woocommerce'); ?>: <?php echo esc_html($_POST['amount']); //$altapay->altapay_get_currency_code($_POST['currency']); ?></li>
		</ul>
		<form id="PensioPaymentForm"></form> 
	</main>
</div>

<?php get_sidebar(); ?>

<?php get_footer(); ?>
