<?php
namespace App\Command;
use Doctrine\ORM\EntityManagerInterface;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TGWF\Greencheck\Sitecheck;
/**
 * GreencheckDataDumpCommand.
 *
 * Dump all redis result keys for domains
 */
class GreencheckDataDumpCommand extends Command
{
    protected $checker = null;
    protected $output = null;
    /**
     * @var ParameterBagInterface
     */
    private $params;
    /**
     * @var Client
     */
    private $client;
    public function __construct(
        $name = null,
        ParameterBagInterface $params,
        Client $client
    ) {
        parent::__construct($name);
        $this->params = $params;
        $this->client = $client;
    }
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:datadump')
            ->setDescription('Make a csv database dump from redis')
            ;
    }
    /**
     * Execute the command.
     *
     * @param InputInterface  $input  Input
     * @param OutputInterface $output Output
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $fp = fopen('tgwf-dump.csv', 'w');
        $first = true;
        $allDomainKeys = $this->client->keys('domains:*');
        // creates a new progress bar (50 units)
        $progressBar = new ProgressBar($output, count($allDomainKeys));
        // starts and displays the progress bar
        $progressBar->start();
        foreach($allDomainKeys as $key) {
            $domain = substr($key,8);
            $data = $this->client->get($key);
            $row = json_decode($data, true);
            if($first) {
                fputcsv($fp, array_keys($row));
                $first = false;
            }
            $progressBar->advance();
            fputcsv($fp, $row);
        }
        fclose($fp);
        $progressBar->finish();
        $output->writeln("");
        $output->writeln('Check tgwf-dump.csv');
    }
}