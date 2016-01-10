<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2016 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 *
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Console;

use PPI\Framework\AppInterface;
use PPI\Framework\Module\AbstractModule;
use Symfony\Component\Console\Application as BaseApplication;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Application.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 * @author      Paul Dragoonis <paul@ppi.io>
 */
class Application extends BaseApplication
{
    /**
     * @var PPI\Framework\AppInterface
     */
    protected $app;

    /**
     * @param AppInterface $app
     */
    public function __construct(AppInterface $app)
    {
        $this->app = $app;
        parent::__construct('PPI', $app->getVersion() . ' - ' . $app->getEnvironment() . ($app->isDebug() ? '/debug' : ''));

        $this->getDefinition()->addOption(new InputOption('--shell', '-s', InputOption::VALUE_NONE, 'Launch the shell.'));
        $this->getDefinition()->addOption(new InputOption('--process-isolation', null, InputOption::VALUE_NONE, 'Launch commands from shell as a separate process.'));
        $this->getDefinition()->addOption(new InputOption('--env', '-e', InputOption::VALUE_REQUIRED, 'The Environment name.', $app->getEnvironment()));
        $this->getDefinition()->addOption(new InputOption('--no-debug', null, InputOption::VALUE_NONE, 'Switches off debug mode.'));
    }

    /**
     * Gets the PPI App associated with this Console.
     *
     * @return AppInterface An AppInterface instance
     */
    public function getApp()
    {
        return $this->app;
    }

    /**
     * Gets the PPI App associated with this Console.
     *
     * @return AppInterface An AppInterface instance
     *
     * @note This method is here to provide compatibility with Symfony's ContainerAwareCommand.
     */
    public function getKernel()
    {
        return $this->app;
    }

    /**
     * Runs the current application.
     *
     * @param InputInterface  $input  An Input instance
     * @param OutputInterface $output An Output instance
     *
     * @return int 0 if everything went fine, or an error code
     */
    public function doRun(InputInterface $input, OutputInterface $output)
    {
        $this->registerCommands();
        if (true === $input->hasParameterOption(array('--shell', '-s'))) {
            $shell = new Shell($this);
            $shell->setProcessIsolation($input->hasParameterOption(array('--process-isolation')));
            $shell->run();

            return 0;
        }

        return parent::doRun($input, $output);
    }

    protected function registerCommands()
    {
        $this->app->boot();

        $config = $this->app->getConfig();

        $commands = array(
            new Command\AssetsInstallCommand(),
            new Command\CacheClearCommand(),
            new Command\ConfigDebugCommand(),
            new Command\ModuleDebugCommand(),
            new Command\RouterDebugCommand(),
            new Command\RouterMatchCommand(),
            new Command\ServiceManagerDebugCommand(),
        );
        if (isset(
            $config['module_listener_options']['module_paths'][0],
            $config['framework']['skeleton_module']['path']
        )) {
            $moduleCreateCommand = new Command\ModuleCreateCommand();
            $moduleCreateCommand->setSkeletonModuleDir($config['framework']['skeleton_module']['path']);
            $moduleCreateCommand->setTargetModuleDir($config['module_listener_options']['module_paths'][0]);

            // Enabled templating engines
            if (isset($config['framework']['templating']['engines'])) {
                $moduleCreateCommand->setEnabledTemplatingEngines($config['framework']['templating']['engines']);
            }

            $commands[] = $moduleCreateCommand;
        }

        $this->addCommands($commands);

        // Commands found in active Modules
        foreach ($this->app->getModules() as $module) {
            if ($module instanceof AbstractModule) {
                $module->registerCommands($this);
            }
        }
    }
}
