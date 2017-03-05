<?php


namespace Weeks\Mersey;


use Symfony\Component\Console\Application;
use Symfony\Component\Console\Exception\CommandNotFoundException;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputDefinition;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;

class Console extends Application
{
    /**
     * For improved UX, try to interact with server without the need for 'server:' prefix first.
     *
     * @param InputInterface $input
     *
     * @return string
     */
    protected function getCommandName(InputInterface $input)
    {
        try {
            return $this->find('server:' . $input->getFirstArgument())
                ->getName();
        } catch (CommandNotFoundException $e) {
            return parent::getCommandName($input);
        }
    }

    /**
     * Gets the default input definition.
     *
     * @return InputDefinition An InputDefinition instance
     */
    protected function getDefaultInputDefinition()
    {
        return new InputDefinition(array(
            new InputArgument('command', InputArgument::REQUIRED, 'The command to execute'),
            new InputOption('--help', '-h', InputOption::VALUE_NONE, 'Display this help message'),
            new InputOption('--quiet', '-q', InputOption::VALUE_NONE, 'Do not output any message'),
            new InputOption('--verbose', '-v|vv|vvv', InputOption::VALUE_NONE,
                'Increase the verbosity of messages: 1 for normal output, 2 for more verbose output and 3 for debug'),
            new InputOption('--version', '-V', InputOption::VALUE_NONE, 'Display this application version'),
//            new InputOption('--ansi', '', InputOption::VALUE_NONE, 'Force ANSI output'),
//            new InputOption('--no-ansi', '', InputOption::VALUE_NONE, 'Disable ANSI output'),
//            new InputOption('--no-interaction', '-n', InputOption::VALUE_NONE, 'Do not ask any interactive question'),
        ));
    }
}