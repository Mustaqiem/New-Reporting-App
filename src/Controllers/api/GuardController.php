<?php

namespace App\Controllers\api;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\GuardModel;
use App\Models\Users\UserModel;

class GuardController extends BaseController
{
    // Function Create Guardian
    public function createGuardian(Request $request, Response $response, $args)
    {
        $guard = new \App\Models\GuardModel($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userToken = new \App\Models\Users\UserToken($this->db);
        $userId = $userToken->getUserId($token);
        $findGuard = $guard->findTwo('guard_id', $args['id'], 'user_id', $userId);

        $data = [
            'guard_id'  =>  $args['id'],
            'user_id' => $userId,
        ];

        if ($findGuard) {
            $data = $this->responseDetail(404, true, 'Data tidak ditemukan');
        } else {
            $addGuardian = $guard->add($data);

            $data = $this->responseDetail(200, false, 'Berhasilkan menambahkan guardian', [
                    'data' => $data
                ]);
        }
        return $data;

    }
    // Function Delete Guardian
    public function deleteGuardian(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userToken = new \App\Models\Users\UserToken($this->db);
        $findUser = $userToken->find('token', '72af357cae642386ccaaf5c4e86b669a');
        $findGuard = $guard->findGuards('user_id', $findUser['user_id'], 'guard_id', $args['id']);

           // var_dump($findGuard);die();
        $query = $request->getQueryParams();

           if ($findGuard && $findUser['user_id']) {
               $oh = $guard->deleteGuard($args['id']);
               var_dump($oh);die();
               $data = $this->responseDetail(200, false, 'Guardian berhasil dihapus', [
                    'data' => $findGuard
                ]);
           } else {
               $data = $this->responseDetail(404, true, 'Data tidak ditemukan');
           }

           return $data;

    }
    // Function show user by guard_id
    public function getUserByGuard(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $users = new \App\Models\Users\UserModel($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userToken = new \App\Models\Users\UserToken($this->container->db);
        $userId = $userToken->getUserId($token);
        $findGuard = $guard->findGuard('guard_id', $args['id']);
        $guards = $guard->findGuards('user_id', $userId['user_id'], 'guard_id', $args['id']);

        $user = $users->find('id', $userId['user_id']);
        $query = $request->getQueryParams();

        if ($guards) {
            if ($findGuard || $user) {
                $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
                $findAll = $guard->findAllUser($args['id'])->setPaginate($page, 5);
                    // var_export($findAll);die();
                    // var_dump($findGuard);die();
                $data = $this->responseDetail(200, false, 'Berhasil menampilkan user dalam guardian', [
                    'query'     =>  $query,
                    'data'    =>  $findAll['data'],
                    'pagination'      =>  $findAll['pagination'],
                ]);

            } else {
                $data = $this->responseDetail(404, true, 'User tidak ditemukan', [
                    'query'     =>  $query
                ]);
            }
        } else {
            $data = $this->responseDetail(403, true, 'User tidak di temukan atau Kamu belum menambahkan id'. " ".$args['id']." ". 'menjadi guard', [
                    'query'     =>  $query
                ]);
        }
        return $data;
    }

    // Function show guard by user_id
    public function getGuardByUser(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userToken = new \App\Models\Users\UserToken($this->container->db);
        // $userId = $userToken->find('token', '90c4a9cebeaae6515c7dd4d265271bf6');
        $userId = $userToken->getUserId($token);
        $guards = $guard->findGuards('guard_id', $args['id'], 'user_id', $userId['user_id']);
// var_dump($guards);die();
        $query = $request->getQueryParams();
         if ($userId['user_id'] || $guards ) {
                $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
                $userGuard = $guard->getUserId($userId['user_id'])->setPaginate($page, 5);
                // var_dump($userGuard);die();
             $data = $this->responseDetail(200, false, 'Berhasil menampilkan data', [
                    'query'     =>  $query,
                    'data'    =>  $userGuard['data'],
                    'pagination'      =>  $userGuard['pagination'],
                ]);

        } else {
            $data = $this->responseDetail(400, true, 'Gagal menampilkan data');
        }
        return $data;
      }

    // Function get user by guard login
    public function getUser(Request $request, Response $response, $args)
    {
        $guard = new GuardModel($this->db);
        $token = $request->getHeader('Authorization')[0];
        $userToken = new \App\Models\Users\UserToken($this->container->db);
        $userId = $userToken->getUserId($token);
        $query = $request->getQueryParams();
         if ($userId) {
                $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
                $userGuard = $guard->findAllUser($userId)->setPaginate($page, 5);
                // var_dump($userGuard);die();
             $data = $this->responseDetail(200, false, 'Berhasil menampilkan user', [
                    'data'    =>  $userGuard['data'],
                    'pagination'      =>  $userGuard['pagination'],
                ]);

        } else {
            $data = $this->responseDetail(400, true, 'Gagal menampilkan user');
        }
        return $data;
    }
}
