<?php

namespace App\Command;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TGWF\Greencheck\Sitecheck;

/**
 * GreencheckTryoutCommand.
 *
 * Try a greencheck without the gearman setup for local testing or verifiying behaviour
 */
class GreencheckTryoutCommand extends Command
{
    protected $checker = null;
    protected $output = null;

    /**
     * @var ParameterBagInterface
     */
    private $params;

    /**
     * @var EntityManagerInterface
     */
    private $entityManager;
    /**
     * @var Sitecheck
     */
    private $sitecheck;

    public function __construct(
        $name = null,
        ParameterBagInterface $params,
        EntityManagerInterface $entityManager,
    Sitecheck $sitecheck
    ) {
        parent::__construct($name);
        $this->params = $params;
        $this->entityManager = $entityManager;
        $this->sitecheck = $sitecheck;
    }

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:tryout')
            ->setDescription('Tryout the greencheck from the cli')
            ->addArgument(
                'url',
                InputArgument::REQUIRED,
                'Site to check?'
            );
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
        $this->sitecheck->disableCache();

        $this->output = $output;

        $time_start = microtime(true);

        $key = 1;
        $url = $input->getArgument('url');
        $ip = '127.0.0.1';
        $browser = 'local';
        $source = 'local';
        $this->output->writeln("Workload: $url");
        $result = ['result' => $this->check($url, $ip, $browser, $source), 'key' => $key];
        $this->output->writeln(json_encode($result));

        $time_end = microtime(true);
        $time = $time_end - $time_start;

        $this->output->writeln("Took $time seconds");
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
    public function check($url, $ip, $browser, $source)
    {
        $valid = $this->sitecheck->validate($url);
        if ($valid === false) {
            return ['error' => 'Invalid url'];
        }

        $sitecheckResult = $this->sitecheck->check($url, $ip, $browser, $source);
        if ($sitecheckResult->isCached()) {
            $this->output->writeln('Result was from cache');
        }

        $result = ['green' => $sitecheckResult->isGreen(),
                        'url' => $sitecheckResult->getCheckedUrl(),
                        'data' => $sitecheckResult->isData(), ];
        if ($sitecheckResult->getIpAddress() === '85.17.167.138') {
            $result['icon'] = 'green';
        }

        if ($sitecheckResult->isHostingProvider()) {
            $hostingProvider = $sitecheckResult->getHostingProvider();
            if ($hostingProvider !== null) {
                $result['hostedby'] = $hostingProvider->getNaam();
                $result['hostedbyid'] = $hostingProvider->getId();
                $result['hostedbywebsite'] = $hostingProvider->getWebsite();
                $result['partner'] = $hostingProvider->getPartner();
                if (!isset($result['icon']) && $hostingProvider->getIcon() !== '') {
                    $result['icon'] = $hostingProvider->getIcon();
                    if (!isset($result['iconurl']) && $hostingProvider->getIconUrl() !== '') {
                        $result['iconurl'] = $hostingProvider->getIconUrl();
                    }
                }
            }
        }
        return $result;
    }
}
