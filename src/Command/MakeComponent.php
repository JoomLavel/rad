<?php
/**
 * Created by PhpStorm.
 * User: Jakub Walczak
 * Date: 14.09.2020
 * Time: 18:50
 */

namespace JoomLavel\Rad\Command;

use JoomLavel\Rad\Service\ComponentGenerator;
use JoomLavel\Rad\Service\ZipGenerator;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;


class MakeComponent extends GenericCommand
{

    const NAME = 'JoomLavel-connect';
    const TITLE = 'Component Creator';
    const STEPS = 8;

    private $steps;
    private $force = false;

    protected static $defaultName = 'make:component';
    protected $componentDirectory;

    protected $publishExec;
    public $componentGenerator;


    /**
     * MakeComponent constructor.
     * @param array $config
     */
    public function __construct(array $config)
    {
        parent::__construct();

        $this->publishExec = $config['rad']['publish']['exec'];
        $this->componentGenerator = new ComponentGenerator($config);
        $this->steps = $this::STEPS;
    }

    /**
     *
     */
    protected function configure()
    {
        $this
            ->addArgument('name', InputArgument::OPTIONAL, 'The name of the new component.')
            ->addOption(
                'zip',
                'z',
                InputOption::VALUE_NONE,
                'Do you want to zip the component for Joomla import?'
            )
            ->addOption(
                'force',
                'f',
                InputOption::VALUE_NONE,
                'Force deletion of previous files and directories.'
            )
            ->setDescription('Creates a new Joomla component based on a JoomLavel-connector template')
            ->setHelp('This command allows you to create a Joomla component. This component can be used to add API, especially JoomLavel API support. Open API 3.0 is also supported.');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->showIntro($input, $output);

        !$input->getOption('zip') ?: $this->steps = $this->steps + 4;
        !$input->getOption('force') ?: $this->force = true;

        $name = $input->getArgument('name') == true ? $input->getArgument('name') : $this::NAME;
        $output->writeln('Create component with name: ' . $name);
        $this->createComponent($name);

        if ($input->getOption('zip') == true && isset($this->componentDirectory)) {
            $this->zipComponent($name);
            $note = " and zipped";
        } else {
            $note = ".";
        }

        $this->showEndNote($output);

        $output->writeln("");
        $output->write('Component ' . $name . ' successfully created' . $note);

        return Command::SUCCESS;
    }

    /**
     * @param string $name
     */
    private function createComponent(string $name)
    {
        $this->addProgressBar($this->steps);

        $this->addCommentAndProgressBar("setComponentName(" . $name . ")");
        $this->componentGenerator->setComponentName($name);

        $this->addCommentAndProgressBar("checkExistingFilesAndDirectories()");
        $checkResult = $this->componentGenerator->checkExistingFilesAndDirectories($this->force);
        $checkResult=='' ?: $this->addWarningAndBreak($checkResult);

        $this->addCommentAndProgressBar("copyTemplateToDirectory()");
        $this->addVerbosityDescription($this->componentGenerator->copyTemplateToDirectory());
        $this->componentDirectory = $this->componentGenerator->getComponentDirectory();

        $this->addCommentAndProgressBar("renameFilesAndDirectories()");
        $this->componentGenerator->renameFilesAndDirectories();

        $this->addCommentAndProgressBar("manipulateXML()");
        $this->componentGenerator->manipulateXml();

        $this->addCommentAndProgressBar("manipulatePhp()");
        $this->componentGenerator->manipulatePhp();

        $this->addCommentAndProgressBar("cleanDirectory(), exec cleanExec");
        $this->componentGenerator->cleanDirectory();

        $this->addCommentAndProgressBar("finish createComponent()");
    }

    /**
     * @param string $name
     */
    private function zipComponent(string $name)
    {
        $this->addCommentAndProgressBar("init ZipGenerator()");
        $zipGenerator = new ZipGenerator();

        $this->addCommentAndProgressBar("zipDirectory()");
        $this->addVerbosityDescription(
            count($zipGenerator->zipDirectory($name, $this->componentDirectory), true)
            . " files zipped");

        $this->addCommentAndProgressBar("move zip to publishPath");
        $this->componentGenerator->moveZipFile($name);

        $this->addCommentAndProgressBar("exec custom publishExec command");
        !$this->publishExec ?: exec($this->publishExec);
    }

}