<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Home extends CI_Controller {

	public function __construct() {
    parent::__construct();

		$this->load->model('Account');
		$this->load->model('GeneralModel');

  }

	public function index() {
    if($this->session->userdata('email')) {

			$this->data['admin'] = $this->GeneralModel->get_admin_list();
			$this->data['super'] = $this->GeneralModel->check_super();
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('home', $this->data);
    }
    else{
      redirect('Login');
    }
	}


	public function add_admin() {

		$this->form_validation->set_rules('first_name','First Name','required');
    $this->form_validation->set_rules('last_name','Lastname','required');
		$this->form_validation->set_rules('email','Email','required|is_unique[member.email]');
    $this->form_validation->set_rules('password','Password','required|min_length[6]');
    $this->form_validation->set_rules('phone','Phone','required|is_unique[member.phone]|max_length[10]');
		if ($this->form_validation->run() == FALSE){

			$this->data['admin'] = $this->GeneralModel->get_admin_list();
			$this->data['super'] = $this->GeneralModel->check_super();
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('home', $this->data);
     }
		 else{
			 $this->Account->save_admin();
			 redirect('Home');
		 }

	}

	public function make_super_admin($member_id,$super) {

		$this->GeneralModel->member_id =  $member_id;
		$this->GeneralModel->make_super_admin($super);
		redirect('Home');

	}

	public function is_super() {

		$data = $this->GeneralModel->check_super();

		if($data['is_super'] == 1) {
			return true;
		}
		else{
			return false;
		}
	}

	public function no_permission() {

		$this->data['side_bar'] = 'template/sidebar';
		$this->load->view('no_permission', $this->data);
	}

	public function add_money() {
    if($this->session->userdata('email') && $this->is_super()) {

		$this->data['member'] = $this->GeneralModel->fetch_all_member();
		$this->data['admin'] = $this->GeneralModel->session_user_data();
		$this->data['side_bar'] = 'template/sidebar';
		$this->load->view('add_money', $this->data);
	}

	elseif($this->session->userdata('email') && !$this->is_super()) {
       redirect('Home/no_permission');
	}
	else{
		redirect('Login');
	}

	}

	public function save_deposit() {

		$this->form_validation->set_rules('phone','Name','required');
    $this->form_validation->set_rules('value','Money','required');
		$this->form_validation->set_rules('depositor_phone','Phone','required');

		if ($this->form_validation->run() == FALSE){

			$this->data['member'] = $this->GeneralModel->fetch_all_member();
			$this->data['admin'] = $this->GeneralModel->session_user_data();
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('add_money', $this->data);
		}
		else{
			$this->GeneralModel->add_deposit();
			redirect('Home/add_money');
		}


	}

	public function deposit_history() {

    if($this->session->userdata('email') && $this->is_super()) {

			$this->load->library('pagination');
            $config['base_url'] = base_url() . "Home/deposit_history";;
            $config['total_rows'] = $this->db->count_all('account');
            $config['per_page'] = 10;
            $config["uri_segment"] = 3;
            $config['full_tag_open']  = '<div class="pagging text-center"><nav><ul class="pagination">';
            $config['full_tag_close']   = '</ul></nav></div>';
            $config['num_tag_open']     = '<li class="page-item"><span class="page-link">';
            $config['num_tag_close']    = '</span></li>';
            $config['cur_tag_open']     = '<li class="page-item active"><span class="page-link">';
            $config['cur_tag_close']    = '<span class="sr-only">(current)</span></span></li>';
            $config['next_tag_open']    = '<li class="page-item"><span class="page-link">';
            $config['next_tagl_close']  = '<span aria-hidden="true">&raquo;</span></span></li>';
            $config['prev_tag_open']    = '<li class="page-item"><span class="page-link">';
            $config['prev_tagl_close']  = '</span></li>';
            $config['first_tag_open']   = '<li class="page-item"><span class="page-link">';
            $config['first_tagl_close'] = '</span></li>';
            $config['last_tag_open']    = '<li class="page-item"><span class="page-link">';
            $config['last_tagl_close']  = '</span></li>';
            $this->pagination->initialize($config);
            $page = ($this->uri->segment(3)) ? $this->uri->segment(3) : 0;

			$this->data['history'] = $this->GeneralModel->get_deposit_history($config["per_page"], $page);
			$this->data['calulate_deposit'] = $this->GeneralModel->sum_all_deposit();
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('deposit_history', $this->data);
		}
		else{
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('no_permission', $this->data);
		}

	}

	public function edit_deposit($deposit_id){

		if($this->session->userdata('email') && $this->is_super()) {

			$this->GeneralModel->deposit_id = $deposit_id;
			$this->data['deposit'] = $this->GeneralModel->get_update_deposit();
			$this->data['member'] = $this->GeneralModel->fetch_all_member();
			$this->data['admin'] = $this->GeneralModel->session_user_data();
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('edit_deposit', $this->data);
		}
		else{
			$this->data['side_bar'] = 'template/sidebar';
			$this->load->view('no_permission', $this->data);
		}

	}

	public function update_deposit($deposit_id) {

		$this->GeneralModel->deposit_id = $deposit_id;
		$this->GeneralModel->update_deposit();
		redirect('Home/deposit_history');
	}

	public function delete_deposit($deposit_id) {

		$this->GeneralModel->deposit_id = $deposit_id;
		$this->GeneralModel->delete_deposit();
		redirect('Home/deposit_history');
	}





}