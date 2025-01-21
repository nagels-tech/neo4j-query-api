<?php

use Neo4j\QueryAPI\Objects\Authentication;
require_once 'Authentication.php'; // Assuming your Authentication class is in Authentication.php

// Using Bearer Token Authentication
$authBearer = Authentication::create([
    'token' => 'yourBearerToken'
]);

print_r($authBearer->getHeader());

// Using Basic Authentication
$authBasic = Authentication::create([
    'username' => '',
    'password' => ''
]);

print_r($authBasic->getHeader());
