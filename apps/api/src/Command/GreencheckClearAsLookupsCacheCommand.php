<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TGWF\Greencheck\Sitecheck;

/**
 * Clear the as lookups cache.
 */
class GreencheckClearAsLookupsCacheCommand extends Command
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;

    public function __construct(
        $name = null,
        ParameterBagInterface $params,
        EntityManagerInterface $entityManager
    ) {
        parent::__construct($name);
        $this->params = $params;
        $this->entityManager = $entityManager;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:clearcache:aslookups')
            ->setDescription('Clear the greencheck cache for aslookups');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $config = $this->params->get('greencheck');

        $sitecheckCache = new Sitecheck\Cache($config);
        $sitecheckCache->setCache('default');

        // @todo inject these in constructor
        $this->greencheckUrlRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $this->greencheckIpRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $this->greencheckAsRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $this->greencheckTldRepository = $this->entityManager->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $siteCheck = new Sitecheck($this->greencheckUrlRepository, $this->greencheckIpRepository, $this->greencheckAsRepository, $this->greencheckTldRepository, $this->cache, new Sitecheck\Logger($this->entityManager), 'api');
        $siteCheck->disableLog();

        $output->writeln('Clearing greencheck aslookups cache');

        $siteCheck->getCache('aslookups')->flushAll();

        $output->writeln('Succesfully cleared greencheck as lookups cache');

        $output->writeln('Everything done');
    }
}
