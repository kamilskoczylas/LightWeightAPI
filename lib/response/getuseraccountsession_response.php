<?php

class GetUseraccountSessionResponse extends BasicResponse {
    public $session_guid;
    
    protected $propertiesValidationRules =
        array (
            'session_guid' => array(
                'type' => 'guid',
                'required' => true
                ),
            );
}
