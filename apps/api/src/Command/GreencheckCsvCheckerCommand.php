<?php
namespace App\Command;
use Doctrine\ORM\EntityManagerInterface;
use Enqueue\Client\ProducerInterface;
use Enqueue\Client\Message;
use Enqueue\Client\MessagePriority;
use Enqueue\Util\JSON;
use Predis\Client;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use TGWF\Greencheck\Sitecheck;
/**
 * GreencheckCsvCheckerCommand.
 *
 * Grabs all domains from a csv file in the first column and runs them through the queue
 */
class GreencheckCsvCheckerCommand extends Command
{
    /**
     * @var ProducerInterface
     */
    private $producer;
    public function __construct(
        $name = null,
        ProducerInterface $producer
    ) {
        parent::__construct($name);
        $this->producer = $producer;
    }
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('tgwf:greencheck:csvchecker')
            ->setDescription('Checks all domains in a csv file')
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

        if (($handle = fopen("tgwf-check.csv", "r")) !== FALSE) {
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                $position  = $data[0];
                $domainToCheck = $data[1];
                // echo "$position - $domainToCheck\n";

                $message = new Message(JSON::encode(['key' => 0, 'url' => $domainToCheck, 'ip' => '127.0.0.1', 'browser' => 'cli', 'source' => 'cli', 'blind' => true]));

                // We still want incoming requests to take precedence over this
                $message->setPriority(MessagePriority::VERY_LOW);

                $this->producer->sendCommand('greencheck_prio', $message, $needReply = false);
            }
            fclose($handle);
        }
        $output->writeln("");
        $output->writeln('Check tgwf-logger.csv');
    }
}