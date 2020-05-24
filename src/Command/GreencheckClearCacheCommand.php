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
     * @var KernelInterface
     */
    private $kernel;

    /**
     * @var Sitecheck
     */
    private $sitecheck;

    public function __construct(
        $name = null,
        KernelInterface $kernel,
        Sitecheck $sitecheck
    ) {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->sitecheck = $sitecheck;
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
        $this->sitecheck->disableLog();

        $output->writeln('Clearing doctrine cache');

        // Cleanup the doctrine result cache for new entries
        // @todo: Do this automatically if the admin approves or changes stuff in the admin.thegreenwebfoundation.org
        $command = $this->getApplication()->find('doctrine:cache:clear-result');

        $arguments = [
            'command' => 'doctrine:cache:clear-result',
            '--env' => $this->kernel->getEnvironment(),
        ];

        $commandInput = new ArrayInput($arguments);
        $command->run($commandInput, $output);

        //Cleanup all result cache entries in the database
        $output->writeln('Clearing greencheck result cache');
        $cache = $this->sitecheck->getCache('result');
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
