<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use TGWF\Greencheck\Sitecheck;

/**
 * Clear the as lookups cache.
 */
class GreencheckClearAsLookupsCacheCommand extends Command
{
    /**
     * @var Sitecheck
     */
    private $sitecheck;

    public function __construct(
        $name = null,
        Sitecheck $sitecheck
    ) {
        parent::__construct($name);
        $this->sitecheck = $sitecheck;
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
        $this->sitecheck->disableLog();

        $output->writeln('Clearing greencheck aslookups cache');

        $this->sitecheck->getCache('aslookups')->flushAll();

        $output->writeln('Succesfully cleared greencheck as lookups cache');

        $output->writeln('Everything done');
    }
}
