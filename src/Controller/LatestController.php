<?php

namespace App\Controller;

use Predis\Client;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class LatestController.
 */
class LatestController extends AbstractController
{
    /**
     * @var Client
     */
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    /**
     * @param Request $request
     *
     * @return Response
     *
     * @Route(path="/checks/latest")
     */
    public function indexAction(Request $request)
    {
        $checks = $this->client->lrange('latest_checks', 0, 10);

        $callback = $request->query->get('callback');

        return $this->returnJson($checks, $callback, false);
    }

    private function returnJson($data, $callback, $cache = false)
    {
        $json = json_encode($data);
        if ('' != $callback) {
            $json = $callback.'('.$json.')';
        }
        $response = new Response($json);
        $response->headers->set('Content-type', 'application/json; charset=utf-8');
        $response->headers->set('Access-Control-Allow-Origin', '*');
        $response->headers->set('Access-Control-Allow-Methods', 'GET');
        $response->headers->set('Access-Control-Allow-Headers', 'x-requested-with');
        if ($cache) {
            $response->setPublic();
            $response->setSharedMaxAge(1);
        }

        return $response;
    }
}
