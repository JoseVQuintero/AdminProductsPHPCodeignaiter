<?php

namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php')) {
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(true);

/*
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

$routes->get('/', 'Welcome::index');
$routes->post('GetLogin', 'Welcome::index');
$routes->get('blocked', 'Welcome::forbiddenPage');
$routes->get('register', 'Welcome::register');
$routes->post('register', 'Welcome::registration');
$routes->get('home', 'Home::index');

$routes->post('cron2', 'Cron/Cron::execute');//create pricefile country

$routes->group('segmentation', function($routes){
    $routes->post('segmentationManufacturerCategories','segmentation::segmentationManufacturerCategories')
           ->match(['get','put','delete'],'segmentationManufacturerCategories','api/HTTPBadRequest::index');
    $routes->get('/','segmentation::index')
           ->match(['post','put','delete'],'/','api/HTTPBadRequest::index');
});

$routes->group('api', ['filter'=>'auth'], function($routes){
    $routes->get('manufacturer','api/Manufacturer::index')
           ->match(['post','put','delete'],'manufacturer','api/HTTPBadRequest::index');
    $routes->get('client','api/Client::index')
           ->match(['post','put','delete'],'client','api/HTTPBadRequest::index');
    $routes->post('products','api/Products::index')
           ->match(['get','put','delete'],'products','api/HTTPBadRequest::index');
});

$routes->group('cron', ['filter'=>'authcron'], function($routes){   
    $routes->post('cron','Cron/Timer/Cron::index')
           ->match(['get','put','delete'],'cron','cron/HTTPBadRequest::index');
    $routes->post('loop','Cron/Cron::execute')
           ->match(['get','put','delete'],'cron','cron/HTTPBadRequest::index');
    //$routes->match(['post','put','delete'],'client','api/Client::inde2x');
});

// We get a performance increase by specifying the default
// route since we don't have to scan directories.
//$routes->get('/', 'Home::index');
//$routes->get('/', 'Welcome::index');
/*$routes->get('client', 'Client::index');
$routes->post('client', 'Client::store');
$routes->get('client/(:num)', 'Client::show/$1');
$routes->post('client/(:num)', 'Client::update/$1');
$routes->delete('client/(:num)', 'Client::destroy/$1');

$routes->get('manufacturer', 'Manufacturer::index');
$routes->post('manufacturer', 'Manufacturer::store');
$routes->get('manufacturer/(:num)', 'Manufacturer::show/$1');
$routes->post('manufacturer/(:num)', 'Manufacturer::update/$1');
$routes->delete('manufacturer/(:num)', 'Manufacturer::destroy/$1');

$routes->get('categories', 'Categories::index');
$routes->post('categories', 'Categories::store');
$routes->get('categories/(:num)', 'Categories::show/$1');
$routes->post('categories/(:num)', 'Categories::update/$1');
$routes->delete('categories/(:num)', 'Categories::destroy/$1');

$routes->get('products', 'Products::index');
$routes->post('products', 'Products::store');
$routes->get('products/(:num)', 'Products::show/$1');
$routes->post('products/(:num)', 'Products::update/$1');
$routes->delete('products/(:num)', 'Products::destroy/$1');

$routes->get('storage', 'Storage::update');//se generan json por segmentacion para clientes
$routes->get('storage/prices/(:num)', 'Storage::putPrices/$1');//create pricefile country

$routes->get('pricefile/(:alphanum)', 'PriceFile::import/$1');//create pricefile country

//Common Routes
$routes->get('/', 'Welcome::index');
$routes->post('GetLogin', 'Welcome::index');
$routes->get('blocked', 'Welcome::forbiddenPage');
$routes->get('register', 'Welcome::register');
$routes->post('register', 'Welcome::registration');
$routes->get('home', 'Home::index');

// Setting Routes
$routes->get('users/userRoleAccess', 'Users::userRoleAccess');
$routes->post('users/createRole', 'Users::createRole');
$routes->post('users/updateRole', 'Users::updateRole');
$routes->delete('users/deleteRole', 'Users::deleteRole');
$routes->post('users/createMenuCategory', 'Users::createMenuCategory');
$routes->post('users/createMenu', 'Users::createMenu');
$routes->post('users/createSubMenu', 'Users::createSubMenu');
$routes->post('users/createUser', 'Users::createUser');
$routes->post('users/updateUser', 'Users::updateUser');
$routes->delete('users/deleteUser', 'Users::deleteUser');
$routes->post('users/changeMenuPermission', 'Users::changeMenuPermission');
$routes->post('users/changeMenuCategoryPermission', 'Users::changeMenuCategoryPermission');
$routes->post('users/changeSubMenuPermission', 'Users::changeSubMenuPermission');

//Developer Routes
$routes->get('menuManagement', 'Developers/MenuManagement::index');
$routes->get('crudGenerator', 'Developers/CRUDGenerator::index');

$routes->get('cron', 'Cron::index');
$routes->get('segmentation', 'segmentation::index');
$routes->post('segmentation/segmentationManufacturerCategories', 'Segmentation::segmentationManufacturerCategories');*/

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
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php')) {
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
