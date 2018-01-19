<?php
/*
 * Sign_in Controller
 */
class Area extends CI_Controller {

	/**
	 * Constructor
	 */
	function __construct()
	{
		parent::__construct();

		// Load the necessary stuff...
		$this->load->config('account/account');
		$this->load->helper(array('language', 'account/ssl', 'url','date'));
		$this->load->library(array('account/authentication','account/authorization', 'form_validation'));
		$this->load->model(array('account/account_model', 'account/account_details_model', 'account/acl_role_model'));
		date_default_timezone_set('Asia/Dhaka');  // set the time zone UTC+6					
	}

	/**
	 * Account sign in
	 *
	 * @access public
	 * @return void
	 */
	 
	function index()
	{
		echo "Opps! Wrong URL";
	}
	
	function get_area()
	{
	header('Content-Type: application/json');	
	$data_source='BLASTAPPSV1';			
	$api_name='GET_AREA';
		
	## 	Sec:1 
	##	If API Key match insert the raw request in TABLE: blast_apps_all_requests
	$raw_data=array(
		'raw_params'=>var_export($_POST, true),			
		'status'=>0,
		'data_source'=>$data_source,
		'api_name'=>$api_name,
		'received_datetime'=>mdate('%Y-%m-%d %H:%i:%s', now())					
		);
	
	$request_id=$this->general_model->save_into_table_and_return_insert_id('blast_apps_all_requests', $raw_data);
	
	$api_key=$this->input->post('api_key', TRUE);
	
	if($api_key==$this->config->item("api_key"))
		{	
		$query="SELECT s.`area_id`, s.`area_title` AS area_name, s.`area_location`, s.`area_latitude`, s.`area_longitude`, s.`publish_status`, s.`updated_at`  FROM `blast_geo_area` s ORDER BY s.`area_id` ASC";		
		$result=$this->general_model->get_all_querystring_result($query);
					
			if($result)
			{
			$response["area_list"] = $result; 								
			$response["success"] = 1;									
			echo json_encode($response);						
			}
			else
			{
			$response["success"] = 1;
			$response["message"] = 'No Data Found';
			echo json_encode($response);	
			}
						
		}
		else
		{
		$response["success"] = 0;
		$response["message"] = 'Wrong API Key';
		echo json_encode($response);		
		}
		
		/*
		| Update blast_apps_all_requests status
		|		
		*/	
		if(isset($response["message"]))
		{
		$message=$response["message"];	
		}
		else
		{
		$message=NULL;	
		}
		
		$update_data=array(										
				'status'=>1,						
				'error_msg'=>$message,
				'comments'=>substr(json_encode($response),0, 150)."..."
			);			
		$this->general_model->update_table('blast_apps_all_requests', $update_data, 'request_id', $request_id);
	}
	
	function get_user_area()
	{
	header('Content-Type: application/json');	
	$data_source='BLASTAPPSV1';			
	$api_name='GET_USER_AREA';
	$is_error=0;
		
	## 	Sec:1 
	##	If API Key match insert the raw request in TABLE: blast_apps_all_requests
	$raw_data=array(
		'raw_params'=>var_export($_POST, true),			
		'status'=>0,
		'data_source'=>$data_source,
		'api_name'=>$api_name,
		'received_datetime'=>mdate('%Y-%m-%d %H:%i:%s', now())					
		);
	
	$request_id=$this->general_model->save_into_table_and_return_insert_id('blast_apps_all_requests', $raw_data);
	
	$api_key=$this->input->post('api_key', TRUE);
	$user_name=$this->input->post('user_name', TRUE);
	if ( ! $user = $this->account_model->get_by_username_email($this->input->post('user_name', TRUE)))
		{
			$response["success"] = 0;
			$response["message"] = 'Username or Email does not exist';
			echo json_encode($response);
			$is_error=1;
		}
		else
		{	
			if($api_key==$this->config->item("api_key")) 
			{
					
			$query="SELECT blast_a3m_account.id,
		   blast_a3m_account.username,
		   blast_a3m_account.email,
		   blast_geo_area.area_id,
		   blast_geo_area.area_title as area_name,
		   blast_geo_area.area_location,
		   blast_geo_area.area_latitude,
		   blast_geo_area.area_longitude,
		   blast_geo_area.area_division,
		   blast_geo_area.area_district,
		   blast_geo_area.area_upazila,
		   blast_geo_area.area_union,
		   blast_geo_area.area_description,
		   blast_geo_area.publish_status
	  FROM (blast_area_user_map blast_area_user_map
			INNER JOIN blast_geo_area blast_geo_area
			   ON (blast_area_user_map.area_id = blast_geo_area.area_id))
		   INNER JOIN blast_a3m_account blast_a3m_account
			  ON (blast_a3m_account.id = blast_area_user_map.user_id)
	 WHERE     (blast_a3m_account.username = '".$user_name."')
		   AND (blast_geo_area.publish_status = 1)
	ORDER BY blast_geo_area.area_id ASC";		
				$result=$this->general_model->get_all_querystring_result($query);
						
				if($result)
				{
				$response["user_area_list"] = $result; 								
				$response["success"] = 1;									
				echo json_encode($response);						
				}
				else
				{
				$response["success"] = 1;
				$response["message"] = 'No Data Found';
				echo json_encode($response);	
				}
						
			}
			else
			{
			$response["success"] = 0;
			$response["message"] = 'Wrong API Key';
			echo json_encode($response);		
			}
		
		}
		/*
		| Update blast_apps_all_requests status
		|		
		*/	
		if(isset($response["message"]))
		{
		$message=$response["message"];	
		}
		else
		{
		$message=NULL;	
		}
		
		$update_data=array(										
				'status'=>1,						
				'error_msg'=>$message,
				'comments'=>substr(json_encode($response),0, 150)."..."
			);			
		$this->general_model->update_table('blast_apps_all_requests', $update_data, 'request_id', $request_id);
	}
	

}


/* End of file sign_in.php */
/* Location: ./application/account/controllers/api/information.php */
?>