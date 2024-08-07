<?php


enum DTO_INCLUDE: int {
    case NO_PK_FIELDS_HAVING_VALUE = 0;
    case PRIMARY_KEYS = 1;
    case EMPTY_KEYS = 2;
    case DEFAULT_VALUES = 4;
}

class DTO_Options {
    
    private $value;
    
    function __construct(DTO_INCLUDE $value1 = DTO_INCLUDE::NO_PK_FIELDS_HAVING_VALUE, 
        DTO_INCLUDE $value2 = DTO_INCLUDE::NO_PK_FIELDS_HAVING_VALUE, 
        DTO_INCLUDE $value3 = DTO_INCLUDE::NO_PK_FIELDS_HAVING_VALUE) {
            
        $this->value = $value1->value | $value2->value | $value3->value;
    }
    
    function shouldIncludePrimaryKeys(){
        return ($this->value & DTO_INCLUDE::PRIMARY_KEYS->value) > 0;
    }
    
    function shouldIncludeEmptyKeys(){
        return ($this->value & DTO_INCLUDE::EMPTY_KEYS->value) > 0;
    }
    
    function shouldIncludeDefaultValues(){
        return ($this->value & DTO_INCLUDE::DEFAULT_VALUES->value) > 0;
    }
}

class ForeignReference {
    
    public $FieldName;
    public $ForeignTableName;
    public $isLeftJoin;
    public $aColumns;
    public $prefix;
    
    function __construct($field_name_id, $foreign_table_name, $aForeignColumns, $prefix = false, $isLeftJoin = false) {
        assert(is_array($aForeignColumns) && !empty($aForeignColumns));
        
        $this->FieldName = $field_name_id;
        $this->ForeignTableName = $foreign_table_name;
        $this->aColumns = $aForeignColumns;
        $this->isLeftJoin = $isLeftJoin;
        $this->prefix = $prefix;
        
        if (!$prefix) $this->createPrefix();
    }
    
    function createPrefix() {
        $this->prefix = $this->ForeignTableName;
    }
    
    function getColumns() {
        return $this->aColumns;
    }
    
    function getPrefixedColumns($joinCharacter = '.') {
        
        $aModifiedColumn = $this->aColumns;
        array_walk($aModifiedColumn, function (&$item, $key, $prefix) {
            $item = $prefix.$item;
        }, $this->prefix.$joinCharacter);
        return $aModifiedColumn;
    }
    
    function join() {
        return $this->isLeftJoin ? 'LEFT JOIN' : 'INNER JOIN';
    }
    
}

class Basic_dto {
    protected $primary_key_name = '';
    protected $table_name = '';
    protected $field_names = array();
    protected $preffixed_field_names = array();
    protected $preffix = ':';
    protected $default_field_values = array(); //EXAMPLE: array('field'=>'value');
    protected $default_comparision_operators = array(); //EXAMPLE: array('field'=>'=', 'updated'=>'<');
    protected $foreign_references = array();
    protected $automatic_validation = true;
    
    private $operators = array('=', '<', '>', '<=', '>=');
    
    function __construct($table_name = '', $primary_key_name = ''){
        
        // attempt to discover table primary key using naming standard
        if (empty($this->primary_key_name) && empty($primary_key_name)){
            foreach ($this->GetFieldNames(
                        New DTO_Options(DTO_INCLUDE::EMPTY_KEYS)
                    ) as $field_name) 
            {
                if (substr($field_name, 0, 3) == 'id_')
                   $primary_key_name = $field_name;
            }
        }
        
        // try to discover table name if not specified
        if (empty($this->table_name) && empty($table_name)) {
            preg_match ('/^([A-Z]{1}[a-z]+)([A-Z]{1}[a-z]+)*$/' , str_replace('_dto', '', get_class($this)), $matches);
            
            array_splice($matches, 0, 1);
            $table_name = strtolower(implode('_', $matches));
        }
        
        if (!empty($primary_key_name))
            $this->primary_key_name = $primary_key_name;
            
        if (!empty($table_name))
            $this->table_name = $table_name;
       
    }
    
    public function canValidate(){
        return $this->automatic_validation;
    }
    
    public function doNotValidateAutomatically(){
        return $this->automatic_validation = false;
    }
    
    private function prepareFieldNames()
    {
        $reflect = new ReflectionClass($this);
        $properties = $reflect->getProperties(ReflectionProperty::IS_PUBLIC);
    
        foreach ($properties as $property)
        {
            $this->field_names[] = $property->getName();
            $this->preffixed_field_names[] = $this->preffix.$property->getName();
        }
    }
    
    public function GetFieldNames(DTO_Options $dto_options)
    {
        // $onlySetValues = false, $skipPrimaryKey = true, $setDefaultsWhenPossible = true
        if (count($this->field_names) == 0)
        {
            $this->prepareFieldNames();
        }
        
        //if (!$onlySetValues && !$skipPrimaryKey)
        if ($dto_options->shouldIncludeEmptyKeys() && $dto_options->shouldIncludePrimaryKeys())
            return $this->field_names;
            
        $aResult = array();
        
        foreach ($this->field_names as $field)
        {
            if (!$dto_options->shouldIncludePrimaryKeys() && $field == $this->primary_key_name)
                continue;
                
            if ($dto_options->shouldIncludeEmptyKeys() 
                || isset($this->$field) 
                || ($dto_options->shouldIncludeDefaultValues() && isset($this->default_field_values[$field]))
                )
                $aResult[] = $field;
        }
        
        return $aResult;
    }
    
    public function GetPrefixedFieldNames(DTO_Options $dto_options)
    //$onlySetValues = false, $skipPrimaryKey = true, $setDefaultsWhenPossible = true
    {
        if (count($this->field_names) == 0)
        {
            $this->prepareFieldNames();
        }
        
        //if (!$onlySetValues && !$skipPrimaryKey)
        if ($dto_options->shouldIncludeEmptyKeys() && $dto_options->shouldIncludePrimaryKeys())
            return $this->preffixed_field_names;
            
        $aResult = array();
        
        foreach ($this->field_names as $field)
        {
            if (!$dto_options->shouldIncludePrimaryKeys() && $field == $this->primary_key_name)
                continue;
                
            if ($dto_options->shouldIncludeEmptyKeys() 
                || isset($this->$field) 
                || ($dto_options->shouldIncludeDefaultValues() && isset($this->default_field_values[$field]))
                )
                if ($dto_options->shouldIncludeDefaultValues() && 
                    !isset($this->$field) && 
                    isset($this->default_field_values[$field])) 
                    $aResult[] = $this->default_field_values[$field];
                else
                    $aResult[] = $this->preffix.$field;
        }
        
        return $aResult;
    }
    
    /*public function GetPrefixedFieldNamesHavingValues($includeEmptyFieldsHavingDefaults = true)
    {
        return $this->GetPrefixedFieldNames(true, false, $includeEmptyFieldsHavingDefaults);
    }
    
    public function GetFieldNamesHavingValues($includeEmptyFieldsHavingDefaults = true)
    {
        return $this->GetFieldNames(true, false, $includeEmptyFieldsHavingDefaults);
    }*/
    
    public function GetPreffixedFieldsValues(DTO_Options $dto_options)
        
       // $onlySetValues = false, $skipPrimaryKey = true, $removeFieldsHavingNoValueButDefaults = true)
    {
        $allFields = $this->GetFieldNames($dto_options);
        $aResult = array();
        
        foreach ($allFields as $field)
        {
            // when no defaults are defined or should not affect
            if ((isset($this->$field) && !empty($this->$field)) || !isset($this->default_field_values[$field]))
                $aResult[$this->preffix.$field] = $this->$field;
        }
        
        return $aResult;
    }
    
    /*public function GetPreffixedFieldsHavingValues(DTO_Options $dto_options)
    {
        return $this->GetPreffixedFieldsValues($dto_options);
    }*/
    
    public function SetComparisionOperator($field, $operator)
    {
        assert(in_array($operator, $this->operators));
        assert(in_array($field, $this->field_names));
        
        $this->default_comparision_operators[$field] = $operator;
    }
    
    public function GetComparisionOperator($field)
    {
        return isset($this->default_comparision_operators[$field]) ? $this->default_comparision_operators[$field] : '=';
    }
    
    public function Id()
    {
        $primaryKey = $this->GetPrimaryKeyName();
        return $this->$primaryKey;
    }
    
    public function GetPrimaryKeyName()
    {
        return $this->primary_key_name;
    }
    
    public function TableName()
    {
        return $this->table_name;
    }
    
    public function ForeignReference(ForeignReference $reference)
    {
        $this->foreign_references[$reference->FieldName] = $reference;
    }
    
    public function GetForeignReferences()
    {
        return $this->foreign_references;
    }
}