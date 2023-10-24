<?php

namespace Config;

use App\Controllers\PrivacyPolicy;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

/*
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
// The Auto Routing (Legacy) is very dangerous. It is easy to create vulnerable apps
// where controller filters or CSRF protection are bypassed.
// If you don't want to define all routes, please use the Auto Routing (Improved).
// Set `$autoRoutesImproved` to true in `app/Config/Feature.php` and set the following to true.

$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
$routes->get('/', 'Home::index');

$routes->get('privacyPolicy', [PrivacyPolicy::class, 'index']);

/* $routes->resource('api/auth', ['controller' => 'Auth']);
$routes->resource('api/user', ['controller' => 'User']); */

$routes->group('api', ['namespace' => 'App\Controllers\Api'], function ($routes) {
    $routes->group('auth', function ($routes) {
        $routes->post('signup', 'Auth::signup');
        $routes->post('login', 'Auth::login');
        $routes->get('guestToken', 'Auth::guestToken');
        $routes->post('checkDuplicate', 'Auth::checkDuplicate');
    });

    $routes->group('account', function ($routes) {
        $routes->post('registerDeviceToken', 'Account::registerDeviceToken', ['filter' => 'authFilter']);
        $routes->post('permDelete', 'Account::permDelete', ['filter' => 'authFilter']);
    });

    $routes->group('privacy', function ($routes) {
        $routes->post('reportContent', 'Privacy::reportContent', ['filter' => 'authFilter']);
    });

    //    $routes->resource('users', ['filter' => 'authJwt']);
    $routes->group('users', function ($routes) {
        $routes->post('editDisplayName', 'Users::editDisplayName', ['filter' => 'authFilter']);
        $routes->post('editProfile', 'Users::editProfile', ['filter' => 'authFilter']);
        $routes->post('editStyles', 'Users::editStyles', ['filter' => 'authFilter']);
        $routes->post('editMeasurement', 'Users::editMeasurement', ['filter' => 'authFilter']);
        $routes->post('uploadFile', 'Users::uploadFile', ['filter' => 'authFilter']);
        $routes->post('follow', 'Users::follow', ['filter' => 'authFilter']);
        $routes->get('profile/(:segment)', 'Users::profile/$1', ['filter' => 'authFilter']);
        $routes->get('notifications', 'Users::notifications', ['filter' => 'authFilter']);
        $routes->post('sendMessage', 'Users::sendMessage', ['filter' => 'authFilter']);
        $routes->get('contacts', 'Users::contacts', ['filter' => 'authFilter']);
        $routes->post('blockUser', 'Users::blockUser', ['filter' => 'authFilter']);
        $routes->get('changePassword', 'Users::changePassword');



        /*
        $routes->get('', 'Users::index', ['filter' => 'authJwt']);
        $routes->get('(:segment)', 'Users::show/$1', ['filter' => 'authJwt']);
        $routes->get('(:segment)/edit', 'Users::edit/$1', ['filter' => 'authJwt']);
        $routes->post('', 'Users::create', ['filter' => 'authJwt']);
        $routes->put('(:segment)', 'Users::update/$1', ['filter' => 'authJwt']);
        $routes->patch('(:segment)', 'Users::update/$1', ['filter' => 'authJwt']);
        $routes->delete('(:segment)', 'Users::delete/$1', ['filter' => 'authJwt']);
        */
    });

    // $routes->resource('employee');

    //    $routes->resource('players', ['filter' => 'authJwt']);
    $routes->group('post', function ($routes) {
        $routes->get('', 'Post::index', ['filter' => 'authFilter']);
        $routes->post('addPost', 'Post::addPost', ['filter' => 'authFilter']);
        $routes->post('getPost', 'Post::getPost', ['filter' => 'authFilter']);
        $routes->post('editPost/(:segment)', 'Post::editPost/$1', ['filter' => 'authFilter']);
        $routes->post('markSold/(:segment)', 'Post::markSold/$1', ['filter' => 'authFilter']);
        $routes->delete('(:segment)', 'Post::deletePost/$1', ['filter' => 'authFilter']);
        $routes->post('search', 'Post::search', ['filter' => 'authFilter']);
        $routes->get('details/(:segment)', 'Post::getDetail/$1', ['filter' => 'authFilter']);
        $routes->post('liked', 'Post::liked', ['filter' => 'authFilter']);
        $routes->post('setLike', 'Post::setLike', ['filter' => 'authFilter']);
        $routes->post('comment', 'Post::comment', ['filter' => 'authFilter']);
        $routes->get('comments/(:segment)', 'Post::comments/$1', ['filter' => 'authFilter']);
        $routes->post('deleteComment', 'Post::deleteComment', ['filter' => 'authFilter']);


        /*
        $routes->get('index', 'Players::index', ['filter' => 'authJwt']);
        $routes->get('(:segment)', 'Players::show/$1', ['filter' => 'authJwt']);
        $routes->get('index/(:segment)', 'Players::show/$1', ['filter' => 'authJwt']);
        $routes->get('getUserByEmail/(:segment)', 'Players::getUserByEmail/$1', ['filter' => 'authJwt']);
        $routes->delete('(:segment)', 'Players::deletePlayer/$1');
        */
    });
});

/*
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (is_file(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
