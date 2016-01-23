<?php if ( ! defined('BASEPATH')) exit('No direct script access allowed');

class Publication extends CI_Controller {

	function __construct(){
	    parent::__construct();
	    $this->load->model('laboratory');
	}
	public function index()
	{
	    $data = new stdClass();
	    $data->journal = $this->laboratory->get_journal();
	    $data->conference= $this->laboratory->get_conference();

	    $inside = new stdClass();
	    $inside->content = $this->load->view('publication',$data,true);
	    $this->buildpage->build("ch",$inside, array('type'=>'publication'));
	}
}

