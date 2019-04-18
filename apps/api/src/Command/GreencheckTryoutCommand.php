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
        $config = $this->params->get('greencheck');
        $em = $this->entityManager;

        // Setup the cache
        $this->cache = new Sitecheck\Cache($config);
        $this->cache->setCache('default');
        $this->cache->disableCache();

        // @todo inject these in constructor
        $this->greencheckUrlRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckUrl");
        $this->greencheckIpRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckIp");
        $this->greencheckAsRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckAs");
        $this->greencheckTldRepository = $em->getRepository("TGWF\Greencheck\Entity\GreencheckTld");

        $this->checker = new Sitecheck($this->greencheckUrlRepository, $this->greencheckIpRepository, $this->greencheckAsRepository, $this->greencheckTldRepository, $this->cache, new Sitecheck\Logger($this->entityManager), 'api');
        $this->checker->disableLog();

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
     * @return [type] [description]
     */
    public function check($url, $ip, $browser, $source)
    {
        $checker = $this->checker;
        $valid = $checker->validate($url);
        if ($valid) {
            $resultobject = $checker->check($url, $ip, $browser, $source);
            if ($resultobject->isCached()) {
                $this->output->writeln('Result was from cache');
            }

            $result = ['green' => $resultobject->isGreen(),
                            'url' => $resultobject->getCheckedUrl(),
                            'data' => $resultobject->isData(), ];
            if ('85.17.167.138' == $resultobject->getIpAddress()) {
                $result['icon'] = 'green';
            }
            if ($resultobject->isHostingProvider()) {
                $hp = $resultobject->getHostingProvider();
                if (!is_null($hp)) {
                    $result['hostedby'] = $hp->getNaam();
                    $result['hostedbyid'] = $hp->getId();
                    $result['hostedbywebsite'] = $hp->getWebsite();
                    $result['partner'] = $hp->getPartner();
                    if ('' != $hp->getIcon() && !isset($result['icon'])) {
                        $result['icon'] = $hp->getIcon();
                        if ('' != $hp->getIconUrl() && !isset($result['iconurl'])) {
                            $result['iconurl'] = $hp->getIconUrl();
                        }
                    }
                }
            }
        } else {
            $result = ['error' => 'Invalid url'];
        }

        return $result;
    }
}
