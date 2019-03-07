<?php

namespace App\Controller;

use App\Greencheck\DatabaseQueries;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class StatsController extends AbstractController
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
     * @Route(path="/stats")
     *
     * @param Request $request
     *
     * @return Response
     */
    public function indexAction(Request $request)
    {
        $stats = $this->databaseQueries->getUniqueUsers();
        $total = $this->databaseQueries->getTotalStats();

        $data = [
                'api' => ['users' => $stats['api']['count'], 'checks' => $stats['api']['checks']],
                'total' => ['users' => $total[0]['users'], 'checks' => $total[0]['checks']],
        ];
        if (isset($stats['apisearch'])) {
            $data['search'] = ['users' => $stats['apisearch']['count'], 'checks' => $stats['apisearch']['checks']];
            $data['results'] = ['checks' => $stats['api']['checks'] + $stats['apisearch']['checks']];
        } else {
            $data['results'] = ['checks' => $stats['api']['checks']];
        }

        $data['tld']['count'] = $this->databaseQueries->getTlds();
        $data['tld']['percentage'] = number_format($this->databaseQueries->getPercentage(), 1);

        $data['weekly'] = $this->databaseQueries->getWeeklyStats();
        $data['daily'] = $this->databaseQueries->getDailyStats();

        $domains = $this->databaseQueries->getCountryDomains();
        $hps = $this->databaseQueries->getHostingProvidersCount();
        $data['ge']['countries'] = count($domains);
        $data['ge']['hosters'] = $hps;

        $ges = $this->databaseQueries->getEnergyCompanies();
        $data['ge']['companies'] = $ges;

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
