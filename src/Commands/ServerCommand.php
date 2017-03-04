<?php
namespace Weeks\Mersey\Commands;

use Illuminate\Support\Collection;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Weeks\Mersey\Mersey;
use Weeks\Mersey\Project;
use Weeks\Mersey\Script;
use Weeks\Mersey\Server;
use Weeks\Mersey\Traits\PassThruTrait;

class ServerCommand extends Command
{

    use PassThruTrait;

    /**
     * @var \Weeks\Mersey\Server
     */
    protected $server;

    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var Mersey
     */
    protected $app;

    /**
     * AddServerCommand constructor.
     *
     * @param Mersey $app
     * @param        $name
     */
    public function __construct(Mersey $app, $name)
    {
        parent::__construct($name);
        $this->app = $app;
    }

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
                'p',
                InputOption::VALUE_NONE,
                'List available projects.'
            )
            ->addOption(
                'scripts',
                's',
                InputOption::VALUE_NONE,
                'List available scripts for a project.'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Skip ping test.'
            );
    }

    /**
     * Run the command.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int|null|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;
        $arguments = $input->getArguments();
        $requestedProjectName = $arguments['project'];

        if ($input->getOption('projects')) {
            $this->showProjects($output);

            return 0;
        }

        if (!$this->app->serverIsAccessible($this->server) && !$input->getOption('force')) {
            $output->writeln(sprintf("<error>%s is unreachable.</error>", $this->server->getDisplayName()));

            return 1;
        }

        if ($requestedProjectName && !$this->checkProjectExists($requestedProjectName)) {

            $output->writeln($this->projectNotFoundError($requestedProjectName));

            return 1;
        }

        $project = $this->server->getProject($requestedProjectName);
        $requestType = $this->getRequestType($arguments);

        if ($input->getOption('scripts')) {
            if (!$requestedProjectName) {
                $output->writeln(sprintf("<error>You must specify a project to view it's scripts.</error>"));

                return 1;
            }

            return $this->showScripts($output, $project, $project->getScripts());
        }

        switch ($requestType) {

            case 'server':
                $output->writeln(sprintf('<info>Connecting to \'%s\'...</info>', $this->server->getDisplayName()));
                $this->passthru($this->server->getCommand());
                break;

            case 'project':

                $message = '<info>Connecting to the %s and going to the project root of \'%s\'</info>';
                $output->writeln(sprintf(
                    $message,
                    $this->server->getDisplayName(),
                    ucwords($project->getName())
                ));

                $command = $this->server->getCommand($project->getRootCommand());

                $this->passthru($command);

                break;

            case 'script':

                $availableScripts = $project->availableScripts();

                $scriptRequested = $arguments['script'];


                if ($this->checkRequestedScriptExists($scriptRequested, $availableScripts)) {
                    $output->writeln($this->scriptNotFoundError($scriptRequested, $requestedProjectName));

                    return 1;
                }

                $requestedScript = $project->getScript($scriptRequested);

                $output->writeln(sprintf('<info>Executing remote script \'%s\'...</info>', $scriptRequested));
                $command = sprintf('cd %s; %s', $project->getRoot(), $requestedScript->getCommand());
                $command = $this->server->getCommand($command);

                $this->passthru($command);

                break;
        }

        return 0;
    }

    /**
     * Set the server related to this command.
     * nb: called when creating the command.
     *
     * @param Server $server
     */
    public function setServer(Server $server)
    {
        $this->server = $server;
    }

    /**
     * Show the projects for this server.
     *
     * @param OutputInterface $output
     */
    protected function showProjects(OutputInterface $output)
    {
        /** @var Collection $projects */
        $projects = $this->server->getProjects();

        if (empty($projects)) {
            $output->writeln('<error>No projects for this server.</error>');

            return;
        }

        $output->writeln(
            sprintf(
                '<comment>Available projects for %s: </comment>',
                $this->server->getDisplayName()
            )
        );

        $table = new Table($output);
        $table
            ->setRows(
                $projects->transform(function (Project $project) {
                    return [
                        sprintf("<info>%s</info>", $project->getName())
                    ];
                })->toArray()
            );
        $table->render();


        $output->writeln(
            sprintf(
                '<comment>example use: php mersey %s <projectname></comment>',
                $this->server->getName()
            )
        );
    }

    /**
     * Render a project not found error.
     *
     * @param $project
     *
     * @return string
     */
    private function projectNotFoundError($project)
    {
        $format = "<error>There is no project named '%s' associated with the %s</error>";

        return sprintf($format, $project, $this->server->getDisplayName());
    }

    /**
     * Render a script not found error.
     *
     * @param $script
     * @param $project
     *
     * @return string
     */
    private function scriptNotFoundError($script, $project)
    {
        $format = "<error>There is no script named '%s' associated with the '%s' project on the %s</error>";

        return sprintf($format, $script, $project, $this->server->getDisplayName());
    }

    /**
     * Check if a given script exists.
     *
     * @param $scriptRequested
     * @param $availableScripts
     *
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
     *
     * @return string
     */
    protected function getRequestType($arguments)
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
     * Check if a project exists based upon it's name.
     *
     * @param $requestedProjectName
     *
     * @return bool
     */
    protected function checkProjectExists($requestedProjectName)
    {
        return !empty($requestedProjectName) && $this->server->hasProject($requestedProjectName);
    }

    /**
     * Output a table of the scripts available for a given project
     *
     * @param OutputInterface $output
     * @param Project         $project
     * @param Collection      $scripts
     *
     * @return int
     */
    private function showScripts(OutputInterface $output, Project $project, Collection $scripts)
    {
        if ($scripts->count() == 0) {
            $output->writeln('<error>No scripts for this project.</error>');

            return 0;
        }
        $output->writeln("<comment>Scripts available for " . $project->getName() . "</comment>");

        $table = new Table($output);
        $table
            ->setHeaders(['Script Name', 'Description'])
            ->setRows(
                collect($scripts)
                    ->transform(function (Script $script) use ($output) {
                        return [
                            "<info>$script->name</info>",
                            ucwords($script->description)
                        ];
                    })->toArray()
            );

        $table->render();

        $output->writeln(
            sprintf(
                "<comment>Example use: mersey %s %s <script name></comment>",
                $this->server->getName(),
                $project->getName()
            )
        );

        return 0;
    }
}