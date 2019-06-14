<?php

namespace App\Queue;

use App\Greencheck\Checker;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\CommandSubscriberInterface;
use Enqueue\Client\ProducerInterface;
use Interop\Queue\Processor;
use Interop\Queue\Message;
use Interop\Queue\Context;
use Enqueue\Util\JSON;
use Enqueue\Consumption\Result;
use Liuggio\StatsdClient\Factory\StatsdDataFactory;
use Liuggio\StatsdClient\StatsdClient;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Predis\Client;
use TGWF\Greencheck\Repository\GreencheckAsRepository;
use TGWF\Greencheck\Repository\GreencheckIpRepository;
use TGWF\Greencheck\Repository\GreencheckTldRepository;
use TGWF\Greencheck\Repository\GreencheckUrlRepository;
use TGWF\Greencheck\Sitecheck;
use TGWF\Greencheck\Sitecheck\Cache;

class GreencheckProcessor implements Processor, CommandSubscriberInterface
{
    /**
     * @var Checker
     */
    private $checker;
    /**
     * @var ParameterBagInterface
     */
    private $params;
    /**
     * @var StatsdDataFactory
     */
    private $statsdDataFactory;
    /**
     * @var StatsdClient
     */
    private $statsdClient;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ProducerInterface
     */
    private $producer;

    /**
     * @var GreencheckUrlRepository
     */
    private $greencheckUrlRepository;

    /**
     * @var GreencheckIpRepository
     */
    private $greencheckIpRepository;

    /**
     * @var GreencheckAsRepository
     */
    private $greencheckAsRepository;

    /**
     * @var GreencheckTldRepository
     */
    private $greencheckTldRepository;

    public function __construct(
        ParameterBagInterface $params,
        StatsdDataFactory $statsdDataFactory,
        StatsdClient $statsdClient,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        ProducerInterface $producer
    ) {
        $this->params = $params;
        $this->statsdDataFactory = $statsdDataFactory;
        $this->statsdClient = $statsdClient;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->producer = $producer;
    }

    public function process(Message $message, Context $context)
    {
        $this->logger->debug('GreencheckProcessor called');
        $checker = $this->initializeChecker();

        $this->logger->debug($message->getBody());

        $request = JSON::decode($message->getBody());
        $result = $checker->greencheck($request);

        $replyMessage = $context->createMessage(JSON::encode($result));

        return Result::reply($replyMessage);
    }

    public static function getSubscribedCommand()
    {
        return [
            'processorName' => 'greencheck_prio',
            // these are optional, setting these option we make the migration smooth and backward compatible.
            'queueName' => 'greencheck_prio',
            'queueNameHardcoded' => true,
            'exclusive' => true,
        ];
    }

    /**
     * Initialize checker service once.
     */
    public function initializeChecker()
    {
        if (null !== $this->checker) {
            return $this->checker;
        }

        $config = $this->params->get('greencheck');

        $cache = new Cache($config);
        $cache->setCache('default');

        $redis = new Client();

        // @todo inject these in constructor
        $this->greencheckUrlRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $this->greencheckIpRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $this->greencheckAsRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $this->greencheckTldRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $siteCheck = new Sitecheck($this->greencheckUrlRepository, $this->greencheckIpRepository, $this->greencheckAsRepository, $this->greencheckTldRepository, $cache, new Sitecheck\Logger($this->entityManager), 'api');
        $siteCheck->disableLog();

        // @todo make this a proper service and inject it
        return $this->checker = new Checker($siteCheck, $this->statsdDataFactory, $this->statsdClient, $this->logger, $this->producer, $redis, $config['mock']);
    }
}
