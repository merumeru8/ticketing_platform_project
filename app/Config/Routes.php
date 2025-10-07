<?php

return [

    [
        "url" => "/",
        "name" => "home",
        'controller' => 'HomeController',
        'method' => 'index',
        'authorized_group' => null
    ],
    [
        "url" => "/organizer",
        "name" => "home_organizer",
        'controller' => 'OrganizerHomeController',
        'method' => 'index',
        'authorized_group' => 'organizer'
    ],
    [
        "url" => "/organizer/delete_event",
        "name" => "event_delete",
        'controller' => 'OrganizerHomeController',
        'method' => 'deleteEvent',
        'authorized_group' => 'organizer'
    ],
    [
        "url" => "/organizer/restore_event",
        "name" => "event_restore",
        'controller' => 'OrganizerHomeController',
        'method' => 'restoreEvent',
        'authorized_group' => 'organizer'
    ],
    
    [
        "url" => "/organizer/event",
        "name" => "event_creator",
        'controller' => 'ManageEventController',
        'method' => 'manageEvent',
        'authorized_group' => 'organizer'
    ],
    [
        "url" => "/organizer/event/(:num)",
        "name" => "event_editor",
        'controller' => 'ManageEventController',
        'method' => 'manageEvent',
        'authorized_group' => 'organizer'
    ],
    [
        "url" => "/organizer/save_event",
        "name" => "event_save",
        'controller' => 'ManageEventController',
        'method' => 'saveEvent',
        'authorized_group' => 'organizer'
    ],
    [
        "url" => "/customer",
        "name" => "home_buyer",
        'controller' => 'BuyerHomeController',
        'method' => 'index',
        'authorized_group' => 'customer'
    ],
    [
        "url" => "/customer/checkout",
        "name" => "checkout",
        'controller' => 'CartController',
        'method' => 'checkout',
        'authorized_group' => null
    ],   
    [
        "url" => "/login",
        "name" => "login",
        'controller' => 'Auth\LoginController',
        'method' => 'index',
        'authorized_group' => null
    ],
    [
        "url" => "/login/action",
        "name" => "login_action",
        'controller' => 'Auth\LoginController',
        'method' => 'loginAction',
        'authorized_group' => null
    ],

    [
        "url" => "/logout",
        "name" => "logout",
        'controller' => 'Auth\LoginController',
        'method' => 'logout',
        'authorized_group' => null
    ],

    [
        "url" => "/register",
        "name" => "register",
        'controller' => 'Auth\RegisterController',
        'method' => 'index',
        'authorized_group' => null
    ],
    [
        "url" => "/register/action",
        "name" => "register_action",
        'controller' => 'Auth\RegisterController',
        'method' => 'registerAction',
        'authorized_group' => null
    ],
];