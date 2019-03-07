<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\ArrayInput;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Predis\Client;
use Symfony\Component\HttpKernel\KernelInterface;

/**
 * Clear the cache from the admin part
 */
class GreencheckClearCacheListenerCommand extends Command
{
    /**
     * @var KernelInterface
     */
    private $kernel;
    /**
     * @var Client
     */
    private $predis;

    public function __construct($name = null, KernelInterface $kernel, Client $client)
    {
        parent::__construct($name);
        $this->kernel = $kernel;
        $this->predis = $client;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:clearcache:listener')
            ->setDescription('Listens for clearcache field and then clears the greencheck cache');
    }

    /**
     * Execute the command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Checking cache clear flag');

        $clearcache = $this->predis->get('tgwf_reset_cache');
        if (1 == $clearcache) {
            $output->writeln('Cache clear flag is set, clearing ...');

            $command = $this->getApplication()->find('tgwf:greencheck:clearcache');

            $arguments = [
                'command' => 'tgwf:greencheck:clearcache',
                '--env' => $this->kernel->getEnvironment(),
            ];

            $input = new ArrayInput($arguments);
            $returnCode = $command->run($input, $output);

            $output->writeln('Deleting Cache clear flag ...');
            $this->predis->del('tgwf_reset_cache');
            $output->writeln('Everything done.');
        } else {
            $output->writeln('Flag not set');
        }
    }
}
