<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2012 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Generates modules.
 *
 * @author Paul Dragoonis <paul@ppi.io>
 */
class ModuleCommand extends Command
{
    protected $modulesDir;

    protected function configure()
    {
        $this->modulesDir = realpath('./modules');

        $this->setName('module:create')
             ->setDescription('Create a module')
             ->addArgument('name', InputArgument::REQUIRED, 'What is your module name?')
             ->addOption('dir', null, InputOption::VALUE_NONE, 'Specify the modules directory');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $name = $input->getArgument('name');
        $dir  = $this->modulesDir . '/' . $name;

        $this->copyRecursively('./app/skeleton', $dir);
        file_put_contents($dir . '/Module.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/Module.php')));
        file_put_contents($dir . '/Controller/Index.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/Controller/Index.php')));
        file_put_contents($dir . '/Controller/Shared.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/Controller/Shared.php')));
        file_put_contents($dir . '/resources/views/index/index.html.php', str_replace('[MODULE_NAME]', $name, file_get_contents($dir . '/resources/views/index/index.html.php')));
        file_put_contents($dir . '/resources/config/routes.yml', str_replace('[MODULE_NAME]', strtolower($name), file_get_contents($dir . '/resources/config/routes.yml')));

        $output->writeln("<info>Created module: {$name}</info>");
        $output->writeln("<comment>To activate it, add <info>'{$name}'</info> to your <info>'activeModules'</info> setting in <info>modules.config.php</info></comment>");
    }

    protected function interact(InputInterface $input, OutputInterface $output)
    {
        if ($input->getOption('dir') === false) {
            $dialog = $this->getHelper('dialog');
            $this->modulesDir = $dialog->ask($output, 'Where\'s the modules dir? [' . $this->modulesDir . ']: ', $this->modulesDir);
        }
    }

    protected function copyRecursively($src, $dst)
    {
        $dir = opendir($src);
        @mkdir($dst);
        while (false !== ( $file = readdir($dir)) ) {
            if (( $file != '.' ) && ( $file != '..' )) {
                if ( is_dir($src . '/' . $file) ) {
                    $this->copyRecursively($src . '/' . $file,$dst . '/' . $file);
                } else {
                    copy($src . '/' . $file,$dst . '/' . $file);
                }
            }
        }
        closedir($dir);
    }
}