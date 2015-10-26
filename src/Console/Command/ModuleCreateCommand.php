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

/**
 * Module Command.
 *
 * @author      Paul Dragoonis <paul@ppi.io>
 * @author      Vítor Brandão <vitor@ppi.io>
 */
class ModuleCreateCommand extends AbstractCommand
{
    protected $skeletonModuleDir;
    protected $modulesDir;

    public function setSkeletonModuleDir($dir)
    {
        $this->skeletonModuleDir = realpath($dir);
    }

    public function setTargetModuleDir($dir)
    {
        $this->modulesDir = realpath($dir);
    }

    protected function configure()
    {
        $this->setName('module:create')
            ->setDescription('Create a module')
            ->addArgument('name', InputArgument::REQUIRED, 'What is your module name?')
            ->addOption('dir', null, InputOption::VALUE_NONE, 'Specify the modules directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dir  = $this->modulesDir . '/' . $name;

        $this->copyRecursively($this->skeletonModuleDir, $dir);
        file_put_contents($dir . '/Module.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/Module.php')));
        file_put_contents($dir . '/src/Controller/Index.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/src/Controller/Index.php')));
        file_put_contents($dir . '/src/Controller/Shared.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/src/Controller/Shared.php')));
        file_put_contents($dir . '/resources/views/index/index.html.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/resources/views/index/index.html.php')));
        file_put_contents($dir . '/resources/config/routes.yml', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/resources/config/routes.yml')));

        $output->writeln("<info>Created module: {$name}</info>");
        $output->writeln("<comment>To activate it, add <info>'{$name}'</info> to your <info>'active_modules'</info> setting in <info>your app config file.</info></comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dir') === false) {
            $dialog           = $this->getHelper('dialog');
            $this->modulesDir = $dialog->ask($output, 'Where\'s the modules dir? [' . $this->modulesDir . ']: ', $this->modulesDir);
        }
    }

    protected function copyRecursively($src, $dst)
    {
        if (empty($src)) {
            throw new \Exception('Unable to locate source path: ' . $src);
        }

        if (empty($dst)) {
            throw new \Exception('Unable to locate dst path: ' . $dst);
        }

        $dir = opendir($src);
        @mkdir($dst);

        if ($dir === false) {
            throw new \Exception('Unable to open dir: ' . $src);
        }

        while (false !== ($file = readdir($dir))) {
            if (($file != '.') && ($file != '..')) {
                if (is_dir($src . '/' . $file)) {
                    $this->copyRecursively($src . '/' . $file, $dst . '/' . $file);
                } else {
                    copy($src . '/' . $file, $dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}
