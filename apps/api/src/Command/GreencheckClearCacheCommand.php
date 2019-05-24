<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpKernel\KernelInterface;
use TGWF\Greencheck\Sitecheck;

/**
 * Clear the greencheck cache.
 */
class GreencheckClearCacheCommand extends Command
{
    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var KernelInterface
     */
    private $kernel;

    public function __construct(
        $name = null,
        ParameterBagInterface $params,
        EntityManagerInterface $entityManager,
        KernelInterface $kernel
    ) {
        parent::__construct($name);
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->kernel = $kernel;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:clearcache')
            ->setDescription('Clear the greencheck cache');
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
        $em = $this->entityManager;

        // Setup the cache
        $this->cache = new Sitecheck\Cache($config);
        $this->cache->setCache('default');

        // @todo inject these in constructor
        $this->greencheckUrlRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $this->greencheckIpRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $this->greencheckAsRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $this->greencheckTldRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $this->checker = new Sitecheck($this->greencheckUrlRepository, $this->greencheckIpRepository, $this->greencheckAsRepository, $this->greencheckTldRepository, $this->cache, 'api');

        $output->writeln('Clearing doctrine cache');

        // Cleanup the doctrine result cache for new entries
        // @todo: Do this automatically if the admin approves or changes stuff in the admin.thegreenwebfoundation.org
        $command = $this->getApplication()->find('doctrine:cache:clear-result');

        $arguments = [
            'command' => 'doctrine:cache:clear-result',
            '--env' => $this->kernel->getEnvironment(),
        ];

        $input = new ArrayInput($arguments);
        $returnCode = $command->run($input, $output);

        //Cleanup all result cache entries in the database
        $output->writeln('Clearing greencheck result cache');
        $cache = $this->checker->getCache('result');
        $cache->flushAll();

        /*
         * @todo: Figure out a clean way to cleanup the http cache directory
         * https://github.com/symfony/symfony/pull/6213
         *
         */

        $output->writeln('Succesfully cleared greencheck result cache');

        $output->writeln('Everything done');
    }
}
