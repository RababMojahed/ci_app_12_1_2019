<?php
class User_c extends CI_Controller{
	
	public function __construct(){
		parent::__construct();
		$this->load->model('User_m');
		$this->load->library('form_validation');
	}//end __construct function
	public function show_general($page_title,$target_page,$data){

		$data['page_name']=$page_title;		
		$this->load->view('templates/header');
		$this->load->view('user_pages/'.$target_page,$data);
		$this->load->view('templates/footer');

	}//end show_general function
	public function register(){
		$data['page_name']='Sign UP';
		$this->form_validation->set_rules('u_username','Username','required');
		$this->form_validation->set_rules('u_email','Email','required|callback_check_email_exists');
		$this->form_validation->set_rules('u_password','Password','required');
		$this->form_validation->set_rules('u_cpassword','Confirm Password','required|matches[u_password]');

		if($this->form_validation->run()===FALSE){
			$this->show_general('Sign UP','register',$data);
		}
		else{
			$enc_password=md5($this->input->post('u_password'));
			$this->User_m->register_user($enc_password);
			$this->session->set_flashdata('user_registered','You are registered successfully and can login');
			redirect('services_c/index');
			die('Continue');
		}//end if check
	}//end register function

	public function check_email_exists($email){
		$query=$this->form_validation->set_message('check_email_exists','That email is taken.Please choose a different one.');
		if($this->user_m->check_email_exists_db($email)){
			return true;
		}
		else{
			return false;
		}

	}//end check_email_exists function

	public function login(){
		$data['page_name']='Login';
		$this->form_validation->set_rules('u_email','Email','required');
		$this->form_validation->set_rules('u_password','Password','required');

		if($this->form_validation->run()===FALSE){
			$this->show_general('Login','login',$data);
		}
		else{
			//get email and password 
			$email=$this->input->post('u_email');
			$password=md5($this->input->post('u_password'));
			//get user id 
			$userid=$this->User_m->login_user_db($email,$password);
			//-------------chcek user signed up ago --------------
			if($userid):
				//create a session
				$user_data=array(
					'u_id'=>$userid,
					'u_email'=>$email,
					'logged_in'=>true
					);
				$this->session->set_userdata($user_data);
				//set message
				$this->session->set_flashdata('user_loggedin','You are logged in successfully');
				redirect('services_c/index');
			else:
				$this->session->set_flashdata('login_failed','Login is invalid');
				redirect('user_c/login');
			endif;//end if check if user is found in database
			
		}//end if check
	}//end login function

	public function logout(){
		//unset session of user 
		$this->session->unset_userdata('u_id');
		$this->session->unset_userdata('u_email');
		$this->session->unset_userdata('logged_in');

		//set a message
		$this->session->set_flashdata('user_loggedout','You are logged out now');
		redirect('user_c/login');
	}//end logout function
	
}//end User_c class
?>