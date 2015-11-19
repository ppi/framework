<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\ChoiceQuestion;

/**
 * Module Command.
 *
 * @author      Paul Dragoonis <paul@ppi.io>
 * @author      Vítor Brandão <vitor@ppi.io>
 */
class ModuleCreateCommand extends AbstractCommand
{

    const TPL_ENGINE_PHP = 'php';
    const TPL_ENGINE_TWIG = 'twig';
    const TPL_ENGINE_SMARTY = 'smarty';

    const ROUTING_ENGINE_SYMFONY = 'symfony';
    const ROUTING_ENGINE_AURA = 'aura';
    const ROUTING_ENGINE_LARAVEL = 'laravel';

    protected $skeletonModuleDir;
    protected $modulesDir;
    protected $tplEngine;
    protected $routingEngine;
    protected $configEnabledTemplatingEngines = [];

    /**
     * @var array
     */
    protected $coreDirs = [
        'src',
        'src/Controller',
        'tests',
        'resources',
        'resources/routes',
        'resources/config',
        'resources/views',
        'resources/views/index'
    ];

    /**
     * @var array
     */
    protected $coreFiles = [
        'Module.php',
        'resources/config/config.php'
    ];

    /**
     * @var array
     */
    protected $tplEngineFilesMap = [
        'php' => [
            'resources/views/index/index.html.php'
        ],
        'twig' => [
            'resources/views/index/base.html.twig',
            'resources/views/index/index.html.twig'
        ],
        'smarty' => [
            'resources/views/index/base.html.smarty',
            'resources/views/index/index.html.smarty'
        ]
    ];

    protected $routingEngineFilesMap = [
        'symfony' => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/symfony.yml'
        ],
        'aura' => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/aura.php'
        ],
        'laravel' => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/laravel.php'
        ]
    ];

    protected $routingEngineTokenMap = [
        'symfony' => [
            '[ROUTING_LOAD_METHOD]' => 'loadSymfonyRoutes',
            '[ROUTING_DEF_FILE]' => 'symfony.yml',
            '[ROUTING_GETROUTES_RETVAL]' => '\Symfony\Component\Routing\RouteCollection'
        ],
        'aura' => [
            '[ROUTING_LOAD_METHOD]' => 'loadAuraRoutes',
            '[ROUTING_DEF_FILE]' => 'aura.php',
            '[ROUTING_GETROUTES_RETVAL]' => '\Aura\Router\Router'
        ],
        'laravel' => [
            '[ROUTING_LOAD_METHOD]' => 'loadLaravelRoutes',
            '[ROUTING_DEF_FILE]' => 'laravel.php',
            '[ROUTING_GETROUTES_RETVAL]' => '\Illuminate\Routing\Router'
        ]
    ];

    /**
     * @param string $moduleDir
     */
    public function setSkeletonModuleDir($moduleDir)
    {
        $this->skeletonModuleDir = realpath($moduleDir);
    }

    /**
     * @param string $moduleDir
     */
    public function setTargetModuleDir($moduleDir)
    {
        $this->modulesDir = realpath($moduleDir);
    }

    /**
     * @param array $tplEngines
     */
    public function setEnabledTemplatingEngines(array $tplEngines)
    {
        $this->configEnabledTemplatingEngines = $tplEngines;
    }


    /**
     * @return void
     */
    protected function configure()
    {
        $this->setName('module:create')
            ->setDescription('Create a module')
            ->addArgument('name', InputArgument::REQUIRED, 'What is your module name?')
            ->addOption('dir', null, InputOption::VALUE_OPTIONAL, 'Specify the modules directory')
            ->addOption('tpl', null, InputOption::VALUE_OPTIONAL, 'Specify the templating engine')
            ->addOption('routing', null, InputOption::VALUE_OPTIONAL, 'Specify the routing engine');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     *
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $moduleName = $input->getArgument('name');
        $moduleDir = $this->modulesDir . DIRECTORY_SEPARATOR . $moduleName;

        // Acquire Module Information
        $this->askQuestions($input, $output);
        $this->createModuleStructure($moduleDir, $moduleName);
        // Copy the core files
        $this->copyFiles($this->skeletonModuleDir, $moduleDir, $this->coreFiles);

        $tokenizedFiles = [];
        $tokens = [];
        foreach($this->coreFiles as $coreFile) {
            $tokenizedFiles[] = $coreFile;
        }

        // Copy files relative to the selected templating engine
        switch($this->tplEngine) {
            case self::TPL_ENGINE_PHP:
            case self::TPL_ENGINE_TWIG:
            case self::TPL_ENGINE_SMARTY:
                // Copy templating files over
                $tplFiles = $this->tplEngineFilesMap[$this->tplEngine];
                $this->copyFiles($this->skeletonModuleDir, $moduleDir, $tplFiles);
                // Setting up templating tokens
                foreach($tplFiles as $tplFile) {
                    $tokenizedFiles[] = $tplFile;
                }
                $tokens['[MODULE_NAME]'] = $moduleName;
                $tokens['[TPL_ENGINE_EXT]'] = $this->tplEngine;
                break;
        }
        // Routing
        switch($this->routingEngine) {
            case self::ROUTING_ENGINE_SYMFONY:
            case self::ROUTING_ENGINE_AURA:
            case self::ROUTING_ENGINE_LARAVEL:
                // Copy routing files over
                $routingFiles = $this->routingEngineFilesMap[$this->routingEngine];
                $this->copyFiles($this->skeletonModuleDir, $moduleDir, $routingFiles);

                // Setting up routing tokens
                foreach($routingFiles as $routingFile) {
                    $tokenizedFiles[] = $routingFile;
                }
                $routingTokensMap = $this->routingEngineTokenMap[$this->routingEngine];
                foreach($routingTokensMap as $routingTokenKey => $routingTokenVal) {
                    $tokens[$routingTokenKey] = $routingTokenVal;
                }
                break;
        }

        // Replace tokens in all files
        $this->replaceTokensInFiles($moduleDir, $tokenizedFiles, $tokens);

        // @todo - maybe test some stuff, to verify?

        // Success
        $output->writeln("<info>Created module successfully</info>");
        $output->writeln("Name: <info>{$moduleName}</info>");
        $output->writeln(sprintf("Routing: <info>%s</info>", $this->routingEngine));
        $output->writeln(sprintf("Templating: <info>%s</info>", $this->tplEngine));
        $output->writeln(sprintf("Path: <info>%s</info>", $moduleDir));

        $output->writeln("<comment>This module is not enabled. Enable it in <info>config[modules]</info> key</comment>");

        if($this->tplEngine == self::TPL_ENGINE_TWIG) {
            if(!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf(
                    "<comment>This templating engine is not enabled. Add <info>%s</info> it in config[framework][templating][engines] key</comment>",
                    $this->tplEngine
                ));
            }
            if(!class_exists('\Twig_Environment')) {
                $output->writeln("Twig doesn't appear to be loaded. Run: <info>composer require ppi/twig-module</info>");
            }
        }

        if($this->tplEngine == self::TPL_ENGINE_SMARTY) {
            if(!class_exists('\Smarty')) {
                $output->writeln("Smarty doesn't appear to be loaded. Run: <info>composer require ppi/smarty-module</info>");
            }
        }

    }

    /**
     * @param string $moduleDir
     * @param array $files
     * @param array $tokens
     *
     * @return void
     */
    protected function replaceTokensInFiles($moduleDir, $files, $tokens)
    {
        foreach($files as $file) {
            $file = $moduleDir . DIRECTORY_SEPARATOR . $file;
            if(!is_writeable($file)) {
                throw new \InvalidArgumentException(sprintf('File %s is not writeable', $file));
            }
            file_put_contents(
                $file,
                str_replace(
                    array_keys($tokens),
                    array_values($tokens),
                    file_get_contents($file)
                )
            );
        }
    }

    /**
     * @param string $skeletonDir
     * @param string $moduleDir
     * @param array $files
     *
     * @throws \InvalidArgumentException When a file path being created already exists
     */
    protected function copyFiles($skeletonDir, $moduleDir, $files)
    {
        foreach($files as $file) {
            $srcFile = $skeletonDir . DIRECTORY_SEPARATOR . $file;
            $dstFile = $moduleDir . DIRECTORY_SEPARATOR . $file;
            if(!file_exists($srcFile)) {
                throw new \InvalidArgumentException(sprintf('File does not exist: %s', $srcFile));
            }
            if(file_exists($dstFile)) {
                throw new \InvalidArgumentException(sprintf('File already exists: %s', $dstFile));
            }
            copy($srcFile, $dstFile);
        }
    }

    /**
     * @param string $moduleDir
     * @param string $moduleName
     *
     * @throws \InvalidArgumentException When a dir path being created already exists
     */
    protected function createModuleStructure($moduleDir, $moduleName)
    {
        if(is_dir($moduleDir)) {
            throw new \InvalidArgumentException(sprintf(
                'Unable to create module: %s it already exists at %s%s',
                $moduleName, $moduleDir, $moduleName
            ));
        }

        @mkdir($moduleDir);

        // Create base structure
        foreach($this->coreDirs as $coreDir) {
            $tmpDir = $moduleDir . DIRECTORY_SEPARATOR . $coreDir;
            @mkdir($tmpDir);
        }
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     */
    protected function askQuestions(InputInterface $input, OutputInterface $output)
    {
        // Module DIR
        if ($input->getOption('dir') == null) {
            $questionHelper = $this->getHelper('question');
            $modulesDirQuestion = new ChoiceQuestion('Where is the modules dir?', [1 => $this->modulesDir], $this->modulesDir);
            $modulesDirQuestion->setErrorMessage('Modules dir: %s is invalid.');
            $this->modulesDir = $questionHelper->ask($input, $output, $modulesDirQuestion);
        }

        // Templating
        if ($input->getOption('tpl') == null) {
            $questionHelper = $this->getHelper('question');
            $tplQuestion = new ChoiceQuestion('Choose your templating engine [php]', [
                1 => 'php',
                2 => 'twig',
                3 => 'smarty'
            ], 'php');
            $tplQuestion->setErrorMessage('Templating engine %s is invalid.');
            $this->tplEngine = $questionHelper->ask($input, $output, $tplQuestion);
        }
        // Routing
        if ($input->getOption('routing') == null) {
            $questionHelper = $this->getHelper('question');
            $routingQuestion = new ChoiceQuestion('Choose your routing engine [symfony]', [
                1 => 'symfony',
                2 => 'aura',
                3 => 'laravel'
            ], 'symfony');
            $tplQuestion->setErrorMessage('Routing engine %s is invalid.');
            $this->routingEngine = $questionHelper->ask($input, $output, $routingQuestion);
        }
    }
}
