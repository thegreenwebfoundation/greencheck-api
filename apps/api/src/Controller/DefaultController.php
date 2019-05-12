<?php

namespace App\Controller;

use App\Greencheck\ImageGenerator;
use Predis\Client;
use Enqueue\Client\ProducerInterface;
use Enqueue\Util\JSON;
use Liuggio\StatsdClient\Factory\StatsdDataFactory;
use Liuggio\StatsdClient\StatsdClient;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

/**
 * Class DefaultController.
 *
 * @todo figure out if all entrypoints are still used
 */
class DefaultController extends AbstractController
{
    /**
     * @var StatsdDataFactory
     */
    private $statsdDataFactory;
    /**
     * @var StatsdClient
     */
    private $statsdClient;
    /**
     * @var RequestStack
     */
    private $requestStack;

    /**
     * @var ImageGenerator
     */
    private $imageGenerator;
    /**
     * @var ProducerInterface
     */
    private $producer;

    public function __construct(
        StatsdDataFactory $statsdDataFactory,
        StatsdClient $statsdClient,
        RequestStack $requestStack,
        ImageGenerator $imageGenerator,
        ProducerInterface $producer,
        Client $redis
    ) {
        $this->statsdDataFactory = $statsdDataFactory;
        $this->statsdClient = $statsdClient;
        $this->requestStack = $requestStack;
        $this->imageGenerator = $imageGenerator;
        $this->producer = $producer;
        $this->redis = $redis;

    }

    /**
     * @Route(path="/")
     *
     * @return Response
     */
    public function indexAction()
    {
        return new Response(file_get_contents($this->getParameter('kernel.project_dir').'/public/index.html'));
    }

    /**
     * @Route(path="/getip")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function getipAction(Request $request)
    {
        $ip = $request->getClientIp();

        return $this->returnJson($ip, false);
    }

    /**
     * @Route(path="/client/{url}")
     *
     * @param Request $request
     * @param $url
     *
     * @return RedirectResponse
     */
    public function clientAction(Request $request, $url = '')
    {
        return RedirectResponse::create('https://www.thegreenwebfoundation.org/green-web-check/?url='.$url);
    }

    /**
     * @Route(path="/greencheck/{url}")
     *
     * @param Request $request
     * @param $url
     *
     * @return RedirectResponse|Response
     */
    public function greencheckAction(Request $request, $url)
    {
        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');
        $blind = $request->get('blind', false);
        if (false !== $blind) {
            $blind = true;
        }

        if ('' == $url) {
            return $this->handleEmptyUrl($request, $url);
        }

        $result = $this->doGreencheck($url, $ip, $browser, 'api', $blind);

        $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.greencheck.check'));

        return $this->returnJson($result, true);
    }

    /**
     * @Route(path="/greencheck-async/{url}")
     *
     * @param Request $request
     * @param $url
     *
     * @return RedirectResponse|Response
     */
    public function greencheckActionAsync(Request $request, $url)
    {
        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');
        $blind = $request->get('blind', false);
        if (false !== $blind) {
            $blind = true;
        }

        if ('' == $url) {
            return $this->handleEmptyUrl($request, $url);
        }

        $result = $this->doGreencheckAsync($url, $ip, $browser, 'api', $blind);

        $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.greencheck.check'));

        return $this->returnJson($result, true);
    }

    /**
     * @Route(path="/greencheckimage/{url}")
     *
     * @param Request $request
     * @param $url
     *
     * @return RedirectResponse|Response
     */
    public function greencheckimageAction(Request $request, $url)
    {
        $acceptableTypes = $request->getAcceptableContentTypes();
        $imagefound = true;
        // If there is a text/html or similar content type, then the browser is calling the url directly instead of from a <img> tag
        // Changed for firefox which now only sens */*
        foreach ($acceptableTypes as $type) {
            if (false !== strpos($type, 'text')) {
                // Direct link
                $imagefound = false;
            }
        }

        $download = $request->query->get('download', false);
        $direct = $request->query->get('direct', false);

        if (false === $imagefound && false === $download && false === $direct) {
            // Opened in browser window
            // Return a html page here
            return $this->redirect('https://www.thegreenwebfoundation.org/?check='.$url);
        }

        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');
        $sponsored = $request->get('sponsored', false);

        if ('' == $url) {
            // Url in get request? Some strange hackery needed for Symfony2
            return $this->handleEmptyUrl($request, $url);
        }

        $result = $this->doGreencheck($url, $ip, $browser, 'api');

        $data = $this->statsdDataFactory->increment('api.actions.greencheck.image');
        $this->statsdClient->send($data);

        $str = $this->imageGenerator->createImage($sponsored, $result);

        $response = new Response($str);
        $response->headers->add(['Content-Type' => 'image/png']);
        if ($download) {
            $response->headers->set('Content-Disposition', sprintf('attachment; filename="%s"', 'greencheck'.$url.'.png'));
        }

        return $response;
    }

    /**
     * @Route(path="/greencheckmulti/{data}", requirements={"data"=".+"})
     *
     * @param Request $request
     * @param $data
     *
     * @return Response
     *
     * @deprecated use v2/greencheckmulti. still used
     */
    public function multipleAction(Request $request, $data)
    {
        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');
        $source = 'apisearch';

        $results = new \StdClass();
        if (null === $data || '' == $data) {
            $results = ['error' => 'No url provided'];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        $data = json_decode($data);
        if (0 === count($data)) {
            $results = ['error' => 'No url provided'];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        return $this->handleMultiGreencheck($data, $ip, $browser, $source, false, $results);
    }

    /**
     * @Route(path="/v2/greencheckmulti/{data}", requirements={"data"=".+"})
     *
     * @param Request $request
     * @param $data
     *
     * @return Response
     */
    public function multiplev2Action(Request $request, $data)
    {
        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');
        $source = 'apisearch';
        $blind = $request->get('blind', false);
        if (false !== $blind) {
            $blind = true;
        }

        $results = new \StdClass();
        if (null === $data || '' == $data) {
            $results = ['error' => 'No url provided'];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        $data = json_decode($data);
        if (0 === count($data)) {
            $results = ['error' => "Can't deserialize data - ".json_last_error_msg()];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        return $this->handleMultiGreencheck($data, $ip, $browser, $source, $blind, $results);
    }

    /**
     * @Route(path="/json-multi.php")
     *
     * @param Request $request
     * @param $source
     *
     * @return mixed
     *
     * @deprecated still used by old extensions
     */
    public function multiAction(Request $request, $source = 'api')
    {
        $ip = $request->getClientIp();
        $browser = $request->server->get('HTTP_USER_AGENT');

        $fakerequest = Request::create($request->getRequestUri());
        $url = $fakerequest->query->get('url');
        if (!is_null($url)) {
            if (isset($_GET['useragent'])) {
                $browser = $_GET['useragent'];
            }
            if (isset($_GET['origip'])) {
                $ip = $_GET['origip'];
                $source = 'website';
            }
            $result = $this->doGreencheck($url, $ip, $browser, $source);

            return $this->returnJson($result);
        }

        if ('api' == $source) {
            $source = 'apisearch';
        }
        $results = new \StdClass();
        $data = $fakerequest->query->get('data');
        if (null === $data || '' == $data) {
            $results = ['error' => 'No url provided'];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        $data = json_decode($data);

        if (0 == count($data)) {
            $results = ['error' => 'No url provided'];
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.error.no_url_provided'));

            return $this->returnJson($results);
        }

        return $this->handleMultiGreencheck($data, $ip, $browser, $source, false, $results);
    }

    /**
     * @param Request $request
     * @param $url
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     */
    private function handleEmptyUrl(Request $request)
    {
        // Url in get request? Some strange hackery needed for Symfony2
        $fakerequest = Request::create($request->getRequestUri());
        $url = $fakerequest->query->get('url');
        if (null === $url) {
            $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.greencheck.error.no_url_provided'));

            return $this->returnJson(['error' => 'No url provided']);
        }

        return $this->redirect('/greencheck/'.$url);
    }

    /**
     * We fire off multiple commands to our queue and we get promises back that we will at some point get a returned result
     * After fireing off all commands we loop through all promises and collect their results.
     *
     * @param $data
     * @param $ip
     * @param $browser
     * @param $source
     * @param $blind
     * @param \StdClass $results
     *
     * @return Response
     */
    public function handleMultiGreencheck($data, $ip, $browser, $source, $blind, \StdClass $results)
    {
        $promises = [];
        foreach ($data as $key => $url) {
            $taskdata = ['key' => $url, 'url' => $url, 'ip' => $ip, 'browser' => $browser, 'source' => $source, 'blind' => $blind];
            $promises[] = $this->producer->sendCommand('greencheck', JSON::encode($taskdata), $needReply = true);
        }

        foreach ($promises as $promise) {
            $replyMessage = $promise->receive();
            $data = JSON::decode($replyMessage->getBody());
            $key = $data['key'];
            $results->$key = $data['result'];
        }

        $result = get_object_vars($results);

        $this->statsdClient->send($this->statsdDataFactory->increment('api.actions.multi.check'));

        return $this->returnJson($result);
    }

    /**
     * @param $url
     * @param $ip
     * @param $browser
     * @param string $source
     * @param bool   $blind
     *
     * @return mixed
     */
    private function doGreencheck($url, $ip, $browser, $source = 'api', $blind = false)
    {
        $promise = $this->producer->sendCommand('greencheck', JSON::encode(['key' => 0, 'url' => $url, 'ip' => $ip, 'browser' => $browser, 'source' => $source, 'blind' => $blind]), $needReply = true);
        $replyMessage = $promise->receive();
        $data = JSON::decode($replyMessage->getBody());

        return $data['result'];
    }

     /**
     * @param $url
     * @param $ip
     * @param $browser
     * @param string $source
     * @param bool   $blind
     *
     * @return mixed
     */
    private function doGreencheckAsync($url, $ip, $browser, $source = 'api', $blind = false)
    {
        $this->producer->sendCommand('greencheck', JSON::encode(['key' => 0, 'url' => $url, 'ip' => $ip, 'browser' => $browser, 'source' => $source, 'blind' => $blind]), $needReply = false);

        $cachedLookup = $this->getGreencheckResultFromCache($url);

        if (null === $cachedLookup) {
            return array(
                "green" => false,
                "url" => $url,
                "data" => false
            );
        }

        $data = json_decode($cachedLookup);

        return $data;
    }
    /**
     * fetch the results for the URL synchronously
     * @param $url
     *
     * @return mixed
     */
    private function getGreencheckResultFromCache($url)
    {
        return $this->redis->get("domains:$url");
    }



    /**
     * @param $result
     * @param bool $cache
     *
     * @return Response
     */
    private function returnJson($result, $cache = false)
    {
        $json = json_encode($result);

        $callback = $this->requestStack->getCurrentRequest()->query->get('callback');
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
            $response->setSharedMaxAge(3600);
        }

        return $response;
    }
}
