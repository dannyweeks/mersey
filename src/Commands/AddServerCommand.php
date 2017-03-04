<?php

namespace Weeks\Mersey\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\QuestionHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ConfirmationQuestion;
use Symfony\Component\Console\Question\Question;
use Weeks\Mersey\Mersey;

class AddServerCommand extends Command
{
    /**
     * @var OutputInterface
     */
    protected $output;

    /**
     * @var InputInterface
     */
    protected $input;

    /**
     * @var QuestionHelper
     */
    protected $questionHelper;

    /**
     * @var string
     */
    protected $configFile;

    /**
     * @var Mersey
     */
    protected $app;

    /**
     * AddServerCommand constructor.
     *
     * @param Mersey $app
     */
    public function __construct(Mersey $app)
    {
        parent::__construct('add');
        $this->app = $app;
    }

    /**
     * Set up the command
     */
    protected function configure()
    {
        $this->setDescription('Add a server to the config file');
    }

    /**
     * Create new server.
     *
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->questionHelper = $this->getHelper('question');
        $this->output = $output;
        $this->input = $input;

        $this->configFile = $this->app->getServersConfig(env('APP_ENV'));

        $config = collect($this->app->loadServerConfig(env('APP_ENV')));

        $usedNames = $config->pluck('name');

        $required = function ($answer) {

            if (empty(trim($answer))) {
                throw new \Exception('You must define something.');
            }

            return $answer;
        };

        $serverDetails = [];
        $serverDetails['name'] = strtolower($this->askQuestion('Server name/alias (what you will type into the command line)',
            null, $required));

        if (in_array($serverDetails['name'], $usedNames->toArray())) {
            $question = sprintf("'%s' is already defined. Would you like to overwrite it? (y/N)",
                $serverDetails['name']);

            if (!$this->askConfirmQuestion($question, 'error')) {
                $output->writeln('Aborted server definition.');

                return 0;
            }

            $config = $config->reject(function ($item) use ($serverDetails) {
                return $item['name'] == $serverDetails['name'];
            });
        }

        $defaultDisplayName = ucfirst($serverDetails['name']) . ' Server';
        $question = "Server display name (default: $defaultDisplayName)";
        $serverDetails['displayName'] = $this->askQuestion($question, $defaultDisplayName);

        $serverDetails['username'] = $this->askQuestion('SSH username', null, $required);
        $serverDetails['hostname'] = $this->askQuestion('Hostname (IP address or Domain name)', null, $required);

        if ($this->askConfirmQuestion('Required information defined. Add some optional settings? (y/N)')) {
            $serverDetails['sshKey'] = $this->askQuestion('SSH key location (default: ~/.ssh/id_rsa)');
            $serverDetails['port'] = (int)$this->askQuestion('SSH port (default: 22)');
        }

        $addProjects = $this->askConfirmQuestion('Define a project? (y/N)');

        while ($addProjects) {
            $project = [];
            $project['name'] = $this->askQuestion('Project name');
            $project['root'] = $this->askQuestion('Path to project root?');

            $output->writeln(sprintf('<comment>The project \'%s\' has been defined.</comment>', $project['name']));

            $addScripts = $this->askConfirmQuestion(sprintf('Add some scripts to the project? (y/N)'));

            while ($addScripts) {

                $script = [];
                $script['name'] = $this->askQuestion('Script name?', null, $required);
                $description = ucfirst($script['name']) . ' script.';
                $question = sprintf('Script description (default: %s)', $description);
                $script['description'] = $this->askQuestion($question, $description);
                $script['command'] = $this->askQuestion('Script command?', null, $required);

                $output->writeln('<comment>Script defined.</comment>');

                $project['scripts'][] = $script;

                $addScripts = $this->askConfirmQuestion(sprintf('Add another script? (y/N)'));
            }

            $serverDetails['projects'][] = $project;
            $addProjects = $this->askConfirmQuestion('Define another project? (y/N)');
        }

        $config->push(array_filter($serverDetails));

        $this->app->updateConfig($this->configFile, $config->toArray());

        $output->writeln('<comment>Server created. You can access it by running:</comment> mersey ' . $serverDetails['name']);

        return 0;
    }

    /**
     * Helper method to ask a question
     *
     * @param string        $question
     * @param null          $default
     * @param callable|null $validator
     * @param string        $tag
     *
     * @return string
     */
    protected function askQuestion(
        $question,
        $default = null,
        callable $validator = null,
        $tag = 'info'
    ) {
        $question = new Question("<$tag>$question:</$tag> ", $default);

        $question->setValidator($validator);

        return $this->questionHelper->ask($this->input, $this->output, $question);
    }

    /**
     * Helper method to ask a confirmation question
     *
     * @param string $question
     * @param string $tag
     *
     * @return string
     */
    protected function askConfirmQuestion($question, $tag = 'question')
    {
        $question = new ConfirmationQuestion("<$tag>$question:</$tag> ");

        return $this->questionHelper->ask($this->input, $this->output, $question);
    }
}


