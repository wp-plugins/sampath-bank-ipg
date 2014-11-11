<?php
if ( ! defined( 'ABSPATH' ) ) exit;
/*
Plugin Name: Sampath Bank IPG
Plugin URI: sampathipg.oganro.net
Description: Sampath Bank Payment Gateway from Oganro (Pvt)Ltd.
Version: 1.0
Author: Oganro | Maleewa Jayaweera
Author URI: www.oganro.com
*/

add_action('plugins_loaded', 'woocommerce_sampath_gateway', 0);

function woocommerce_sampath_gateway(){
  if(!class_exists('WC_Payment_Gateway')) return;

  class WC_Sampath extends WC_Payment_Gateway{
    public function __construct(){
	  $plugin_dir = plugin_dir_url(__FILE__);
      $this->id = 'SampathIPG';	  
	  $this->icon = apply_filters('woocommerce_Paysecure_icon', ''.$plugin_dir.'sampath.jpg');
      $this->medthod_title = 'SampathIPG';
      $this->has_fields = false;
 
      $this->init_form_fields();
      $this->init_settings(); 
	  
      $this->title 				= $this -> settings['title'];
      $this->description 		= $this -> settings['description'];
      $this->merchant_id 		= $this -> settings['merchant_id'];
	  $this->pg_instance_id 	= $this -> settings['pg_instance_id'];
	  $this->perform 			= $this -> settings['perform'];
	  $this->currency_code 		= $this -> settings['currency_code'];	  	  
	  $this->hash_key 			= $this -> settings['hash_key'];	  
      $this->redirect_page_id 	= $this-> settings['redirect_page_id'];
      $this->liveurl 			= $this-> settings['pg_domain'];
	  $this->sucess_responce_code	= $this-> settings['sucess_responce_code'];	  
	  $this->responce_url_sucess	= $this-> settings['responce_url_sucess'];
	  $this->responce_url_fail		= $this-> settings['responce_url_fail'];	  	  
	  $this->checkout_msg			= $this-> settings['checkout_msg'];	  
	   
      $this->msg['message'] 	= "";
      $this->msg['class'] 		= "";
 
      add_action('init', array(&$this, 'check_SampathIPG_response'));	  
	  	  
		if ( version_compare( WOOCOMMERCE_VERSION, '2.0.0', '>=' ) ) {
        	add_action( 'woocommerce_update_options_payment_gateways_'.$this->id, array( &$this, 'process_admin_options' ) );
		} else {
            add_action( 'woocommerce_update_options_payment_gateways', array( &$this, 'process_admin_options' ) );
        }
      add_action('woocommerce_receipt_SampathIPG', array(&$this, 'receipt_page'));
	 
   }
	
    function init_form_fields(){
 
       $this -> form_fields = array(
                'enabled' => array(
                    'title' => __('Enable/Disable', 'ogn'),
                    'type' => 'checkbox',
                    'label' => __('Enable Sampath IPG Module.', 'ognro'),
                    'default' => 'no'),
					
                'title' => array(
                    'title' => __('Title:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('This controls the title which the user sees during checkout.', 'ognro'),
                    'default' => __('Sampath IPG', 'ognro')),
				
				'description' => array(
                    'title' => __('Description:', 'ognro'),
                    'type'=> 'textarea',
                    'description' => __('This controls the description which the user sees during checkout.', 'ognro'),
                    'default' => __('Sampath IPG', 'ognro')),	
					
				'pg_domain' => array(
                    'title' => __('PG Domain:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('IPG data submiting to this URL', 'ognro'),
                    'default' => __('https://www.paystage.com/AccosaPG/verify.jsp', 'ognro')),	
					
				'merchant_id' => array(
                    'title' => __('PG Merchant Id:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('Unique ID for the merchant acc, given by bank.', 'ognro'),
                    'default' => __('', 'ognro')),
				
				'pg_instance_id' => array(
                    'title' => __('PG Instance Id:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('collection of intiger numbers, given by bank.', 'ognro'),
                    'default' => __('', 'ognro')),
				
				'perform' => array(
                    'title' => __('PG perform:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('Collection of intiger numbers, given by bank.', 'ognro'),
                    'default' => __('initiatePaymentCapture#sale', 'ognro')),
				
				'currency_code' => array(
                    'title' => __('PG Currency Code LKR:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('You\'r currency type of the account. 144 (LKR) 840 (USD) ...', 'ognro'),
                    'default' => __('144', 'ognro')),
					
				'hash_key' => array(
                    'title' => __('PG Hash Key:', 'ognro'),
                    'type'=> 'text',
                    'description' => __('Collection of mix intigers and strings , given by bank.', 'ognro'),
                    'default' => __('', 'ognro')),
					
				'sucess_responce_code' => array(
                    'title' => __('Sucess responce code :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('50020 - Transaction Passed | 50097 - Test Transaction Passed', 'ognro'),
                    'default' => __('50097', 'ognro')),	  
								
				'checkout_msg' => array(
                    'title' => __('Checkout Message:', 'ognro'),
                    'type'=> 'textarea',
                    'description' => __('Message display when checkout'),
                    'default' => __('Thank you for your order, please click the button below to pay with the secured Sampath Bank payment gateway.', 'ognro')),		
					
				'responce_url_sucess' => array(
                    'title' => __('Sucess redirect URL :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('After payment is sucess redirecting to this page.'),
                    'default' => __('http://your-site.com/thank-you-page/', 'ognro')),
				
				'responce_url_fail' => array(
                    'title' => __('Fail redirect URL :', 'ognro'),
                    'type'=> 'text',
                    'description' => __('After payment if there is an error redirecting to this page.', 'ognro'),
                    'default' => __('http://your-site.com/error-page/', 'ognro'))	
            );
    }
 
	public function admin_options(){
    	echo '<h3>'.__('Sampath bank online payment gateway', 'ognro').'</h3>';
        echo '<p>'.__('<a target="_blank" href="http://www.oganro.com/">Oganro</a> is a fresh and dynamic web design and custom software development company with offices based in East London, Essex, Brisbane (Queensland, Australia) and in Colombo (Sri Lanka).').'</p>';
        echo '<table class="form-table">';        
        $this->generate_settings_html();
        echo '</table>'; 
    }
	

    function payment_fields(){
        if($this -> description) echo wpautop(wptexturize($this -> description));
    }

    function receipt_page($order){        		
		global $woocommerce;
        $order_details = new WC_Order($order);
        
        echo $this->generate_ipg_form($order);		
		echo '<br>'.$this->checkout_msg.'</b>';        
    }
    	
    public function generate_ipg_form($order_id){
 
        global $wpdb;
        global $woocommerce;
        
        $order          = new WC_Order($order_id);
		$productinfo    = "Order $order_id";		
        $currency_code  = $this -> currency_code;		
		$curr_symbole 	= get_woocommerce_currency();		
		
		$messageHash = $this -> pg_instance_id."|".$this -> merchant_id."|".$this -> perform."|".$currency_code."|".(($order -> order_total) * 100)."|".$order_id."|".$this	-> hash_key."|";
$message_hash = "CURRENCY:7:".base64_encode(sha1($messageHash, true));
		
						
		$table_name = $wpdb->prefix . 'sampath_ipg';		
		$check_oder = $wpdb->get_var( "SELECT COUNT(*) FROM $table_name WHERE merchant_reference_no = '".$order_id."'" );
        
		if($check_oder > 0){
			$wpdb->update( 
				$table_name, 
				array( 
					'transaction_id' => '',					
					'transaction_type_code' => '',
					'currency_code' => $this->currency_code,
					'amount' => ($order->order_total),
					'status' => 0000,
					'or_date' => date('Y-m-d'),
					'installments' => '',
					'exponent' => '',
					'3ds_eci' => '',
					'pg_error_code' => '',
					'pg_error_detail' => '',
					'pg_error_msg' => '',
					'message_hash' => ''
				), 
				array( 'merchant_reference_no' => $order_id ));								
		}else{
			
			$wpdb->insert($table_name, array( 'transaction_id'=>'', 'merchant_reference_no'=>$order_id, 'transaction_type_code'=>'', 'currency_code'=>$this->currency_code, 'amount'=>$order->order_total, 'status'=>00000,'or_date' => date('Y-m-d'), 'installments'=>'', 'exponent'=>'', '3ds_eci'=>'', 'pg_error_code'=>'', 'pg_error_detail'=>'', 'pg_error_msg'=>'', 'message_hash'=>'' ), array( '%s', '%d' ) );					
		}		
				
		
        $form_args = array(
		  'merchant_id' => $this -> merchant_id,
          'pg_instance_id' => $this -> pg_instance_id,          
          'perform' => $this -> perform,
          'currency_code' => $currency_code,
          'amount' => (($order -> order_total ) * 100 ),
          'merchant_reference_no' => $order_id,
          'order_desc' => $productinfo,
          'message_hash' => $message_hash
		  );
		  
        $form_args_array = array();
        foreach($form_args as $key => $value){
          $form_args_array[] = "<input type='hidden' name='$key' value='$value'/>";
        }
        return '<p>'.$percentage_msg.'</p>
		<p>Total amount will be <b>'.$curr_symbole.' '.number_format(($order->order_total)).'</b></p>
		<form action="'.$this -> liveurl.'" method="post" id="merchantForm">
            ' . implode('', $form_args_array) . '
            <input type="submit" class="button-alt" id="submit_ipg_payment_form" value="'.__('Pay via Credit Card', 'ognro').'" /> 
			<a class="button cancel" href="'.$order->get_cancel_order_url().'">'.__('Cancel order &amp; restore cart', 'ognro').'</a>            
            </form>'; 
    }
    	
    function process_payment($order_id){
        $order = new WC_Order($order_id);
        return array('result' => 'success', 'redirect' => add_query_arg('order',           
		   $order->id, add_query_arg('key', $order->order_key, get_permalink(woocommerce_get_page_id('pay' ))))
        );
    }
 
   	 
    function check_SampathIPG_response(){				
        global $wpdb;
        global $woocommerce;
        
		if(isset($_POST['transaction_type_code']) && isset($_POST['status']) && isset($_POST['merchant_reference_no'])){			
			$order_id = $_POST['merchant_reference_no'];
			
			if($order_id != ''){				
				$order 	= new WC_Order($order_id);
				
				$amount = $_POST['amount'];
				$status = $_POST['status'];
				if($this->sucess_responce_code == $_POST['status']){
						
				$table_name = $wpdb->prefix . 'sampath_ipg';	
				$wpdb->update( 
				$table_name, 
				array( 
					'transaction_id' => $_POST["transaction_id"],					
					'transaction_type_code' => $_POST["transaction_type_code"],					
					'status' => $_POST["status"],
					'installments' => $_POST["installments"],
					'exponent' => $_POST["exponent"],
					'3ds_eci' => $_POST["3ds_eci"],
					'pg_error_code' => $_POST["pg_error_code"],
					'pg_error_detail' => $_POST["pg_error_detail"],
					'pg_error_msg' => $_POST["pg_error_msg"],
					'message_hash' => $_POST["message_hash"]
				), 
				array( 'merchant_reference_no' => $_POST["merchant_reference_no"] ));
									
                    $order->add_order_note('Sampath payment successful<br/>Unnique Id from Sampath IPG: '.$_POST['transaction_id']);
                    $order->add_order_note($this->msg['message']);
                    $woocommerce->cart->empty_cart();
					
					$mailer = $woocommerce->mailer();

					$admin_email = get_option( 'admin_email', '' );

$message = $mailer->wrap_message(__( 'Order confirmed','woocommerce'),sprintf(__('Order '.$_POST["transaction_id"].' has been confirmed', 'woocommerce' ), $order->get_order_number(), $posted['reason_code']));	
$mailer->send( $admin_email, sprintf( __( 'Payment for order %s confirmed', 'woocommerce' ), $order->get_order_number() ), $message );					
					
					
$message = $mailer->wrap_message(__( 'Order confirmed','woocommerce'),sprintf(__('Order '.$_POST["transaction_id"].' has been confirmed', 'woocommerce' ), $order->get_order_number(), $posted['reason_code']));	
$mailer->send( $order->billing_email, sprintf( __( 'Payment for order %s confirmed', 'woocommerce' ), $order->get_order_number() ), $message );

					$order->payment_complete();
					wp_redirect( $this->responce_url_sucess, 200 ); exit;
					
				}else{					
					global $wpdb;
                    
                    $order->update_status('failed');
                    $order->add_order_note('Failed - Code'.$_POST['pgErrorCode']);
                    $order->add_order_note($this->msg['message']);
							
					$table_name = $wpdb->prefix . 'sampath_ipg';	
					$wpdb->update( 
					$table_name, 
					array( 
						'transaction_id' => $_POST["transaction_id"],					
						'transaction_type_code' => $_POST["transaction_type_code"],					
						'status' => $_POST["status"],
						'installments' => $_POST["installments"],
						'exponent' => $_POST["exponent"],
						'3ds_eci' => $_POST["3ds_eci"],
						'pg_error_code' => $_POST["pg_error_code"],
						'pg_error_detail' => $_POST["pg_error_detail"],
						'pg_error_msg' => $_POST["pg_error_msg"],
						'message_hash' => $_POST["message_hash"]
					), 
					array( 'merchant_reference_no' => $_POST["merchant_reference_no"] ));
					
					wp_redirect( $this->responce_url_fail, 200 ); exit;
				}				 
			}
			
		}
    }
    
    function get_pages($title = false, $indent = true) {
        $wp_pages = get_pages('sort_column=menu_order');
        $page_list = array();
        if ($title) $page_list[] = $title;
        foreach ($wp_pages as $page) {
            $prefix = '';            
            if ($indent) {
                $has_parent = $page->post_parent;
                while($has_parent) {
                    $prefix .=  ' - ';
                    $next_page = get_page($has_parent);
                    $has_parent = $next_page->post_parent;
                }
            }            
            $page_list[$page->ID] = $prefix . $page->post_title;
        }
        return $page_list;
    }
}


if(isset($_POST['transaction_type_code']) && isset($_POST['status']) && isset($_POST['merchant_reference_no'])){
	$WC = new WC_Sampath();
}

   
   function woocommerce_add_sampath_gateway($methods) {
       $methods[] = 'WC_Sampath';
       return $methods;
   }
	 	
    add_filter('woocommerce_payment_gateways', 'woocommerce_add_sampath_gateway' );
}

	global $jal_db_version;
	$jal_db_version = '1.0';
	
	function jal_install() {		
		global $wpdb;
		global $jal_db_version;
	
		$table_name = $wpdb->prefix . 'sampath_ipg';
		$charset_collate = '';
	
		if ( ! empty( $wpdb->charset ) ) {
		  $charset_collate = "DEFAULT CHARACTER SET {$wpdb->charset}";
		}
	
		if ( ! empty( $wpdb->collate ) ) {
		  $charset_collate .= " COLLATE {$wpdb->collate}";
		}
	
		$sql = "CREATE TABLE $table_name (
					id int(9) NOT NULL AUTO_INCREMENT,
					transaction_id int(9) NOT NULL,
					merchant_reference_no VARCHAR(20) NOT NULL,
					transaction_type_code VARCHAR(20) NOT NULL,
					currency_code int(6) NOT NULL,
					amount VARCHAR(20) NOT NULL,
					status int(6) NOT NULL,
					or_date DATE NOT NULL,
					installments VARCHAR(20) NOT NULL,
					exponent text NOT NULL,
					3ds_eci text NOT NULL,
					pg_error_code text NOT NULL,
					pg_error_detail text NOT NULL,
					pg_error_msg text NOT NULL,
					message_hash text NOT NULL,					
					UNIQUE KEY id (id)
				) $charset_collate;";
				
	
		require_once( ABSPATH . 'wp-admin/includes/upgrade.php' );
		dbDelta( $sql );
	
		add_option( 'jal_db_version', $jal_db_version );
	}
	
	function jal_install_data() {
		global $wpdb;
		
		$welcome_name = 'Sampath IPG';
		$welcome_text = 'Congratulations, you just completed the installation!';
		
		$table_name = $wpdb->prefix . 'sampath_ipg';
		
		$wpdb->insert( 
			$table_name, 
			array( 
				'time' => current_time( 'mysql' ), 
				'name' => $welcome_name, 
				'text' => $welcome_text, 
			) 
		);
	}
	
	register_activation_hook( __FILE__, 'jal_install' );
	register_activation_hook( __FILE__, 'jal_install_data' );