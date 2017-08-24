<?php
$app->get('/signup', 'App\Controllers\web\UserController:getSignUp')->setName('signup');
$app->post('/signup', 'App\Controllers\web\UserController:signUp')->setName('post.signup');
$app->post('/reset', 'App\Controllers\web\UserController:resetPassword')->setName('password.reset');
$app->get('/test', 'App\Controllers\web\HomeController:index')->setName('timeline');
$app->get('/404', 'App\Controllers\web\HomeController:notFound')->setName('not.found');

$app->get('/admin', 'App\Controllers\web\UserController:getLoginAsAdmin')->setName('login.admin');
$app->post('/admin', 'App\Controllers\web\UserController:loginAsAdmin');
$app->get('/user', 'App\Controllers\web\UserController:getAllUser');
$app->get('/', 'App\Controllers\web\UserController:getLogin')->setName('login');
$app->post('/', 'App\Controllers\web\UserController:login')->setName('post.login');
$app->group('', function() use ($app, $container) {
    $app->get('/home', 'App\Controllers\web\HomeController:index')->setName('home');
    $app->get('/logout', 'App\Controllers\web\UserController:logout')->setName('logout');
    // $app->get('/profile', 'App\Controllers\web\UserController:viewProfile')->setName('user.profile');
    // $app->get('/setting', 'App\Controllers\web\UserController:getSettingAccount')->setName('user.setting');
    // $app->post('/setting', 'App\Controllers\web\UserController:settingAccount');

    $app->group('/group', function() use ($app, $container) {
        $app->get('', 'App\Controllers\web\GroupController:index')->setName('group');
        $app->get('/{id}', 'App\Controllers\web\GroupController:enter')->setName('pic.group');
        $app->post('/create', 'App\Controllers\web\GroupController:add')->setName('web.group.add');
        $app->post('/group/create', 'App\Controllers\web\GroupController:createByUser')->setName('pic.create.group');
        $app->get('/{id}/leave', 'App\Controllers\web\GroupController:leaveGroup')->setName('web.leave.group');
        $app->post('/{group}', 'App\Controllers\web\ItemController:createItemUser')->setName('web.item.user.create');
        $app->get('/{user}/{group}/item/reported', 'App\Controllers\web\ItemController:getReportedUserGroupItem')->setName('reported.item.user.group');
        $app->get('/{user}/{group}/item/unreported', 'App\Controllers\web\ItemController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        $app->get('item/{user}/member', 'App\Controllers\web\GroupController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        $app->get('/item/{user}/pic', 'App\Controllers\web\GroupController:getUnreportedUserGroupItem')->setName('unreported.item.user.group');
        $app->get('/{id}/members', 'App\Controllers\web\GroupController:getAllGroupMember')->setName('get.group.member');
        // $app->get('/{id}/pics', 'App\Controllers\web\GroupController:getGroupPic')->setName('get.group.pic');
    });
    // $app->get('/pic/create', 'App\Controllers\web\GroupController:createByUser')->setName('pic.create.group');
    // $app->get('/item/{group}', 'App\Controllers\web\ItemController:createItemUser')->setName('web.item.user.create');
    $app->group('/item', function() use ($app, $container) {
        $app->get('/show/{id}', 'App\Controllers\web\HomeController:showItem')->setName('show.item');
        $app->get('/group/{group}', 'App\Controllers\web\ItemController:getGroupItem')->setName('group.item');
        $app->get('/group/{group}/reported', 'App\Controllers\web\ItemController:getReportedGroupItem')->setname('web.reported.group.item');
        $app->get('/report/{item}', 'App\Controllers\web\ItemController:reportItem')->setname('web.report.item');
        $app->post('/report/{item}', 'App\Controllers\web\ItemController:reportItem')->setname('report.item');
        $app->get('/{item}/user', 'App\Controllers\web\ItemController:deleteItemByUser')->setname('web.user.delete.item');
        $app->post('/comment', 'App\Controllers\web\CommentController:postComment')->setName('post.comment');
        $app->get('/archive', 'App\Controllers\web\ItemController:getItemArchive')->setName('item.archive');
        $app->post('/archive/{id}', 'App\Controllers\web\ItemController:searchItemArchive')->setName('search.item.archive');
    });

    $app->group('/user', function() use ($app, $container) {
        $app->get('/groups', 'App\Controllers\web\GroupController:getGeneralGroup')->setName('group.user');
        $app->get('/profile', 'App\Controllers\web\UserController:viewProfile')->setName('user.view.profile');
        $app->get('/profile/setting', 'App\Controllers\web\UserController:settingProfile')->setName('user.setting.profile');
        $app->post('/profile/update/{id}', 'App\Controllers\web\UserController:updateProfile')->setName('user.update.profile');
        $app->post('/image/change', 'App\Controllers\web\UserController:changeImage')->setName('user.change.image');
        $app->get('/change/password', 'App\Controllers\web\UserController:getChangePassword')->setName('change.password');
        $app->post('/change/password', 'App\Controllers\web\UserController:postChangePassword')->setName('post.change.password');
    });

     $app->group('/guard', function() use ($app, $container) {
        $app->get('/show/user', 'App\Controllers\web\GuardController:showGuardByUser');
        $app->get('/user', 'App\Controllers\web\GuardController:getUser');
        $app->get('/show/{id}', 'App\Controllers\web\GuardController:showUserByGuard');
        $app->get('/delete/{id}', 'App\Controllers\web\GuardController:deleteGuardian');
    });
    // ->add(new \App\Middlewares\web\GuardMiddleware($container));
    // )->add(new \App\Middlewares\web\AuthMiddleware($container)
})->add(new \App\Middlewares\web\AuthMiddleware($container));
