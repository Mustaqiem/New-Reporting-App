<?php
namespace App\Controllers\web;

use GuzzleHttp\Exception\BadResponseException as GuzzleException;

class HomeController extends BaseController
{
    public function index($request, $response)
    {
        $id = $_SESSION['login']['id'];
        try {
            $result = $this->client->request('GET', 'all-item/'.$id.'?',[
                 'query' => [
                     'perpage' => 10,
                     'page' => $request->getQueryParam('page')
 			]]);
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);
        // var_dump($data['pagination']);die();
        if (!isset($data['pagination'])) {
        $data['pagination'] = null;
        }
        if ($_SESSION['login']['status'] == 2) {
            $data = $this->view->render($response, 'users/home.twig', [
                'data'       =>	$data['data'],
                'pagination' =>	$data['pagination']
    		]);

        } elseif ($_SESSION['login']['status'] == 1) {
            $allArticle = count($article->getAll());
            $search = $request->getQueryParam('search');

            $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');

            if (!empty($search)) {
                $findAll = $article->search($request->getQueryParam('search'));
            } else {
                $findAll = $article->getArticle()->setPaginate($page, 3);
            }
// var_dump($findAll);die();
            $data = $this->view->render($response, 'users/home.twig', [
                'items' => $item->getAll()]);
        }

        return $data;
    }

    public function showItem($request, $response, $args)
    {
        // $id = $_SESSION['login']['id'];
        try {
            $result = $this->client->request('GET', 'item/show/'.$args['id'].'?'
            . $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $result = $e->getResponse();
        }

        $data = json_decode($result->getBody()->getContents(), true);

        try {
            $comment = $this->client->request('GET', 'item/comment/'.$args['id'].'?'
            . $request->getUri()->getQuery());
        } catch (GuzzleException $e) {
            $comment = $e->getResponse();
        }
        $allComment = json_decode($comment->getBody()->getContents(), true);

        // var_dump($data['data']);die();

        if ($data['data']) {
            return $this->view->render($response, 'users/show-item.twig', [
                'items' => $data['data'],
                'comment' => $allComment['data'],
            ]);
        } else {
            return $response->withRedirect($this->router->pathFor('home'));
        }
    }



    public function showItemDetail($request, $response, $args)
    {
        $items = new \App\Models\Item($this->db);

        $findItem = $items->find('id', $args['id']);

        if ($findItem['reported_at'] == null) {
            $itemDetails = $items->getUnreportedItemDetail($args['id']);

        }else {
            $itemDetails = $items->getReportedItemDetail($args['id']);
        }

        $newItem = array();
        foreach ($itemDetails as $item) {
            if (!empty($newItem[$item['id']])) {
                $currentValue = (array) $newItem[$item['id']]['comment'];
                $currentValue1 = (array) $newItem[$item['id']]['image'];
                $newItem[$item['id']]['comment'] = array_unique(array_merge($currentValue,
                 (array) $item['comment']));
                $newItem[$item['id']]['image'] =  array_unique(array_merge($currentValue1,
                 (array) $item['image']));
            } else {
                $newItem[$item['id']] = $item;
            }
        }
        $result = array_values($newItem);
        var_dump($result);die();
        return $this->view->render($response, 'users/show-item.twig', ['items' => $findItem]);
    }

    public function notFound($request, $response)
    {
        return $this->view->render($response, 'response/404.twig');
    }
}
//
// public function timeline($request, $response, $args)
// {
//     $items = new \App\Models\Item($this->db);
//
//     $findItem = $items->getAllGroupItem($args['id']);
//     $page = !$request->getQueryParam('page') ? 1 : $request->getQueryParam('page');
//
//     $newItem = array();
//     foreach ($findItem as $item) {
//         if (!empty($newItem[$item['id']])) {
//             // $currentValue = (array) $newItem[$item['id']]['comment'];
//             $currentValue1 = (array) $newItem[$item['id']]['image'];
//             // $newItem[$item['id']]['comment'] = array_unique(array_merge($currentValue, (array) $item['comment']));
//             $newItem[$item['id']]['image'] =  array_unique(array_merge($currentValue1, (array) $item['image']));
//         } else {
//             $newItem[$item['id']] = $item;
//         }
//     }
//     $result = $this->paginateArray(array_values($newItem), $page, 2);
//     var_dump($result);die();
//     return $this->view->render($response, 'users/show-item.twig', ['items' => $findItem]);
// }
