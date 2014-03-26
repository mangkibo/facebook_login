<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Welcome extends CI_Controller {
	public function __construct() {
		parent::__construct();
		date_default_timezone_set('Asia/Jakarta');
		$this->load->library('facebook');
	} 
	
	public function index() {
		$this->load->view('welcome_message');
	}
	
	public function afterlogin() {
		if ($this->session->userdata("logged_in") == "1") {
			$this->load->view('view_afterlogin');
		} else {
			redirect(base_url());
		}
	}
	
	public function auth() {
		$access_token 	= $this->facebook->getAccessToken();
		$user 			= $this->facebook->getUser();
		$redirect_uri	= base_url() . 'welcome/afterlogin';
		
		if ($user) {
			try {
				$fbdata = $this->facebook->api('/me');
				$permissions_list = $this->facebook->api(
											'/me/permissions',
											'GET',
											array('access_token' => $access_token)
										);
				$permissions_needed = array('publish_stream', 'email', 'read_stream');
				
				foreach($permissions_needed as $perm) {
					if( !isset($permissions_list['data'][0][$perm]) || $permissions_list['data'][0][$perm] != 1 ) {
						$login_url_params = array(
							'scope' => 'read_stream, publish_stream, email',
							'fbconnect' =>  1,
							'display'   =>  "page",
							'next' => $redirect_uri
						);
						$login_url = $this->facebook->getLoginUrl($login_url_params);
						print("<script type=\"text/javascript\">top.location = \"");
						print($login_url);
						print("\";</script>");
						die();
					}
				}
				
				$fbdata['logged_in'] = "1";
				$this->session->set_userdata($fbdata);
				redirect(base_url() . "welcome/afterlogin");
			} catch (FacebookApiException $e) {
				$url = $this->facebook->getLoginUrl(array('scope' => "read_stream,publish_stream,email",
														'redirect_uri' => $redirect_uri
													)
												);
				redirect($url);
				die();
			}
		} else {
			$url = $this->facebook->getLoginUrl(array('scope' => "read_stream,user_status,user_about_me,publish_stream,email",
														'redirect_uri' => $redirect_uri
													)
												);
			redirect($url);
			die();
		}
	}
	
	public function dologout() {
		$this->session->sess_destroy();
		redirect(base_url());
	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */