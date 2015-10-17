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


class EditServersCommand extends Command
{
    /**
     * Set up the command
     */
    protected function configure()
    {
        $this
            ->setName('edit')
            ->setDescription('Edit the server config file. ');
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
        $output->writeln('<info>Editing servers.json</info>');
        passthru('open ' . getenv('MERSEY_SERVER_CONFIG'));
    }
}


