<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Pro_act extends CI_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('activity');
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->activities = $this->activity->get_activities();
	    $data->talks= $this->activity->get_talks();
	    $data->reviewers= $this->activity->get_reviewer();

	    $inside = new stdClass();
	    $inside->content = $this->load->view('pro_act',$data,true);
	    $this->buildpage->build("ch",$inside, array('type'=>'pro_act'));

	}
}

/* End of file welcome.php */
/* Location: ./application/controllers/welcome.php */
