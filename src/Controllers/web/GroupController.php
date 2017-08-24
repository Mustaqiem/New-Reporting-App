<?php

namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use GuzzleHttp\Exception\BadResponseException as GuzzleException;
use App\Models\UserGroupModel;
use App\Models\GroupModel;
use GuzzleHttp;
use GuzzleHttp\Subscriber\Oauth\Oauth1;

class GroupController extends BaseController
{
	//Get active Group
	public function index($request, $response)
	{
		$query = $request->getQueryParams();

        try {
            $result = $this->client->request('GET', 'group/list'.$request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

		// var_dump($data);die();
		return $this->view->render($response, 'users/group-list.twig', [
			'data'			=> $data['data'],
			'pagination'	=> $data['pagination']
		]);

	}

	//Get Group user
	public function getGeneralGroup($request, $response)
	{
        try {
            $result = $this->client->request('GET', 'user/groups',[
				'query' => [
					'perpage' => 10,
					'page' => $request->getQueryParam('page')
		   ]]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
		// var_dump($data);die();
		if (!isset($data['pagination'])) {
			$data['pagination'] = null;
		}
		return $this->view->render($response, 'users/group-list.twig', [
			'data'			=>	$data['data'],
			'pagination'	=>	$data['pagination']
		]);
	}

	public function enter($request, $response, $args)
	{
		$query = $request->getQueryParams();

        try {
            $result = $this->client->request('GET', 'group/'.$args['id'].'/member'.
			$request->getUri()->getQuery());
			// $result->addHeader('Authorization', '7e505da11dd87b99ba9a4ed644a20ba4');

        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents());

		// var_dump($data->reporting->results);die();
		return $this->view->render($response, 'pic/group-timeline.twig', [
			'members'	=> $data->reporting->results,
			'pagination'	=> $data->reporting->meta,
		]);
	}


	//Find group by id
	public function findGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.group.detail', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage('errors', 'Data tidak ditemukan');
		}
		return $this->view->render($response, 'admin/group/detail.twig', $content->reporting);
	}
	//Get create group
	public function getAdd($request, $response)
	{
		return $this->view->render($response, 'admin/group/add.twig');
	}
	//Post create group
	public function add($request, $response)
	{
		$query = $request->getQueryParams();

		$data = [
			'name' 			=>	$request->getParams()['name'],
			'description'	=>	$request->getParams()['description'],
			// 'image'			=>	$request->getParams()['image'],
		];
		try {
            $result = $this->client->request('POST', 'group/create',
                ['form_params' => [
                    'name' 			=> $request->getParam('name'),
                    'description'	=> $request->getParam('description')
                ]
            ]);
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
		}

		$content = $result->getBody()->getContents();
        $content = json_decode($content, true);

		return $this->view->render($response, 'users/group-list.twig', [
			'data'	=>	$content
		]);
	}
	//Get edit group
	public function getUpdate($request, $response, $args)
	{
		$group = new GroupModel($this->db);
        $data['group'] = $group->find('id', $args['id']);
		return $this->view->render($response, 'admin/group/edit.twig', $data);
	}
	//Edit group
	public function update($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.group.update', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($client->getResponse()->getBody()->getContents());
		}
	}
	//Set inactive/soft delete group
	public function setInactive($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.softdelete.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
            $this->flash->addMessage('success', 'Berhasil menghapus data');
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
            $this->flash->addMessage('errors', 'Data tidak ditemukan');
		}
		return $response->withRedirect($this->router->pathFor('group.list'));
	}
	//restore
	public function restore($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.restore.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
            $this->flash->addMessage('errors', 'Data tidak ditemukan');
		}
		return $response->withRedirect($this->router->pathFor('group.list'));
	}
	//Set user as member or PIC of group
	public function setUserGroup($request, $response)
	{
		$data = [
				'group_id' 			=>	$request->getParams()['group_id'],
				'user_id'			=>	$request->getParams()['user_id']
			];
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.user.add.group'), [
					'json' => $data
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}
		return $this->router->pathFor('user.group.get', ['id' => $groupId]);
	}

	//Get all member in group
	public function getAllGroupMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET','group/member/all',[
				'query' => [
					'perpage' 	=> 10,
					'page' 		=> $request->getQueryParam('page'),
					'user_id' 	=> $_SESSION['login']['id'],
					'group_id' 	=> $args['id']
		   ]]);
			$content = json_decode($client->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true);
		}

		if ($content['error'] == false) {
			return $this->view->render($response, 'users/group/member.twig', [
				'data'			=>	$content['data'],
				'pagination'  	=>	$content['pagination'],
				// 'group' 		=> 	$dataGroup['data']
			]);
		} else {
			$this->flash->addMessage('warning', $content['message']);
			return $response->withRedirect($this->router->pathFor('login'));
		}
	}


	//Get all user in group
	public function getGroupMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET','group/members',[
				'query' => [
					'perpage' 	=> 10,
					'page' 		=> $request->getQueryParam('page'),
					'user_id' 	=> $_SESSION['login']['id'],
					'group_id' 	=> $args['id']
		   ]]);
			$content = json_decode($client->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true);
		}

		if ($data['error'] == false) {
			return $this->view->render($response, 'users/group/member.twig', [
				'data'			=>	$content['data'],
				'pagination'  	=>	$content['pagination'],
				// 'group' 		=> 	$dataGroup['data']
			]);
		} else {
			$this->flash->addMessage('warning', $content['message']);
			return $response->withRedirect($this->router->pathFor('login'));
		}
	}


	public function getGroupPic($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET','group/pics',[
				'query' => [
					'perpage' 	=> 10,
					'page' 		=> $request->getQueryParam('page'),
					'user_id' 	=> $_SESSION['login']['id'],
					'group_id' 	=> $args['id']
		   ]]);
			$content = json_decode($client->getBody()->getContents(), true);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents(), true);
		}

		// var_dump($content);die();
		if ($data['error'] == false) {
			return $this->view->render($response, 'users/group/pic.twig', [
				'data'			=>	$content['data'],
				'pagination'  	=>	$content['pagination'],
				// 'group' 		=> 	$dataGroup['data']
			]);
        } else {
            $this->flash->addMessage('warning', $content['message']);
            return $response->withRedirect($this->router->pathFor('login'));
        }
		// return $this->view->render($response, '', $content->reporting);
	}

	//Get all user in group
	public function getNotMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getNotMember', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}
		return $this->router->pathFor('home');
	}
	//Set user as member of group
	public function setMemberGroup($request, $response, $args)
	{
			$data = [
				'group_id' 			=>	$request->getParams()['group_id'],
				'user_id'			=>	$request->getParams()['user_id']
			];
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('pic.member.group.set'), [
					'json' => $data
			]);
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}
		return $this->router->pathFor('user.group.get', ['id' => $groupId]);
	}
	public function getGroup($request, $response)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getGroup'));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
		}
		return $this->view->render($response, '', $content->reporting);
	}
	// public function getPicGroup($request, $response)
	// {
	// 	try {
	// 		$client = $this->client->request('GET',
	// 					$this->router->pathFor('api.getPic'));
	// 		$content = json_decode($client->getBody()->getContents());
	// 	} catch (GuzzleException $e) {
	// 		$content = json_decode($e->getResponse()->getBody()->getContents());
	// 	}
	// 	return $this->view->render($response, '', $content->reporting);
	// }
	public function getPicGroup($request, $response)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.getPicGroup'));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
			var_dump($content);die();
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage('error', 'Data tidak ditemukan');
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//Post create group
	public function createByUser($request, $response)
	{
		$query = $request->getQueryParams();
		try {
            $result = $this->client->request('POST', 'group/pic/create',
                ['query' => [
                    'name' 			=> $request->getParam('name'),
                    'description'	=> $request->getParam('description'),
                    'image'			=> $request->getParam('description')
                ]
            ]);
			$this->flash->addMessage('succes', 'Berhasil menambah group');
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
			$this->flash->addMessage('error', 'Gagal menambahkan group');
		}

		$content = $result->getBody()->getContents();
        $content = json_decode($content, true);

		// return $this->router->pathFor('group.user');
    	return $response->withRedirect($this->router->pathFor('group.user'));
	}

	//Find group by id
	public function delGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.delGroup', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}
		return $this->router->render($response, 'user.group', $content->reporting);
	}
	public function searchGroup($request, $response)
    {
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.search.group'));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}
        return $this->view->render($response, 'users/user/found-group.twig', $content->reporting);
    }

    //leave group
	public function leaveGroup($request, $response, $args)
    {
    	$query = $request->getQueryParams();
    	try {
    		$result = $this->client->request('GET', 'group/'.$args['id'].'/leave'.
    			$request->getUri()->getQuery());
            $this->flash->addMessage('succes', 'Berhasil meninggalkan group');
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat meninggalkan group');
    	}

		$data = json_decode($result->getBody()->getContents(), true);

    	return $response->withRedirect($this->router->pathFor('group.user'));
    }
	//Delete group
	public function delete($request, $response, $args)
	{
		try {
			$client = $this->client->request('DELETE',
						$this->router->pathFor('api.group.delete', ['id' => $args['id']]));
			$content = json_decode($client->getBody()->getContents());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage(404, 'Data tidak ditemukan');
		}
		return $this->view->render($response, 'admin/group/index.twig', $content->reporting);
	}
	//Set user as member of group
	public function joinGroup($request, $response, $args)
	{
		try {
			$client = $this->client->request('GET',
						$this->router->pathFor('api.join.group', ['id' => $args['id']]));
			$content = json_decode($client->getBody());
		} catch (GuzzleException $e) {
			$content = json_decode($e->getResponse()->getBody()->getContents());
			$this->flash->addMessage(400, 'Anda sudah bergabung dengan group');
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//set As guardian
	public function setAsGuardian($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT',
						$this->router->pathFor('api.user.set.guardian',
								['group' => $args['group'], 'id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//set As member
	public function setAsMember($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT',
						$this->router->pathFor('api.user.set.member',
								['id' => $args['id'], 'group' => $args['group']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//set As PIC
	public function setAsPic($request, $response, $args)
	{
		try {
			$client = $this->client->request('PUT',
						$this->router->pathFor('api.user.set.pic',
								['id' => $args['id'], 'group' => $args['group']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//delete user
	public function deleteUser($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.delete.user.group',
								['group' => $args['group'], 'id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}
		return $this->view->render($response, '', $content->reporting);
	}
	//delete user
	public function postImage($request, $response, $args)
	{
		try {
			$client = $this->client->request('POST',
						$this->router->pathFor('api.change.photo.group',
								['id' => $args['id']]));
			$client = $client->getBody()->getContents();
			$content = json_decode($client);
		} catch (GuzzleException $e) {
			$client = $e->getResponse()->getBody()->getContents();
			$content = json_decode($client);
		}
		return $this->view->render($response, '', $content->reporting);
	}
}

?>
