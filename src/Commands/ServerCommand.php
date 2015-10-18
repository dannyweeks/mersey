<?php
namespace Weeks\Mersey\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Weeks\Mersey\Server;

class ServerCommand extends Command
{
    /**
     * @var \Weeks\Mersey\Server
     */
    protected $server;

    /**
     * Set standard config of command.
     */
    protected function configure()
    {
        $this
            ->addArgument(
                'project',
                InputArgument::OPTIONAL,
                'Connect to server and go to project root.'
            )
            ->addArgument(
                'script',
                InputArgument::OPTIONAL,
                'Connect to server then project\'s script.'
            )
            ->addOption(
                'projects',
                null,
                InputOption::VALUE_NONE,
                'List available projects.'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('projects')) {
            $this->showProjects($output);

            return;
        }

        if (!$this->server->isAccessible()) {
            $output->writeln(sprintf("<error>%s is unreachable.</error>", $this->server->getDisplayName()));

            return;
        }

        $arguments = $input->getArguments();
        $requestedProjectName = $arguments['project'];

        if ($requestedProjectName && !$this->checkProjectExists($requestedProjectName)) {

            $output->writeln($this->projectNotFoundError($requestedProjectName));

            return;
        }

        $project = $this->server->getProject($requestedProjectName);
        $requestType = $this->getRequestType($arguments);

        switch ($requestType) {

            case 'server':
                $output->writeln(sprintf('<info>Connecting to \'%s\'...</info>', $this->server->getDisplayName()));
                $this->server->connect();
                break;

            case 'project':

                $message = '<info>Connecting to the %s and going to the project root of \'%s\'</info>';
                $output->writeln(sprintf(
                    $message,
                    $this->server->getDisplayName(),
                    ucwords($project->getName())
                ));

                $this->server->connect($project->getRootCommand());

                break;

            case 'script':


                $availableScripts = $project->availableScripts();

                $scriptRequested = $arguments['script'];

                if ($this->checkRequestedScriptExists($scriptRequested, $availableScripts)) {
                    $output->writeln($this->scriptNotFoundError($scriptRequested, $requestedProjectName));

                    return;
                }

                $output->writeln(sprintf('<info>Executing remote script \'%s\'...</info>', $scriptRequested));
                $this->server->connect($project->getScript($scriptRequested));

                break;
        }

    }

    /**
     * @param Server $server
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @param OutputInterface $output
     */
    protected function showProjects(OutputInterface $output)
    {
        $projects = $this->server->getProjects();

        if (!empty($projects)) {

            $output->writeln('Available projects for ' . $this->server->getDisplayName() . ': ');

            foreach ($projects as $project) {

                $output->writeln('    <info>' . $project->getName() . '</info>');
            }

            $output->writeln('example use: php mersey ' . $this->server->getName() . ' <projectname>');

            return;
        }

        $output->writeln('No projects for this server.');
    }

    /**
     * @param $project
     * @return string
     */
    private function projectNotFoundError($project)
    {
        $format = "<error>There is no project named '%s' associated with the %s</error>";

        return sprintf($format, $project, $this->server->getDisplayName());
    }

    /**
     * @param $script
     * @param $project
     * @return string
     */
    private function scriptNotFoundError($script, $project)
    {
        $format = "<error>There is no script named '%s' associated with the '%s' project on the %s</error>";

        return sprintf($format, $script, $project, $this->server->getDisplayName());
    }

    /**
     * @param $scriptRequested
     * @param $availableScripts
     * @return bool
     */
    protected function checkRequestedScriptExists($scriptRequested, $availableScripts)
    {
        return !empty($scriptRequested) && !in_array($scriptRequested, $availableScripts);
    }

    /**
     * Find the intent of the users request.
     *
     * @param $arguments
     * @return string
     */
    private function getRequestType($arguments)
    {
        if (!empty($arguments['script'])) {
            return 'script';
        }

        if (!empty($arguments['project'])) {
            return 'project';
        }

        return 'server';
    }

    /**
     * @param $requestedProjectName
     * @return bool
     */
    protected function checkProjectExists($requestedProjectName)
    {
        return !empty($requestedProjectName) && $this->server->hasProject($requestedProjectName);
    }
}