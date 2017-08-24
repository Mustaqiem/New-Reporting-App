<?php
namespace App\Controllers\web;

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use App\Models\Item as Item;
use App\Models\UserItem;

/**
*
*/
class ItemController extends BaseController
{
    //Get group item unreported
    public function getGroupItem($request, $response, $args)
	{
        try {
            $result = $this->client->request('GET', 'item/group/'.$args['group'],[
                'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

                    try {
                        $findGroup = $this->client->request('GET', 'group/find/'.$args['group']);
                    } catch (GuzzleException $e) {
                        $findGroup = $e->getResponse();
                    }
                    $dataGroup = json_decode($findGroup->getBody()->getContents(), true);

                } catch (GuzzleException $e) {
                    $result = $e->getResponse();
                }

        $data = json_decode($result->getBody()->getContents(), true);
        var_dump($data);die();
        if (!isset($data['pagination'])) {
        $data['pagination'] = null;
        }
		return $this->view->render($response, 'users/group/unreported-item.twig', [
			'data'			=>	$data['data'],
			'pagination'  	=>	$data['pagination'],
			'group' 		=> 	$dataGroup['data']
		]);
	}

	//create item by user
	public function createItemUser($request, $response, $args)
	{
		$query = $request->getQueryParams();

		try {
			$result = $this->client->request('POST', 'item/'.$args['group'], [
				'query' => [
					'name'          => $request->getParam('name'),
	                'description'   => $request->getParam('description'),
	                'recurrent'     => $request->getParam('recurrent'),
	                'start_date'    => $request->getParam('start_date'),
	                'user_id'    	=> $_SESSION['key']['key_token'],
	                'group_id'      => $args['group'],
	                'creator'    	=> $_SESSION['key']['key_token'],
	                'image'         => $request->getParam('image'),
	                'public'        => $request->getParam('public'),
	                'status'        => 0,
	                'reported_at'   => null,
				]
			]);
            $this->flash->addMessage('succes', 'Berhasil membuat item');
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat membuat item');
		}

		$content = $result->getBody()->getContents();
        $content = json_decode($content, true);

    	return $response->withRedirect($this->router->pathFor('group.user'), [
            'group' 		=> 	$args['group']
        ]);
    }

	//Get group item reported
    public function getReportedGroupItem($request, $response, $args)
    {
    	try {
    		$result = $this->client->request('GET', 'item/group/'.$args['group'].'/reported',[
                'query' => [
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

                try {
                    $findGroup = $this->client->request('GET', 'group/find/'.$args['group']);
                } catch (GuzzleException $e) {
                    $findGroup = $e->getResponse();
                }
                $dataGroup = json_decode($findGroup->getBody()->getContents(), true);
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
    	}

		$data = json_decode($result->getBody()->getContents(), true);

		return $this->view->render($response, 'users/group/reported-item.twig', [
			'data'			=>	$data['data'],
			'pagination'	=>	$data['pagination'],
			'group' 		=> 	$dataGroup['data'],
		]);
    }

    //create item by user
	public function reportItem($request, $response, $args)
	{
		try {
			$result = $this->client->request('PUT', 'item/report/'.$args['item'],
                ['form_params' => [
                    'description'   => $request->getParam('description'),
                ]
            ]);
            // $this->flash->addMessage('succes', 'Berhasil melaporkan tugas');
		} catch (GuzzleException $e) {
			$result = $e->getResponse();
            // $this->flash->addMessage('error', 'Ada kesalahan saat melaporkan tugas');
		}

        $content = json_decode($result->getBody()->getContents(), true);
var_dump($content);die();
    	return $response->withRedirect($this->router->pathFor('group.user'), [
            'group' 		=> 	$args['group']
        ]);
	}
	//Delete item by user
    public function deleteItemByUser($request, $response, $args)
    {
    	$query = $request->getQueryParams();

    	try {
    		// $item = $this->client->request('GET', '/items/group/'.$args['group']);
    		$data = $this->client->request('GET', 'item/'.$args['item'].
    			$request->getUri()->getQuery());
            $this->flash->addMessage('succes', 'Berhasil menghapus tugas');
    	} catch (GuzzleException $e) {
    		$data = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat menghapus tugas');
    	}
		$dataDetailItem = json_decode($data->getBody()->getContents(), true);
		// var_dump($dataDetailItem['data']['group_id']);die();

    	try {
    		$result = $this->client->request('DELETE', 'item/'.$args['item'].'/user'.
    			$request->getUri()->getQuery());
            $this->flash->addMessage('succes', 'Berhasil menghapus tugas');
    	} catch (GuzzleException $e) {
    		$result = $e->getResponse();
            $this->flash->addMessage('error', 'Ada kesalahan saat menghapus tugas');
    	}

		$data = json_decode($result->getBody()->getContents(), true);

        return $response->withRedirect($this->router->pathFor('group.item', [
            'group' 		=> 	$dataDetailItem['data']['group_id']
        ]));

    }

    public function searchItemArchive($request, $response, $args)
    {
        // var_dump($request->getParams());die();
        if ($request->getParam('month') == 'all') {
            try {
                $result = $this->client->request('GET', 'item/'.$args['id'].'/year',[
                    'query' => [
                        'perpage' => 999,
                        'year' => $request->getParam('year')
                        ]]);
                    } catch (GuzzleException $e) {
                        $result = $e->getResponse();
                    }
        } else {
            try {
                $result = $this->client->request('GET', 'item/'.$args['id'].'/month',[
                    'query' => [
                        'perpage' => 999,
                        'month' => $request->getParam('month'),
                        'year' => $request->getParam('year')
                        ]]);
                    } catch (GuzzleException $e) {
                        $result = $e->getResponse();
                    }
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data);die();
        return $this->view->render($response, 'users/guard/report-archives.twig', [
            'data'		=>	$data['data'],
            'pagination'=>	$data['pagination'],
            'query' 	=> 	[
                'year'  => $request->getParam('year'),
                'month'  => $request->getParam('month')
            ]
        ]);
    }

    public function getItemArchive($request, $response, $args)
    {
        return  $this->view->render($response, 'users/guard/report-archives.twig');
    }

    //Get group item reported
    public function getReportedUserGroupItem($request, $response, $args)
    {
        $userId = $_SESSION['login']['id'];
        $groupId = $args['id'];
        try {
            $result = $this->client->request('GET', 'item/group/user/reported',[
                'query' => [
                    'user_id' =>  $args['user'],
                    'group_id' =>  $args['group'],
                    'perpage' => 10,
                    'page' => $request->getQueryParam('page')
                    ]]);

                try {
                    $findGroup = $this->client->request('GET', 'group/find/'. $args['group']);
                } catch (GuzzleException $e) {
                    $findGroup = $e->getResponse();
                }
                $dataGroup = json_decode($findGroup->getBody()->getContents(), true);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }
         $data= json_decode($result->getBody()->getContents(), true);
        //  var_dump($dataGroup['data']);die();

        return $this->view->render($response, 'users/group/reported-item.twig', [
            'data'			=>	$data['data'],
            'pagination'	=>	$data['pagination'],
            'group' 		=> 	$dataGroup['data'],
        ]);
    }


        //Get group item reported
        public function getUnreportedUserGroupItem($request, $response, $args)
        {
            // $userId = $_SESSION['login']['id'];
            // $groupId = $args['id'];
            try {
                $result = $this->client->request('GET', 'item/group/user/unreported',[
                    'query' => [
                        'user_id' =>  $args['user'],
                        'group_id' =>  $args['group'],
                        'perpage' => 10,
                        'page' => $request->getQueryParam('page')
                        ]]);

                    try {
                        $findGroup = $this->client->request('GET', 'group/find/'. $args['group']);
                    } catch (GuzzleException $e) {
                        $findGroup = $e->getResponse();
                    }
                    $dataGroup = json_decode($findGroup->getBody()->getContents(), true);
            } catch (GuzzleException $e) {
                $result = $e->getResponse();
            }
             $data= json_decode($result->getBody()->getContents(), true);

            return $this->view->render($response, 'users/group/unreported-item.twig', [
                'data'			=>	$data['data'],
                'pagination'	=>	$data['pagination'],
                'group' 		=> 	$dataGroup['data'],
            ]);
        }
}

 ?>
