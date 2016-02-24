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
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
    protected $moduleDir;
    protected $moduleName;
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
        'resources/config'
    ];

    /**
     * @var array
     */
    protected $coreFiles = [
        'Module.php',
        'resources/config/config.php',
    ];

    protected $tplEngineCoreFiles = [
        'resources/views',
        'resources/views/index'
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

    protected $routingEngineCoreFiles = [
            'resources/routes'
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
     * @param InputInterface $input
     * @param OutputInterface $output
     * @throws \Exception
     * @return void
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->moduleName = $input->getArgument('name');
        $this->moduleDir = $this->modulesDir . DIRECTORY_SEPARATOR . $this->moduleName;

        // Acquire Module Information
        $this->askQuestions($input, $output);
        $this->createModuleStructure($this->moduleDir, $this->moduleName);
        $output->writeln("<info>Created module successfully</info>");
        $output->writeln("Name: <info>{$this->moduleName}</info>");
        $output->writeln(sprintf("Path: <info>%s</info>", $this->moduleDir));

        // Copy the core files
        $this->copyFiles($this->skeletonModuleDir, $this->moduleDir, $this->coreFiles);

        $tokenizedFiles = [];
        $tokens = [];
        foreach ($this->coreFiles as $coreFile) {
            $tokenizedFiles[] = $coreFile;
        }

        if(null !== $this->tplEngine && $this->isValidTemplatingEngine($this->tplEngine)) {
            $this->copyTemplatingFiles($this->moduleDir, $this->moduleName, $tokens, $tokenizedFiles);
            $output->writeln(sprintf("Templating: <info>%s</info>", $this->tplEngine));
        }

        if(null !== $this->routingEngine && $this->isValidRoutingEngine($this->routingEngine)) {
            $this->copyRoutingFiles($this->moduleDir, $this->moduleName, $tokens, $tokenizedFiles);
            $output->writeln(sprintf("Routing: <info>%s</info>", $this->routingEngine));
        }
        $output->writeln("<comment>This module is not enabled. Enable it in <info>config[modules]</info> key</comment>");

        $this->checkEnabledTemplatingEngines($input, $output);
        $this->checkEnabledRouters($input, $output);
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
        $questionHelper = $this->getHelper('question');

        // Module DIR
        if ($input->getOption('dir') == null) {
            $modulesDirQuestion = new ChoiceQuestion('Where is the modules dir?', [1 => $this->modulesDir], $this->modulesDir);
            $modulesDirQuestion->setErrorMessage('Modules dir: %s is invalid.');
            $this->modulesDir = $questionHelper->ask($input, $output, $modulesDirQuestion);
        }

        if($this->askForTemplating($input, $output)) {
            $this->chooseTemplatingEngine($input, $output);
        }

        if($this->askForRouting($input, $output)) {
            $this->chooseRouter($input, $output);
        }
    }

    /**
     * @param InputInterface  $input
     * @param OutputInterface $output
     */
    private function checkEnabledRouters(InputInterface $input, OutputInterface $output)
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
    private function checkEnabledTemplatingEngines(InputInterface $input, OutputInterface $output)
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

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return boolean
     */
    private function askForTemplating(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Do you need templates? (yes/no):\n", false);

        return $questionHelper->ask($input, $output, $question);
    }

    private function askForRouting(InputInterface $input, OutputInterface $output)
    {
        $questionHelper = $this->getHelper('question');
        $question = new ConfirmationQuestion("Do you need routing? (yes/no):\n", false);

        return $questionHelper->ask($input, $output, $question);
    }

    private function chooseTemplatingEngine($input, $output)
    {
        $tplQuestion = new ChoiceQuestion('Choose your templating engine [php]',
            [
                1 => 'php',
                2 => 'twig',
                3 => 'smarty',
                4 => 'plates',
                5 => 'latte',
                99 => 'skip'
            ]
        );
        $tplQuestion->setErrorMessage('Templating engine %s is invalid.');
        if(99 !== ($tplEngine = $this->getHelper('question')->ask($input, $output, $tplQuestion))) {
            $this->tplEngine = $tplEngine;
        }
    }

    private function chooseRouter(InputInterface $input, OutputInterface $output)
    {
        $routingQuestion = new ChoiceQuestion('Choose your routing engine:',
            [
                1 => self::ROUTING_ENGINE_SYMFONY,
                2 => self::ROUTING_ENGINE_AURA,
                3 => self::ROUTING_ENGINE_LARAVEL,
                4 => self::ROUTING_ENGINE_FASTROUTE,
                99 => 'skip'
            ]
        );

        // @todo - test question when you don't choose any option, or an invalid one (like -1)
        $routingQuestion->setErrorMessage('Routing engine %s is invalid.');
        if(99 !== ($routingEngine = $this->getHelper('question')->ask($input, $output, $routingQuestion))) {
            $this->routingEngine = $routingEngine;
        }
    }

    private function copyTemplatingFiles($moduleDir, $moduleName, $tokens, $tokenizedFiles)
    {
        $tplFiles = $this->tplEngineFilesMap[$this->tplEngine];

        // Copy core templating files over
        foreach($this->tplEngineCoreFiles as $coreFile) {
            $tplFiles[] = $coreFile;
        }

        // Copy templating files over relevant to the specified engine
        $this->copyFiles($this->skeletonModuleDir, $moduleDir, $tplFiles);

        // Setting up templating tokens
        foreach ($tplFiles as $tplFile) {
            $tokenizedFiles[] = $tplFile;
        }

        $tokens['[MODULE_NAME]'] = $moduleName;
        $tokens['[TPL_ENGINE_EXT]'] = $this->tplEngine;
    }

    private function copyRoutingFiles($moduleDir, $moduleDir, $tokens, $tokenizedFiles)
    {
        // Copy routing files over
        $routingFiles = $this->routingEngineFilesMap[$this->routingEngine];

        // Copy core templating files over
        foreach($this->routingEngineCoreFiles as $coreFile) {
            $routingFiles[] = $coreFile;
        }

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

        // Prepare the fastroute route file
        if ($this->routingEngine === self::ROUTING_ENGINE_FASTROUTE) {
            rename(
                $moduleDir . DIRECTORY_SEPARATOR . $routingFiles[0],
                str_replace('IndexInvoke', 'Index', $moduleDir . DIRECTORY_SEPARATOR . $routingFiles[0]
                ));
        }
    }

}
