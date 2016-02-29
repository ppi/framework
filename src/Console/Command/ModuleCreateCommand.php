<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
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
    const TPL_ENGINE_LATTE = 'latte';
    const TPL_ENGINE_PLATES = 'plates';
    const TPL_ENGINE_PHP = 'php';
    const TPL_ENGINE_TWIG = 'twig';
    const TPL_ENGINE_SMARTY = 'smarty';

    const ROUTING_ENGINE_SYMFONY = 'symfony';
    const ROUTING_ENGINE_AURA = 'aura';
    const ROUTING_ENGINE_LARAVEL = 'laravel';
    const ROUTING_ENGINE_FASTROUTE = 'fastroute';

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
        'resources/views/index',
    ];

    /**
     * @var array
     */
    protected $coreFiles = [
        'Module.php',
        'resources/config/config.php',
    ];

    /**
     * @var array
     */
    protected $tplEngineFilesMap = [
        self::TPL_ENGINE_LATTE => [
            'resources/views/index/index.html.latte',
        ],
        self::TPL_ENGINE_PLATES => [
            'resources/views/index/index.html.plates',
        ],
        self::TPL_ENGINE_PHP => [
            'resources/views/index/index.html.php',
        ],
        self::TPL_ENGINE_TWIG => [
            'resources/views/index/base.html.twig',
            'resources/views/index/index.html.twig',
        ],
        self::TPL_ENGINE_SMARTY => [
            'resources/views/index/base.html.smarty',
            'resources/views/index/index.html.smarty',
        ],
    ];

    protected $routingEngineFilesMap = [
        self::ROUTING_ENGINE_SYMFONY => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/symfony.yml',
        ],
        self::ROUTING_ENGINE_AURA => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/aura.php',
        ],
        self::ROUTING_ENGINE_LARAVEL => [
            'src/Controller/Index.php',
            'src/Controller/Shared.php',
            'resources/routes/laravel.php',
        ],
        self::ROUTING_ENGINE_FASTROUTE => [
            'src/Controller/IndexInvoke.php',
            'src/Controller/Shared.php',
            'resources/routes/fastroute.php',
        ],
    ];

    protected $routingEngineTokenMap = [
        self::ROUTING_ENGINE_SYMFONY => [
            '[ROUTING_LOAD_METHOD]' => 'loadSymfonyRoutes',
            '[ROUTING_DEF_FILE]' => 'symfony.yml',
            '[ROUTING_GETROUTES_RETVAL]' => '\Symfony\Component\Routing\RouteCollection',
        ],
        self::ROUTING_ENGINE_AURA => [
            '[ROUTING_LOAD_METHOD]' => 'loadAuraRoutes',
            '[ROUTING_DEF_FILE]' => 'aura.php',
            '[ROUTING_GETROUTES_RETVAL]' => '\Aura\Router\Router',
        ],
        self::ROUTING_ENGINE_LARAVEL => [
            '[ROUTING_LOAD_METHOD]' => 'loadLaravelRoutes',
            '[ROUTING_DEF_FILE]' => 'laravel.php',
            '[ROUTING_GETROUTES_RETVAL]' => '\Illuminate\Routing\Router',
        ],
        self::ROUTING_ENGINE_FASTROUTE => [
            '[ROUTING_LOAD_METHOD]' => 'loadFastRouteRoutes',
            '[ROUTING_DEF_FILE]' => 'fastroute.php',
            '[ROUTING_GETROUTES_RETVAL]' => '\PPI\FastRoute\Wrapper\FastRouteWrapper',
        ],
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
     * @param InputInterface  $input
     * @param OutputInterface $output
     *
     * @throws \Exception
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
        foreach ($this->coreFiles as $coreFile) {
            $tokenizedFiles[] = $coreFile;
        }

        if (!$this->isValidTemplatingEngine($this->tplEngine)) {
            throw new \Exception('Invalid templating engine: ' . $this->tplEngine);
        }

        // TEMPLATING

        // Copy templating files over
        $tplFiles = $this->tplEngineFilesMap[$this->tplEngine];
        $this->copyFiles($this->skeletonModuleDir, $moduleDir, $tplFiles);

        // Setting up templating tokens
        foreach ($tplFiles as $tplFile) {
            $tokenizedFiles[] = $tplFile;
        }

        $tokens['[MODULE_NAME]'] = $moduleName;
        $tokens['[TPL_ENGINE_EXT]'] = $this->tplEngine;

        // ROUTING
        if (!$this->isValidRoutingEngine($this->routingEngine)) {
            throw new \Exception('Invalid routing engine: ' . $this->routingEngine);
        }

        // Copy routing files over
        $routingFiles = $this->routingEngineFilesMap[$this->routingEngine];
        $this->copyFiles($this->skeletonModuleDir, $moduleDir, $routingFiles);

        // Setting up routing tokens
        foreach ($routingFiles as $routingFile) {
            $tokenizedFiles[] = $routingFile;
        }
        $routingTokensMap = $this->routingEngineTokenMap[$this->routingEngine];
        foreach ($routingTokensMap as $routingTokenKey => $routingTokenVal) {
            $tokens[$routingTokenKey] = $routingTokenVal;
        }

        // Replace tokens in all files
        $this->replaceTokensInFiles($moduleDir, $tokenizedFiles, $tokens);

        if ($this->routingEngine === self::ROUTING_ENGINE_FASTROUTE) {
            rename(
                $moduleDir . DIRECTORY_SEPARATOR . $routingFiles[0],
                str_replace('IndexInvoke', 'Index', $moduleDir . DIRECTORY_SEPARATOR . $routingFiles[0]
           ));
        }

        // Success
        $output->writeln("<info>Created module successfully</info>");
        $output->writeln("Name: <info>{$moduleName}</info>");
        $output->writeln(sprintf("Routing: <info>%s</info>", $this->routingEngine));
        $output->writeln(sprintf("Templating: <info>%s</info>", $this->tplEngine));
        $output->writeln(sprintf("Path: <info>%s</info>", $moduleDir));

        $output->writeln("<comment>This module is not enabled. Enable it in <info>config[modules]</info> key</comment>");

        $this->checkTemplatingEngines($input, $output);
        $this->checkRouters($input, $output);
    }

    protected function isValidTemplatingEngine($tplEngine)
    {
        return in_array($tplEngine, [
            self::TPL_ENGINE_LATTE,
            self::TPL_ENGINE_PLATES,
            self::TPL_ENGINE_PHP,
            self::TPL_ENGINE_SMARTY,
            self::TPL_ENGINE_TWIG,
        ]);
    }

    protected function isValidRoutingEngine($routingEngine)
    {
        return in_array($routingEngine, [
            self::ROUTING_ENGINE_SYMFONY,
            self::ROUTING_ENGINE_AURA,
            self::ROUTING_ENGINE_LARAVEL,
            self::ROUTING_ENGINE_FASTROUTE,
        ]);
    }

    /**
     * @param string $moduleDir
     * @param array  $files
     * @param array  $tokens
     */
    protected function replaceTokensInFiles($moduleDir, $files, $tokens)
    {
        foreach ($files as $file) {
            $file = $moduleDir . DIRECTORY_SEPARATOR . $file;
            if (!is_writeable($file)) {
                throw new \InvalidArgumentException(sprintf('File %s is not writeable', $file));
            }
            file_put_contents($file, str_replace(array_keys($tokens), array_values($tokens), file_get_contents($file)));
        }
    }

    /**
     * @param string $skeletonDir
     * @param string $moduleDir
     * @param array  $files
     *
     * @throws \InvalidArgumentException When a file path being created already exists
     */
    protected function copyFiles($skeletonDir, $moduleDir, $files)
    {
        foreach ($files as $file) {
            $srcFile = $skeletonDir . DIRECTORY_SEPARATOR . $file;
            $dstFile = $moduleDir . DIRECTORY_SEPARATOR . $file;
            if (!file_exists($srcFile)) {
                throw new \InvalidArgumentException(sprintf('File does not exist: %s', $srcFile));
            }
            if (file_exists($dstFile)) {
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
        if (is_dir($moduleDir)) {
            throw new \InvalidArgumentException(sprintf('Unable to create module: %s it already exists at %s%s', $moduleName, $moduleDir, $moduleName));
        }

        @mkdir($moduleDir);

        // Create base structure
        foreach ($this->coreDirs as $coreDir) {
            $tmpDir = $moduleDir . DIRECTORY_SEPARATOR . $coreDir;
            @mkdir($tmpDir);
        }
    }

    /**
     * @param InputInterface  $input
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
            $tplQuestion = new ChoiceQuestion('Choose your templating engine [php]', [1 => 'php', 2 => 'twig', 3 => 'smarty', 4 => 'plates', 5 => 'latte'], 'php');
            $tplQuestion->setErrorMessage('Templating engine %s is invalid.');
            $this->tplEngine = $questionHelper->ask($input, $output, $tplQuestion);
        }
        // Routing
        if ($input->getOption('routing') == null) {
            $questionHelper = $this->getHelper('question');
            $routingQuestion = new ChoiceQuestion('Choose your routing engine [symfony]',
                [
                    1 => self::ROUTING_ENGINE_SYMFONY,
                    2 => self::ROUTING_ENGINE_AURA,
                    3 => self::ROUTING_ENGINE_LARAVEL,
                    4 => self::ROUTING_ENGINE_FASTROUTE,
                ],
                'symfony'
            );
            $tplQuestion->setErrorMessage('Routing engine %s is invalid.');
            $this->routingEngine = $questionHelper->ask($input, $output, $routingQuestion);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function checkRouters(InputInterface $input, OutputInterface $output)
    {
        // Aura Check
        if ($this->routingEngine == self::ROUTING_ENGINE_AURA && !class_exists('\Aura\Router\Router')) {
            $output->writeln("<comment>Aura Router doesn't appear to be loaded. Run: <info>composer require ppi/aura-router</info></comment>");
        }

        // Laravel check
        if ($this->routingEngine == self::ROUTING_ENGINE_LARAVEL && !class_exists('\PPI\LaravelRouting\LaravelRouter')) {
            $output->writeln("<comment>Laravel Router doesn't appear to be loaded. Run: <info>composer require ppi/laravel-router</info></comment>");
        }

        if ($this->routingEngine == self::ROUTING_ENGINE_FASTROUTE && !class_exists('\PPI\FastRoute\Wrapper\FastRouteWrapper')) {
            $output->writeln("<comment>FastRoute Router doesn't appear to be loaded. Run: <info>composer require ppi/fast-route</info></comment>");
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function checkTemplatingEngines(InputInterface $input, OutputInterface $output)
    {
        // PHP Templating Engine checks
        if ($this->tplEngine == self::TPL_ENGINE_PHP) {
            if (!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf("<comment>PHP is not an enabled templating engine. Add <info>%s</info> it in <info>config[framework][templating][engines]</info> key</comment>", $this->tplEngine));
            }
        }

        // Twig Checks
        if ($this->tplEngine == self::TPL_ENGINE_TWIG) {
            if (!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf("<comment>Twig is not an enabled templating engine. Add <info>%s</info> it in <info>config[framework][templating][engines]</info> key</comment>", $this->tplEngine));
            }
            if (!class_exists('\Twig_Environment')) {
                $output->writeln("<comment>Twig doesn't appear to be loaded. Run: <info>composer require ppi/twig-module</info></comment>");
            }
        }

        // Smarty Checks
        if ($this->tplEngine == self::TPL_ENGINE_SMARTY) {
            if (!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf("<comment>Smarty is not an enabled templating engine. Add <info>%s</info> it in <info>config[framework][templating][engines]</info> key</comment>", $this->tplEngine));
            }
            if (!class_exists('\Smarty')) {
                $output->writeln("<comment>Smarty doesn't appear to be loaded. Run: <info>composer require ppi/smarty-module</info></comment>");
            }
        }

        // Plates Checks
        if ($this->tplEngine == self::TPL_ENGINE_PLATES) {
            if (!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf("<comment>Plates is not an enabled templating engine. Add <info>%s</info> it in <info>config[framework][templating][engines]</info> key</comment>", $this->tplEngine));
            }
            if (!class_exists('\PPI\PlatesModule\Wrapper\PlatesWrapper')) {
                $output->writeln("<comment>Plates doesn't appear to be loaded. Run: <info>composer require ppi/plates-module</info></comment>");
            }
        }

        // Plates Checks
        if ($this->tplEngine == self::TPL_ENGINE_LATTE) {
            if (!in_array($this->tplEngine, $this->configEnabledTemplatingEngines)) {
                $output->writeln(sprintf("<comment>Latte is not an enabled templating engine. Add <info>%s</info> it in <info>config[framework][templating][engines]</info> key</comment>", $this->tplEngine));
            }
            if (!class_exists('\PPI\LatteModule\Wrapper\LatteWrapper')) {
                $output->writeln("<comment>Latte doesn't appear to be loaded. Run: <info>composer require ppi/latte-module</info></comment>");
            }
        }
    }
}
