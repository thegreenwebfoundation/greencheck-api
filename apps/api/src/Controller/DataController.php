<?php

namespace App\Controller;

use App\Greencheck\DatabaseQueries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class DataController extends AbstractController
{
    /**
     * @var DatabaseQueries
     */
    private $databaseQueries;

    public function __construct(DatabaseQueries $databaseQueries)
    {
        $this->databaseQueries = $databaseQueries;
    }

    /**
     * @Route(path="/data/greenlist")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function greenlistAction(Request $request)
    {
        $data = $this->databaseQueries->getGreenlist();

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/datacenters")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function datacentersAction(Request $request)
    {
        $data = $this->databaseQueries->getDatacenters();

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/datacenter/{id}", requirements={"id"="\d+"})
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function datacenterAction($id, Request $request)
    {
        $data = $this->databaseQueries->getDatacenter($id);

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/hostingprovider/{id}", requirements={"id"="\d+"})
     *
     * @param $id
     * @param Request $request
     *
     * @return Response
     */
    public function hostingproviderAction($id, Request $request)
    {
        $hps = $this->databaseQueries->getHostingProvider($id);

        $dcs = $this->databaseQueries->getDatacentersForHostingProviders();
        foreach ($hps as $key => $hp) {
            $hp_id = $hp['id'];
            if (isset($dcs[$hp_id])) {
                // we have datacenter(s)
                $hps[$key]['datacenters'] = $dcs[$hp_id];
            } else {
                $hps[$key]['datacenters'] = [];
            }
        }

        $data = $hps;

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/hostingproviders")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function hostingprovidersAction(Request $request)
    {
        $hps = $this->databaseQueries->getHostingProviders();

        $dcs = $this->databaseQueries->getDatacentersForHostingProviders();
        foreach ($hps as $key => $hp) {
            $hp_id = $hp['id'];
            if (isset($dcs[$hp_id])) {
                // we have datacenter(s)
                $hps[$key]['datacenters'] = $dcs[$hp_id];
            } else {
                $hps[$key]['datacenters'] = [];
            }
        }

        $data = $hps;

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/hostingproviders/{country}")
     *
     * @param $country
     * @param Request $request
     *
     * @return Response
     */
    public function hostingprovidersCountryAction($country, Request $request)
    {
        $data = $this->databaseQueries->getHostingProvidersCountry($country);

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/countrydomains")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function countrydomainsAction(Request $request)
    {
        $data = $this->databaseQueries->getHostingProvidersTLD();

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/directory")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function directoryAction(Request $request)
    {
        $data = $this->databaseQueries->getHostingProvidersTLDDirectory();

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
    }

    /**
     * @Route(path="/data/hostingproviders/latest")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function latestprovidersAction(Request $request)
    {
        $hps = $this->databaseQueries->getLatestProviders();
        $data = $hps;

        $callback = $request->query->get('callback');

        return $this->returnJson($data, $callback, true);
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
            $response->setSharedMaxAge(600);
        }

        return $response;
    }
}
