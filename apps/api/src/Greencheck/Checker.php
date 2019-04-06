<?php

namespace App\Greencheck;

use Enqueue\Client\ProducerInterface;
use Liuggio\StatsdClient\Factory\StatsdDataFactory;
use Liuggio\StatsdClient\StatsdClient;
use Psr\Log\LoggerInterface;
use TGWF\Greencheck\Entity\Hostingprovider;
use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\SitecheckResult;

class Checker
{
    /**
     * @var Sitecheck
     */
    private $checker;

    /**
     * @var StatsdDataFactory
     */
    private $statsdDataFactory;
    /**
     * @var StatsdClient
     */
    private $statsdClient;

    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var
     */
    private $mock;

    /**
     * Checker constructor.
     *
     * @param Sitecheck         $checker
     * @param StatsdDataFactory $statsdDataFactory
     * @param StatsdClient      $statsdClient
     * @param LoggerInterface   $logger
     * @param ProducerInterface $producer
     */
    public function __construct(
        Sitecheck $checker,
        StatsdDataFactory $statsdDataFactory,
        StatsdClient $statsdClient,
        LoggerInterface $logger,
        ProducerInterface $producer,
        $mock
    ) {
        $this->checker = $checker;
        $this->statsdDataFactory = $statsdDataFactory;
        $this->statsdClient = $statsdClient;
        $this->logger = $logger;
        $this->producer = $producer;
        $this->mock = $mock;
    }

    /**
     * Run the greencheck on the workload.
     *
     * @param array $workload The job to handle
     *
     * @return array
     */
    public function greencheck($workload)
    {
        $time_start = microtime(true);

        // Make sure we only pass UTF8 urls down to the checker
        $url = mb_convert_encoding($workload['url'], 'UTF-8', 'UTF-8');
        $key = $workload['key'];
        $ip = $workload['ip'];
        $blind = false;
        if (isset($workload['blind'])) {
            $blind = $workload['blind'];
        }
        $browser = $workload['browser'];
        $source = $workload['source'];
        $this->logger->debug("Workload: $url");

        $result = ['result' => $this->check($url, $ip, $browser, $source, $blind), 'key' => $key];

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->logger->debug("Took $time seconds");
        //$this->logger->debug("Took " . count($this->logger->getQueries()) . " queries");
        //$this->logger->clearQueries();
        //
        $this->statsdClient->send($this->statsdDataFactory->increment('api.greencheck_job.checks'));
        $this->statsdClient->send($this->statsdDataFactory->timing('api.greencheck_job', $time));

        return $result;
    }

    /**
     * Check the given url.
     *
     * @param string $url     Url to check
     * @param string $ip      IP adress of client that requests check
     * @param string $browser Browser user agent from client
     * @param string $source  Source of the check ('api','apisearch','browser')
     *
     * @return array
     */
    public function check($url, $ip, $browser, $source, $blind)
    {
        $valid = $this->checker->validate($url);
        if ($valid) {
            if ($this->mock) {
                $resultobject = $this->getMock($url);
            } else {
                $resultobject = $this->checker->check($url, $ip, $browser, $source);
            }
            if ($resultobject->isCached()) {
                $this->logger->debug('Result was from cache');
                $this->statsdClient->send($this->statsdDataFactory->increment('api.greencheck_job.cached'));
            }

            if (false == $blind) {
                $this->logResult($resultobject);
            }

            if ($resultobject->isGreen()) {
                $this->statsdClient->send($this->statsdDataFactory->increment('api.greencheck_job.green'));
            } else {
                $this->statsdClient->send($this->statsdDataFactory->increment('api.greencheck_job.grey'));
            }

            $result = ['green' => $resultobject->isGreen(),
                'url' => mb_convert_encoding($resultobject->getCheckedUrl(), 'UTF-8', 'UTF-8'),
                'data' => $resultobject->isData(), ];
            if ('85.17.167.138' == $resultobject->getIpAddress()) {
                $result['icon'] = 'green';
            }
            if ($resultobject->isHostingProvider()) {
                $hp = $resultobject->getHostingProvider();
                if (null !== $hp) {
                    $result['hostedby'] = $hp->getNaam();
                    $result['hostedbyid'] = $hp->getId();
                    $result['hostedbywebsite'] = $hp->getWebsite();
                    $result['partner'] = $hp->getPartner();
                    if (!isset($result['icon']) && '' !== $hp->getIcon()) {
                        $result['icon'] = $hp->getIcon();
                        if (!isset($result['iconurl']) && '' !== $hp->getIconUrl()) {
                            $result['iconurl'] = $hp->getIconUrl();
                        }
                    }
                }
            }
        } else {
            $result = ['error' => 'Invalid url'];
            $data = $this->statsdDataFactory->increment('api.greencheck_job.invalidurl');
            $this->statsdClient->send($data);
        }

        return $result;
    }

    public function getMock($url)
    {
        $result = new SitecheckResult($url, $this->checker->getIpForUrl($url));

        $result->setCalledFrom(
            [
                'checked_by' => 'Mock',
                'checked_browser' => 'Mocked browser',
                'checked_through' => 'api',
            ]
        );

        $hpnew = new Hostingprovider();
        $hpnew->setNaam('Mocked Hoster');
        $hpnew->setPartner(true);
        $hpnew->setWebsite('http://www.thegreenwebfoundation.org');
        $result->setGreen(true);
        $result->setData(true);
        $result->setMatch('1', 'ip', 'ip');
        $result->setHostingProviderId('1');
        $result->setHostingProvider($hpnew);

        return $result;
    }

    /**
     * Send the result to the gearman logger.
     *
     * @param SitecheckResult $result The result from the greencheck
     */
    private function logResult($result)
    {
        $taskdata = ['result' => $result];
        $this->producer->sendEvent('greencheck_log', serialize($taskdata));
    }
}
