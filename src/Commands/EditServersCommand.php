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
use Weeks\Mersey\Traits\PassThruTrait;

class EditServersCommand extends Command
{
    use PassThruTrait;
    protected $output;

    /**
     * @var Mersey
     */
    private $app;

    public function __construct(Mersey $app)
    {
        parent::__construct('edit');
        $this->app = $app;
    }

    /**
     * Set up the command
     */
    protected function configure()
    {
        $this
            ->setDescription('Edit the server config file');
    }

    /**
     * Open the config file.
     *
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $output->writeln('<info>Editing servers.json</info>');

        $command = 'open ' . $this->app->getServersConfig(env('APP_ENV'));

        $this->passthru($command);
    }
}


