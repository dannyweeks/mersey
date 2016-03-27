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
use Weeks\Mersey\Server;

class AvailableServersCommand extends Command
{
    /**
     * @var Mersey
     */
    private $app;

    public function __construct(Mersey $app)
    {
        parent::__construct('ping');
        $this->app = $app;
    }

    /**
     * Set up the command
     */
    protected function configure()
    {
        $this
            ->setName('ping')
            ->setDescription('Display availability of all registered servers');
    }

    /**
     * Run the ping test.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $table = new Table($output);
        $table->setHeaders(array('Server', 'Alias', 'Ping'));
        $servers = $this->app->getServers();

        $progress = new ProgressBar($output, $servers->count());
        $output->writeln('Checking server availability');
        $progress->start();

        foreach ($servers as $server) {
            $table->addRow($this->getPingRowData($server));
            $progress->advance();
        }

        $progress->finish();

        $output->writeln('');

        $table->render();
    }

    /**
     * Create row data for the table depending on the ping result.
     *
     * @param Server $server
     * @return array
     */
    private function getPingRowData($server)
    {
        $data = [
            sprintf("<comment>%s</comment>", $server->getDisplayName()),
            sprintf("<comment>%s</comment>", $server->getName())
        ];

        if ($ping = $server->ping()) {
            $data[] = $ping;

            return $data;
        }

        $data[] = '<error>Unavailable</error>';

        return $data;
    }
}


