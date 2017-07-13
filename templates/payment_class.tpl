<?php

require_once( dirname(__FILE__) . '/../util.php' );

class WC_Gateway_{key} extends WC_Payment_Gateway { // see: https://docs.woocommerce.com/wc-apidocs/class-WC_Payment_Gateway.html
		use AltapayMaster;
		
		public function __construct() {		
			// Set default gateway values
			$this->id = strtolower('altapay_{key}');
			$this->icon = ''; // Url to image
			$this->has_fields = false;
			$this->method_title = 'AltaPay - {name}';
			$this->method_description = __( 'Adds AltaPay Payment Gateway to use with WooCommerce', 'altapay');
			$this->supports = array(
				'refunds',
				'subscriptions',
				'subscription_cancellation',
				'subscription_suspension',
				'subscription_reactivation',
				'subscription_amount_changes',
				'subscription_date_changes',
			);
			
			$this->terminal = '{name}';
			$this->enabled = $this->get_option( 'enabled' );
			$this->title = $this->get_option( 'title' );
			$this->description = $this->get_option( 'description' );
			$this->payment_action = $this->get_option( 'payment_action' );
			$this->currency = $this->get_option( 'currency' );
			$currency = explode(' ', '{name}');
			$this->default_currency = end($currency);

			// Load form fields
			$this->init_form_fields();
			$this->init_settings();
			
			// Add actions
			add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
			add_action( 'woocommerce_receipt_' . $this->id, array( $this, 'receipt_page_altapay' ) );			
			add_action( 'woocommerce_api_wc_gateway_' . $this->id, array( $this, 'check_altapay_response' ) );
	
			// Subscription actions
			add_action( 'woocommerce_scheduled_subscription_payment_altapay', array($this, 'scheduled_subscription_payment'), 10, 2 );					
		}
		
		public function admin_options() {
			echo '<h3>{name}</h3>';
			echo '<table class="form-table">';
            $this->generate_settings_html();
			echo '</table>';	
		}
		
		public function receipt_page_altapay( $order_id ) {
			// Show text
			echo '<p>'.__('You are now going to be redirected to AltaPay Payment Gateway','altapay').'</p>';

			$return = $this->createPaymentRequest( $order_id );
			if ($return instanceof WP_Error) {
				echo '<p>' . $return->get_error_message() . '</p>';
			}
			else {
				echo '<script type="text/javascript">window.location.href = "' . $return . '"</script>';
			}
		}
		
		public function createPaymentRequest( $order_id ) {	
			global $wpdb;
			// Create form request etc.
			$api = $this->api_login();
			
			// Create payment request
			$order = new WC_Order( $order_id );
			
			// TODO Get terminal form instance
			$terminal = $this->terminal;
			
			$amount = $order->order_total;
			$currency = $order->get_order_currency(); //get_woocommerce_currency();
			$customer_info = array(
				'billing_firstname' => $order->billing_first_name,
				'billing_lastname' => $order->billing_last_name,
				'billing_address' => $order->billing_address_1,
				'billing_postal' => $order->billing_postcode,
				'billing_city' => $order->billing_city,
				'billing_region' => $order->billing_state,
				'billing_country' => $order->billing_country,
				'email' => $order->billing_email,
				'customer_phone' => $order->billing_phone,
				'shipping_firstname' => $order->shipping_first_name,
				'shipping_lastname' => $order->shipping_last_name,
				'shipping_address' => $order->shipping_address_1,
				'shipping_postal' => $order->shipping_postcode,
				'shipping_city' => $order->shipping_city,
				'shipping_region' => $order->shipping_state,
				'shipping_country' => $order->shipping_country,
			);
			
			$cookie = isset($_SERVER['HTTP_COOKIE']) ? $_SERVER['HTTP_COOKIE'] : '';
			$language = 'en';
			
			$languages = array('da_DK' => 'da', 'sv_SE' => 'sv', 'nn_NO' => 'no', 'de_DE' => 'de');
			if ($languages[get_locale()]) {
				$language = $languages[get_locale()];
			}
			
			// Get chosen page from altapay settings
			$form_page_id = esc_attr( get_option('altapay_payment_page') );
			$config = array(
				'callback_form' => get_page_link($form_page_id), 
				'callback_ok' => add_query_arg( array('type' => 'ok', 'wc-api' => 'WC_Gateway_'.$this->id), $this->get_return_url($order) ),
				'callback_fail' => add_query_arg( array('type' => 'fail', 'wc-api' => 'WC_Gateway_'.$this->id), $this->get_return_url($order) ),
				'callback_open' => add_query_arg( array('type' => 'open', 'wc-api' => 'WC_Gateway_'.$this->id), $this->get_return_url($order) ),
				'callback_notification' => add_query_arg( array('type' => 'notification', 'wc-api' => 'WC_Gateway_'.$this->id), $this->get_return_url($order) ),
			);
			
			// Make these as settings
			$payment_type = 'payment';
			if ($this->payment_action == 'authorize_capture') {
				$payment_type = 'paymentAndCapture';
			}
			
			// Check if WooCommerce subscriptions is enabled
			if (class_exists( 'WC_Subscriptions_Order' )) {
				// Check if cart containt subscription product
				if( WC_Subscriptions_Order::order_contains_subscription( $order_id ) ) {
					if ($this->payment_action == 'authorize_capture') {
						$payment_type = 'subscriptionAndCharge';
					} else {
						$payment_type = 'subscriptionAndReserve';
					}
				}
			}
			
			// Add orderlines to altapay request
			$linedata = createOrderLines($order);

			if ($linedata instanceof WP_Error) {
				return $linedata; // Some error occurred
			}

			$response = $api->createPaymentRequest($terminal, $order_id, $amount, $currency, $payment_type, $customer_info, $cookie, $language, $config, array(), $linedata);
			
			if( !$response->wasSuccessful() ) {
				error_log('Could not create the payment request: ' . $response->getErrorMessage());
				$order->add_order_note( __('Could not create the payment request: ' . $response->getErrorMessage(), 'Altapay') );
				return new WP_Error( 'error', 'Could not create the payment request' );
			}

			$redirectURL = $response->getRedirectURL();
			
			return $redirectURL;
		}
		
		public function check_altapay_response() {	
			// Check if callback is altapay and the allowed IP
			if ( $_GET['wc-api'] == 'WC_Gateway_'.$this->id ) {
				global $woocommerce;
				$postdata = $_POST;
				
				$order_id = sanitize_text_field($postdata['shop_orderid']);
			 	$order = new WC_Order($order_id);
			 	$txnid = sanitize_text_field($postdata['transaction_id']);
			 	$cardno = sanitize_text_field($postdata['masked_credit_card']);
			 	//$amount = sanitize_text_field($postdata['amount']);
			 	$credtoken = sanitize_text_field($postdata['credit_card_token']);
			 	$error_message = sanitize_text_field($postdata['error_message']);
				$merchant_error_message = sanitize_text_field($postdata['merchant_error_message']);
				$payment_status = sanitize_text_field($postdata['payment_status']);
				$status = sanitize_text_field($postdata['status']);
			 	
			 	// TODO Clean up
			 	
			 	// If order already on-hold
			 	if ($order->has_status('on-hold')) {
			 	
			 		if ($status == 'succeeded') {
			 		
			 			$order->add_order_note(__('Notification completed', 'Altapay'));                
						$order->payment_complete();
						
						update_post_meta($order_id, '_transaction_id', $txnid);
						update_post_meta($order_id, '_cardno', $cardno);
						update_post_meta($order_id, '_credit_card_token', $credtoken);
						
			 		} else {
			 			if ($status == 'error' || $status == 'failed') {
			 				$order->update_status('failed', 'Payment failed: ' . $error_message);		
			 				$order->add_order_note( __('Payment failed: ' . $error_message .' Merchant error: ' . $merchant_error_message, 'Altapay') ); 				 				 			
			 			} 
			 		}
			 		
			 		exit;
			 	}
			 	
			 	if ($status == 'open') {
			 		$order->update_status('on-hold', 'The payment is pending an update from the payment provider.');

			 		$redirect = $this->get_return_url($order);
					wp_redirect($redirect);
			 		exit;
			 	}
			 	
			 	if ($payment_status == 'released') {
			 		$order->add_order_note( __('Payment failed: payment released', 'Altapay') ); 
					wc_add_notice( __('Payment error:', 'altapay') . ' Payment released', 'error' );
					$cart_url = $woocommerce->cart->get_checkout_url();
					wp_redirect($cart_url);
					//$redirect = $order->get_cancel_order_url();
					//wp_redirect($redirect);				
					exit;
			 	}
			 	
			 	if ($_GET['cancel_order']) {
					$order->add_order_note( __('Payment failed: ' . $error_message .' Merchant error: ' . $merchant_error_message, 'Altapay') ); 
					wc_add_notice( __('Payment error:', 'altapay') . ' '.$error_message, 'error' );
					$cart_url = $woocommerce->cart->get_checkout_url();
					wp_redirect($cart_url);			
					exit;
				}
			 	
			 	// Make some validation			 	
			 	if ($error_message || $merchant_error_message) {
			 		$order->add_order_note( __('Payment failed: ' . $error_message .' Merchant error: ' . $merchant_error_message, 'Altapay') ); 
					wc_add_notice( __('Payment error:', 'altapay') . ' '.$error_message, 'error' );
					$cart_url = $woocommerce->cart->get_checkout_url();
					wp_redirect($cart_url);					
					exit;
			 	}
			 				 	
			 	if( $order->has_status('pending') && $status == 'succeeded' ) {
					// Payment completed
					$order->add_order_note(__('Callback completed', 'Altapay'));                
					$order->payment_complete();
				
					update_post_meta($order_id, '_transaction_id', $txnid);
					update_post_meta($order_id, '_cardno', $cardno);
					update_post_meta($order_id, '_credit_card_token', $credtoken);
				}
				
				// Redirect to Accept page
				$redirect = $this->get_return_url($order);
				wp_redirect($redirect);
				exit;
			}
		}		
		
}
