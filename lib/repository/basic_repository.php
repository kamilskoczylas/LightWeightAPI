<?php

/*
    Base class for connecting to a database via PDO
    CRUD
*/

class BasicRepository {
    
    protected $DB;
    
    function __construct(){
        global $PDO;
        $this->DB = $PDO;
    }
    
    
    public function Create(Basic_dto $dto)
    {
        //if ($dto->canValidate())
        //    new Validate($dto);
            
        $dto_fields_no_pk_having_values = new DTO_Options(DTO_INCLUDE::NO_PK_FIELDS_HAVING_VALUE, DTO_INCLUDE::DEFAULT_VALUES);
        
        $DTO_Fields = $dto->GetFieldNames($dto_fields_no_pk_having_values);
        $DTO_Prefixed_Fields = $dto->GetPrefixedFieldNames($dto_fields_no_pk_having_values);
        
        
        $sql = 'INSERT INTO ' . $dto->TableName() . '
                    (
                    ' . implode(',', $DTO_Fields) . '
                    ) 
                    VALUES
                    (
                    ' . implode(',', $DTO_Prefixed_Fields) . '
                    )
                ';
                
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        $pdo_statement->execute(
            $dto->GetPreffixedFieldsValues($dto_fields_no_pk_having_values)
            );
            
        
        return $this->DB->lastInsertId();
    }
    
    function Read(Basic_dto $dto) {
        
        $DTO_SelectFields = implode(',', 
                $dto->GetFieldNames(new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS, DTO_INCLUDE::EMPTY_KEYS))
            );
            
        $sql = 'SELECT '.$DTO_SelectFields.' FROM ' . $dto->TableName();
        
        $dto_any_fields_having_values = new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS);
        $DTO_Fields = $dto->GetFieldNames($dto_any_fields_having_values);
        $sConditions = '';
        $sAnd = '';
        
        foreach ($DTO_Fields as $field)
        {
            $sConditions.= $sAnd.' '.$field.$dto->GetComparisionOperator($field) . ' :'.$field;
            $sAnd = ' AND';
        }
        
        assert(!empty($sConditions), new SQLParametersRequiredAssertionError());
        
        $sql.= ' WHERE '. $sConditions;
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $pdo_statement->execute(
                    $dto->GetPreffixedFieldsValues($dto_any_fields_having_values)
            );
            
        $pdo_statement->setFetchMode(PDO::FETCH_CLASS, get_class($dto));
        return $pdo_statement->fetch();
    }
    
    public function Update(Basic_dto $dto)
    {
        //if ($dto->canValidate())
        //    new Validate($dto);
            
        $DTO_Fields = $dto->GetFieldNames(new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS, DTO_INCLUDE::EMPTY_KEYS));
        $sUpdateFields = '';
        $sSeparate = '';
        $aParameters = array();
        
        foreach ($DTO_Fields as $field)
        {
            $sUpdateFields.= $sSeparate.$field.$dto->GetComparisionOperator($field).' :'.$field;
            $sSeparate = ', ';
            $aParameters[':'.$field] = $dto->$field;
        }
        
        
        $sql = 'UPDATE ' . $dto->TableName() . ' 
                    SET
                    ' . $sUpdateFields . '
                    WHERE
                    ' . $dto->GetPrimaryKeyName(). ' = :'. $dto->GetPrimaryKeyName();
                
        $aParameters[':'.$dto->GetPrimaryKeyName()] = $dto->Id();
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        $pdo_statement->execute(
            $aParameters
            );
            
        return $dto;
    }
    
    public function Delete(Basic_dto $dto)
    {
        $dto_any_fields_having_values = new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS);
        
        $DTO_Fields = $dto->GetFieldNames($dto_any_fields_having_values);
        $sConditions = '';
        $sAnd = '';
        
        foreach ($DTO_Fields as $field)
        {
            $sConditions.= $sAnd.' '.$field . $dto->GetComparisionOperator($field) . ' :'.$field;
            $sAnd = ' AND';
        }
        
        $sql = 'DELETE FROM ' . $dto->TableName() . '
                    WHERE 
                        ' . $sConditions;
                
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        $pdo_statement->execute(
                $dto->GetPreffixedFieldsValues($dto_any_fields_having_values)
            );
    }
    
    function _getReferencesColumns(Basic_dto $dto) {
        if (!isset($dto))
            return '';
            
        $aColumns = array();
        $references = $dto->GetForeignReferences();
        foreach ($references as $reference) {
            $aColumns = array_merge($aColumns, $reference->getPrefixedColumns());
        }
        return $aColumns;
    }
    
    function _getReferencesTables(Basic_dto $dto) {
        if (!isset($dto))
            return ''; 
        
        $sTableList = '';
        $references = $dto->GetForeignReferences();
        foreach ($references as $reference) {
            $foreign_id_field_name = 'id_'.$reference->ForeignTableName;
            $sTableList.= PHP_EOL.$reference->join().' '.$reference->ForeignTableName." AS $reference->prefix ON $reference->prefix.$foreign_id_field_name = t1.".$reference->FieldName;
        }
        
        return $sTableList;
    }
    
    function _getPrefixedColumns($arrayOfString, $prefix = 't1.') {
        
        $aModifiedColumn = $arrayOfString;
        array_walk($aModifiedColumn, function (&$item, $key, $prefix) {
            $item = $prefix.$item;
        }, $prefix);
        return $aModifiedColumn;
    }
    
    function GetAll(Basic_dto $dto = null) {
        
        $columnList = '*';
        
        
        if (isset($dto)) {
            $aColumns = $this->_getPrefixedColumns(
                    $dto->GetFieldNames(new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS, DTO_INCLUDE::EMPTY_KEYS)),
                    't1.'
                );
                
            $aColumns = array_merge($aColumns, $this->_getReferencesColumns($dto));
            $columnList = implode(',', $aColumns);
        }
        
        $sql = "SELECT $columnList FROM " . $dto->TableName() ." AS t1 " . $this->_getReferencesTables($dto);
        
        $dto_any_fields_having_values = new DTO_Options(DTO_INCLUDE::PRIMARY_KEYS);
        $DTO_Fields = $dto->GetFieldNames($dto_any_fields_having_values);
        $sConditions = '';
        $sAnd = '';
        
        foreach ($DTO_Fields as $field)
        {
            $sConditions.= $sAnd.' '.$field.$dto->GetComparisionOperator($field) . ' :'.$field;
            $sAnd = ' AND';
        }
        
        $sql.= empty($sConditions) ? '' : ' WHERE '. $sConditions;
        
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        
        $pdo_statement->execute(
            $dto->GetPreffixedFieldsValues($dto_any_fields_having_values)
            );
            
        return $pdo_statement->fetchAll(PDO::FETCH_ASSOC);
        //return $pdo_statement->fetchAll(PDO::FETCH_CLASS, get_class($dto));
    }
    
}