<?php

class ServiceBuilder {
    protected $_service;
    protected $_requestClass;
    protected $_requestClassName;
    
    protected $_method;
    protected $_class;
    protected $_function;
    
    private $_registred_methods = array(
                'Is', 'Get', 'Update', 'Delete', 'Create', 'Show'
            );
            
    private $_registred_services = array(
                'Item', 'Loadbalancer', 'Useraccount', 'Masterdata', 'Translation'
            );
    
    function __construct($requestName, Response $responseClass)
    {
        $this->_parseRequest($requestName);
        try 
        {
            if ($this->_method == 'Get' || $this->_method == 'Show')
                $this->_requestClass = new $this->_requestClassName($_GET);
            else {
                $aPost = $_POST;
                
                if (isset($_SERVER['HTTP_X_HTTP_CLIENT'])){
                    $headerStringValue = $_SERVER['HTTP_X_HTTP_CLIENT'];
                    
                    if ($headerStringValue == 'Angular'){
                        $json = file_get_contents('php://input');
                        $aPost = json_decode($json, true);
                    }
                }
                
                
                $this->_requestClass = new $this->_requestClassName($aPost);
            }
                
        } catch (ValidationException $validationException)
        {
            $validationException->ReportAndDie();
        }
        
        $this->_service = new $this->_class($responseClass);
    }
    
    function _parseRequest($requestName)
    {
        if (!preg_match('/^(Is|Get|Show|Create|Update|Delete)([A-Z][a-z]+)([A-Z][a-zA-Z]+)*(Request)$/', $requestName, $matches) ||
            count($matches) != 5 ||
            !in_array($matches[1], $this->_registred_methods) || 
            !in_array($matches[2], $this->_registred_services) ||
            !isset($matches[3]) ||
            $matches[4] != 'Request'
            ){
                //throw new BadMethodCallException();
                print('Wrong request');
                exit;
            }
            
        $this->_method = $matches[1];
        $this->_class = $matches[2].'Service';
        $this->_function = $matches[3];
        $this->_requestClassName = $matches[0];
    }
    
    public function Execute()
    {
        $run_method = $this->_method.$this->_function;
        return $this->_service->$run_method($this->_requestClass);
    }
}