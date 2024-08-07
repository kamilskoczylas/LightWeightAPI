<?php

class Csv {
    
    function encodeArrayAsFile($aItemsList, $fileName, $aColumns = array()){
        $fp = fopen($fileName, 'w');
        
        // TODO: Get it from DTO, because may be empty
        if (!empty($aItemsList)) {
                
            // first row - field descriptions
            $columnNamesCsvHeader = empty($aColumns) ? array_keys($aItemsList[0]) : $aColumns;
            
            // data filtered to header columns only in same order
            $aFilteredRows = array();
            foreach ($aItemsList as $rowData) {
                $filteredColumns = array();
                foreach ($columnNamesCsvHeader as $columnKey) {
                    $filteredColumns[] = $rowData[$columnKey];
                }
                $aFilteredRows[] = $filteredColumns;
            }
            
            // header
            fputcsv($fp, $columnNamesCsvHeader);
            
            // list of values
            foreach ($aFilteredRows as $rowFields) {
                fputcsv($fp, $rowFields);
            }
        }
        
        fclose($fp);
    }
}