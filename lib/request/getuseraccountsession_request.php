<?php

class GetUseraccountSessionRequest extends BasicRequest {
    public $application_guid;
    
    protected $propertiesValidationRules =
        array (
            'application_guid' => array(
                'type' => 'uuid',
                'required' => true
                )
            );
}