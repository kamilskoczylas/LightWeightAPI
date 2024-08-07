<?php

require_once 'lib/dto/session_dto.php';

class SessionRepository extends BasicRepository {
    
    function updateLastCommand($session_guid){
        
        $sql = 'UPDATE session SET lastcommand_datetime = NOW() WHERE session_guid = :guid';
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $pdo_statement->execute(
            array(
                    ':guid' => $session_guid
                )
            );
            
        return true;
    }
    
    function Get(Session_dto $dto){
        
        $sql = 'SELECT * FROM ' . $dto->TableName() . ' WHERE session_guid = :session_guid';
        
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $pdo_statement->execute(
            array(
                    ':session_guid' => $dto->session_guid
                )
            );
            
        $pdo_statement->setFetchMode(PDO::FETCH_CLASS, 'Session_dto');
        return $pdo_statement->fetch();
       
    }
    
    function Create($dto){
        
        $sql = 'INSERT INTO session (application_id, session_guid, lastcommand_datetime)
                SELECT id_application, :session_guid, NOW() FROM application WHERE application_guid = :application_guid';
                
        $pdo_statement = $this->DB->prepare($sql, array(PDO::ATTR_CURSOR => PDO::CURSOR_FWDONLY));
        $pdo_statement->execute(
            array(
                ':application_guid' => $dto->application_guid,
                ':session_guid' => $dto->session_guid
                )
            );
            
        return $this->DB->lastInsertId();
    }

}