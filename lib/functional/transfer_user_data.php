<?php
define('VALIDATION_ERROR', 'Validation Error');
define('VALIDATION_REQUEST_TOO_BIG', 'The request exceeds defined size');
define('VALIDATION_NO_VALID_ID', 'This is not valid identificator');
define('VALIDATION_NO_VALID_BOOL', 'This is not valid boolean');
define('VALIDATION_NO_VALID_DATE', 'This is not valid datetime');
define('VALIDATION_MISSING_REQUIRED_VALUE', 'Field %s is required');
define('VALIDATION_VALUE_MINIMUM_LENGTH', 'Field %s should have minimum %d characters');
define('VALIDATION_VALUE_MAXIMIM_LENGTH', 'Field %s should have maximum %d characters');

/* https://www.php.net/manual/en/filter.filters.sanitize.php
FILTER_SANITIZE_EMAIL
FILTER_SANITIZE_URL
FILTER_UNSAFE_RAW
FILTER_SANITIZE_SPECIAL_CHARS
FILTER_SANITIZE_NUMBER_INT
FILTER_SANITIZE_NUMBER_FLOAT

htmlspecialchars() 
strip_tags

$size = (int) $_SERVER['CONTENT_LENGTH'];

*/
class ValidationException extends Exception 
{
    protected $validator;
    
    function __construct(BasicValidation $Validator)
    {
            $this->validator = $Validator;
            parent::__construct(VALIDATION_ERROR);
    }
    
    function GetErrors() {
        return $this->validator->GetErrorList();
    }
    
    function ReportAndDie()
    {
        header('Content-Type:application/json;charset=utf-8');
        echo json_encode(
            array(
                'ValidationException' => $this->validator->GetErrorList()
                )
            );
        exit;
    }
}

class BasicValidation
{
    protected $errorList;
    
    function Check($key, $value, $propertyValidationRules)
    {
            // required fields
            if ($propertyValidationRules['required'] && (!isset($value) || empty($value))) {
                // empty int, that isset must be 0, and this should be acceptable
                if ($propertyValidationRules['type'] != 'int')
                    $this->addValidationError($key, VALIDATION_MISSING_REQUIRED_VALUE);
            }
                 
            //if ($propertyValidationRules['type'] == 'filename' && isset($value))
            //     $this->addValidationError($key, VALIDATION_MISSING_REQUIRED_VALUE);case 'filename':
        
            // GUIDS
            if ($propertyValidationRules['type'] == 'guid' && isset($value) && (strlen($value) != 36 || 
            !preg_match('/^([a-f\d]{8}(-[a-f\d]{4}){4}[a-f\d]{8})$/i', $value)))
                 $this->addValidationError($key, VALIDATION_NO_VALID_ID);
                 
            if ($propertyValidationRules['type'] == 'uuid' && isset($value) && (strlen($value) != 64 || 
            !preg_match('/^[0-9a-z]{64}$/i', $value)))
                 $this->addValidationError($key, VALIDATION_NO_VALID_ID);
                 
            // Datetime
            if ($propertyValidationRules['type'] == 'datetime' && isset($value)) {
                try {
                    new \DateTime($value);
                } catch (\Exception $e) {
                    $this->addValidationError($key, VALIDATION_NO_VALID_DATE);
                }
            }
            
            // Booleans
            if ($propertyValidationRules['type'] == 'bool' && isset($value) && 
            !in_array(strtolower($value), array('true', 'false')))
                 $this->addValidationError($key, VALIDATION_NO_VALID_BOOL);
                 
            // minimum length
            if (isset($propertyValidationRules['min_length']) 
                && (($propertyValidationRules['required'] && (!isset($value) || strlen($value) < $propertyValidationRules['min_length']))
                || (!$propertyValidationRules['required'] && (isset($value) && strlen($value) < $propertyValidationRules['min_length'])))
                )
                 $this->addValidationError($key, VALIDATION_VALUE_MINIMUM_LENGTH, $propertyValidationRules['min_length']);
                 
            // maximum length
            if (isset($propertyValidationRules['max_length']) 
                && (($propertyValidationRules['required'] && (!isset($value) || strlen($value) > $propertyValidationRules['max_length']))
                || (!$propertyValidationRules['required'] && (isset($value) && strlen($value) > $propertyValidationRules['max_length'])))
                )
                 $this->addValidationError($key, VALIDATION_VALUE_MAXIMIM_LENGTH, $propertyValidationRules['max_length']);
    }
    
    function addValidationError($key, $errorMessage, $value = null)
    {
        if (!isset($this->errorList[$key]))
            $this->errorList[$key] = sprintf($errorMessage, $key, $value);
    }
    
    function hasErrors()
    {
        return (isset($this->errorList) && count($this->errorList) > 0);
    }
    
    function GetErrorList()
    {
        return $this->errorList;
    }
    
}

class TransferUserData
{
    protected $propertiesValidationRules;
    protected $maxPostedSize = 8192;
    /*=
        array ('example_field_to_validate' => array(
                'type' => 'text',
                'min_length' => 3,
                'max_length' => 30,
                'required' => false
                ));*/
                
    protected $Validator;
    
    function sanitize($field_value, $type) {
        switch ($type) {
            case 'filename':
            case 'text':
                return filter_var($field_value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);
            case 'uuid':
            case 'guid':
                return filter_var($field_value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_HIGH | FILTER_FLAG_STRIP_BACKTICK);
            case 'url':
                return filter_var($field_value, FILTER_SANITIZE_URL);
            case 'email':
                return filter_var($field_value, FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($field_value, FILTER_SANITIZE_NUMBER_INT);
            case 'bool':
                return filter_var($field_value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);
            default:
                return filter_var($field_value, FILTER_UNSAFE_RAW, FILTER_FLAG_STRIP_LOW | FILTER_FLAG_STRIP_BACKTICK);
        }
        return $field_value;
    }
    
    function _getType($field_name) {
        assert(isset($this->propertiesValidationRules) && is_array($this->propertiesValidationRules) &&
            isset($this->propertiesValidationRules[$field_name]) && is_array($this->propertiesValidationRules[$field_name]) &&
            isset($this->propertiesValidationRules[$field_name]['type'])); 
                
        return $this->propertiesValidationRules[$field_name]['type'];
    }
    
    function __construct($aData = null)
    {
        $this->Validator = new BasicValidation();
        $reflect = new ReflectionClass($this);
        $properties   = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
        
        foreach ($properties as $property) 
        {
            if (isset($aData[$property->getName()])){
                $this->{$property->getName()} = $this->sanitize($aData[$property->getName()], $this->_getType($property->getName()));
            }
        }
        
        $this->_Validate();
        if ($this->Validator->hasErrors())
        {
            throw new ValidationException($this->Validator);
        }
    }
    
    function _Validate()
    {
        $posted_size =  (isset($_SERVER['CONTENT_LENGTH']) ? (int)$_SERVER['CONTENT_LENGTH'] : 0) + strlen($_SERVER['REQUEST_URI']);
        
        assert(is_array($this->propertiesValidationRules));
        assert($posted_size < $this->maxPostedSize, new Exception(VALIDATION_REQUEST_TOO_BIG));
        
        foreach ($this->propertiesValidationRules as $propertyValidationKey=>$propertyValidationRules) 
        {
            $this->Validator->Check(
                $propertyValidationKey, 
                isset($this->$propertyValidationKey) ? $this->$propertyValidationKey : null,
                $propertyValidationRules
            );
        }
    }
}