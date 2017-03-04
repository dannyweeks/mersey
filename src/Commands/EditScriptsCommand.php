<?php

namespace Weeks\Mersey\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Weeks\Mersey\Mersey;
use Weeks\Mersey\Traits\PassThruTrait;

class EditScriptsCommand extends Command
{
    use PassThruTrait;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Mersey
     */
    private $app;

    public function __construct(Mersey $app)
    {
        parent::__construct('scripts');
        $this->app = $app;
    }

    /**
     * Set up the command
     */
    protected function configure()
    {
        $this
            ->setDescription('Edit the global scripts file');
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
        $output->writeln('<info>Editing scripts.json</info>');

        $command = 'open ' . $this->app->getScriptsConfig(env('APP_ENV'));

        $this->passthru($command);
    }
}


