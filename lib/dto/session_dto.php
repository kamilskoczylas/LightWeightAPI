<?php

class Session_dto extends Basic_dto {

    function __construct(){
      $this->table_name = 'session';
      $this->default_field_values = array('lastcommand_datetime'=>'NOW()'); 
      parent::__construct();
      //$this->session_guid = $session_guid;
    }
    
    public $application_id;
    public $session_guid;
    public $lastcommand_datetime;
}