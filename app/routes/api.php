<?php

$app->group('/api', function() use ($app, $container) {
    $app->get('/', 'App\Controllers\api\UserController:index');
    $app->post('/login', 'App\Controllers\api\UserController:login')->setname('api.user.login');
    $app->get('/logout', 'App\Controllers\api\UserController:logout')->setname('api.logout');
    $app->post('/register', 'App\Controllers\api\UserController:createUser')->setname('api.user.login');

    $app->get('/items', 'App\Controllers\api\ItemController:all')->setname('api.item.all');
    $app->get('/items/{id}', 'App\Controllers\api\ItemController:getItemDetail')->setname('api.item.Detail');
    $app->get('/items/group/{group}', 'App\Controllers\api\ItemController:getGroupItem')->setname('api.group.item');
    $app->get('/items/user/{user}', 'App\Controllers\api\ItemController:getUserItem')->setname('api.user.item');
    $app->delete('/items/delete/{id}', 'App\Controllers\api\ItemController:deleteItem')->setname('api.item.delete');


    // })->add(new \App\Middlewares\AuthToken($container));
});
