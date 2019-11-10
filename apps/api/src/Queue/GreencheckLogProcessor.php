<?php

namespace App\Queue;

use App\Greencheck\Logger;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\TopicSubscriberInterface;
use Interop\Queue\Processor;
use Interop\Queue\Message;
use Interop\Queue\Context;
use Predis\Client;
use Psr\Log\LoggerInterface;

class GreencheckLogProcessor implements Processor, TopicSubscriberInterface
{
    /**
     * Protection at memory exhaustion.
     *
     * @var int
     */
    private $count = 0;
    private $diecount = 0;

    /**
     * @var Logger
     */
    private $greencheckLogger;
    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Client
     */
    private $redis;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        Client $redis,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->redis = $redis;
        $this->logger = $logger;
    }

    public function process(Message $message, Context $context)
    {
        $this->logger->debug('GreencheckLogProcessor called');
        $greencheckLogger = $this->initializeLogger();

        $request = unserialize($message->getBody());

        $greencheckLogger->logResult($request['result']);

        ++$this->count;
        $this->logger->debug('Counting logs to persist: '.$this->count);
        if (50 == $this->count) {
            $this->logger->debug('Flushing to database');
            $this->entityManager->flush();
            $this->count = 0;
            ++$this->diecount;
            if (20 == $this->diecount) {
                $this->logger->debug('Sleeping for supervisor');
                sleep(1);
                die('Processed enough, quitting');
            }
        }

        return self::ACK;
    }

    public static function getSubscribedTopics()
    {
        return ['greencheck_log'];
    }

    private function initializeLogger()
    {
        if (null !== $this->greencheckLogger) {
            return $this->greencheckLogger;
        }

        return $this->greencheckLogger = new Logger($this->entityManager, $this->redis, $this->logger);
    }
}
