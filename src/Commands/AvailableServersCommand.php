<?php


namespace Weeks\Mersey\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Weeks\Mersey\Mersey;


class AvailableServersCommand extends Command
{

    /**
     * @var Mersey
     */
    private $app;

    public function __construct($name = null, Mersey $app)
    {
        parent::__construct($name);
        $this->app = $app;
    }

    protected function configure()
    {
        $this
            ->setName('ping')
            ->setDescription('Display availability of all registered servers. ');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {


        $table = new Table($output);
        $table->setHeaders(array('Server', 'Ping'));
        $servers = $this->app->getServers();

        $progress = new ProgressBar($output, $servers->count());
        $output->writeln('Checking server availability');
        $progress->start();

        foreach($servers as $server)
        {
            $messageFormat = "<comment>%s</comment>";
            if($ping = $server->ping())
            {
                $table->addRow([
                    sprintf($messageFormat, $server->getDisplayName()),
                    $ping
                ]);
            }
            else {
                $table->addRow([
                    sprintf($messageFormat, $server->getDisplayName()),
                    '<error>Unavailable </error>'
                ]);
            }

            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');

        $table->render();
    }
}


