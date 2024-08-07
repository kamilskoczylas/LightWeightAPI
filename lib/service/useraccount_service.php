<?php
    require_once ('lib/repository/session_repository.php');
    require_once ('lib/dto/session_creator_dto.php');
    require_once ('lib/dto/session_dto.php');

    class UseraccountService extends BasicService {
        
        function __construct(Response $responseClass = null)
        {
            parent::__construct($responseClass);
            $this->session = new SessionRepository();
        }
        
        
        function GetSession(GetUseraccountSessionRequest $getUseraccountSessionRequest){

            
                assert(isset($getUseraccountSessionRequest->application_guid) && 
                    strlen($getUseraccountSessionRequest->application_guid) == 64 && 
                    preg_match('/^[0-9a-z]{64}$/i', $getUseraccountSessionRequest->application_guid)
                );
                
                
                $session_guid = GUID();
                $session_creator = new Session_creator_dto();
                $session_creator->application_guid = $getUseraccountSessionRequest->application_guid;
                $session_creator->session_guid = $session_guid;
                
                $this->session->Create(
                        $session_creator
                        );

                return $this->Response(
                        array('session_guid' => $session_guid)
                    );
            
        }
        
        function DeleteSession(DeleteUseraccountSessionRequest $deleteUseraccountSessionRequest){

                $session_dto = new Session_dto();
                $session_dto->session_guid = $deleteUseraccountSessionRequest->session_guid;
                $session_information = $this->session->Get($session_dto);
                
                assert(isset($session_information) && $session_information !== false && 
                    get_class($session_information) == "Session_dto" && strlen($session_information->session_guid) == 36);
                
                $this->session->Delete(
                        $session_information
                        );
                        
                if (isset($deleteUseraccountSessionRequest->full_delete) && !empty($deleteUseraccountSessionRequest->full_delete) &&
                $deleteUseraccountSessionRequest->full_delete == $full_application_information->application_version) {
                    $this->application->Delete(
                            $full_application_information
                            );
                }

                return $this->Response(
                        array('result' => 'success')
                    );
        }
    }
