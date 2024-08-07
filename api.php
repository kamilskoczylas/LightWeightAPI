<?php

/*
    Service based framework
    2019

*/


include 'lib/api_header.php';

/*
    API process simple requests only
    Naming standard:
    GetUserMainRequest
    GetPageSummaryRequest
    UpdateGuestbookPostRequest
    DeleteGuestbookPostRequest
*/

if (isset($_GET['request'])){
    $service = new ServiceBuilder($_GET['request'], new Response());
    $service->Execute();
}


