<?php

require_once __DIR__ . '/../controllers/LocationController.php';

$locationController = new LocationController($GLOBALS['pdo']);

return [
    // Get all provinces
    'GET /provinces' => [
        'handler' => [$locationController, 'getProvinces'],
        'auth' => 'public'
    ],
    // Get all districts for a specific province
    'GET /districts/{province_id}' => [
        'handler' => [$locationController, 'getDistricts'],
        'auth' => 'public'
    ],
    // Get all cities for a specific district
    'GET /cities/district/{district_id}' => [
        'handler' => [$locationController, 'getCitiesByDistrict'],
        'auth' => 'public'
    ],
    // Get all cities
    'GET /cities' => [
        'handler' => [$locationController, 'getAllCities'],
        'auth' => 'public'
    ],
    // Get a specific city by its ID
    'GET /cities/{id}' => [
        'handler' => [$locationController, 'getCity'],
        'auth' => 'public'
    ],
    // Create a new city
    'POST /cities' => [
        'handler' => [$locationController, 'createCity'],
        'auth' => 'private' // Assuming only authenticated users can create cities
    ],
    // Update an existing city
    'PUT /cities/{id}' => [
        'handler' => [$locationController, 'updateCity'],
        'auth' => 'private' // Assuming only authenticated users can update cities
    ],
    // Delete a city
    'DELETE /cities/{id}' => [
        'handler' => [$locationController, 'deleteCity'],
        'auth' => 'private' // Assuming only authenticated users can delete cities
    ],
];
