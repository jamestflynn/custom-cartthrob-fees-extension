<?php
if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Cartthrob_pre_process_ext
{
	public $settings = array();
    public $name = 'CartThrob Custom Calculate Total Extension';
    public $version = '1.0.1';
    public $description = 'CartThrob pre process extension.';
    public $settings_exist = 'n';
    public $docs_url = 'http://jamesdontmakedocs.com';
    protected $EE;

    public function __construct($settings = '')
    {
	$this->EE =& get_instance();
	$this->settings = $settings;
	$this->EE->load->add_package_path(PATH_THIRD.'cartthrob/');
	$this->EE->load->library('cartthrob_loader');	
    }


    public function activate_extension()
    {
		$this->EE->db->insert(
		    'extensions',
		    array(
			'class' => __CLASS__,
			'method' => 'cartthrob_calculate_total',
			'hook' 	=> 'cartthrob_calculate_total',
			'settings' => '',
			'priority' => 10,
			'version' => $this->version,
			'enabled' => 'y'
		    )
		);
	}
    public function update_extension($current='')
    {
		if ($current == '' OR $current == $this->version)
		{
		    return FALSE;
		}

		$this->EE->db->update(
		    'extensions',
		    array('version' => $this->version),
		    array('class' => __CLASS__)
		);
    }
    public function disable_extension()
    {
	$this->EE->db->delete('extensions', array('class' => __CLASS__));
    }


    public function settings()
    {
	return $settings;
    }
	
	// this is run whenever the transaction is successful
	function cartthrob_calculate_total()
	{

		// ****************  setup of initial/default values  ****************
		$cod_fee_applied = $this->EE->cartthrob->cart->custom_data('cod_fee_applied');
		$res_fee_applied = $this->EE->cartthrob->cart->custom_data('res_fee_applied');
		$taxlevelValue = $this->EE->cartthrob->cart->custom_data('taxlevel');
		$shipping_method = $this->EE->cartthrob->cart->customer_info('shipping_option');
		$customer_state = $this->EE->cartthrob->cart->customer_info('shipping_state');
		$res_fee_price = 3;
		$cod_fee_price = 11;
		$taxit = "no";		
		$free_shipping = "no";		
		$total =  $this->EE->cartthrob->cart->subtotal() + $this->EE->cartthrob->cart->shipping() + $this->EE->cartthrob->cart->tax() - $this->EE->cartthrob->cart->discount();		

		// ****************  should we tax it?  ****************
		if ($taxlevelValue != "") {
				
			  //check if pa no tax field set in admin
			  if($customer_state == "PA" && $taxlevelValue != "PA-NT")	{
			  	$taxit = "yes";
			  }
														   
		} elseif ($customer_state == "PA") {
			$taxit = "yes";
		}	

		// ***********  is shipping free?  ****************
		if ($total > 700 && $shipping_method == 'Standard Ground'){
			$free_shipping = "yes";
		}	


		// ****************  now put it all together!  ****************
		if ($cod_fee_applied == 'Yes' && $res_fee_applied == 'Yes') {
			
			if ($taxit == "yes") {
				
				if ($free_shipping == "yes") {
					$fee_total = 11.66;
				} 
				else {
					$fee_total = 14.84;
				}
			
			} 
			else {
				
				if ($free_shipping == "yes") {
					$fee_total = 11;
				} 
				else {
					$fee_total = 14;
				}					
			
			}
	
		}
		
		elseif ($res_fee_applied == 'Yes' && $cod_fee_applied != 'Yes' && $free_shipping != "yes") {
			
			if ($taxit == "yes") {
				$fee_total = 3.18;
			} 
		
			else {
				$fee_total = 3;
			}									
			
		}	
		
		elseif ($cod_fee_applied == 'Yes' && $res_fee_applied != 'Yes') {
			
			if ($taxit == "yes") {
				$fee_total = 11.66;
			} 
			else {
				$fee_total = 11;
			}	
			
		}	
		else {
			$fee_total = 0;
		}
		
		return $total + $fee_total;  
	}
	// END
}
//END CLASS