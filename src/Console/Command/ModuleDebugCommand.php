<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2014 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModuleDebug Command.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 * @package     PPI
 * @subpackage  Console
 */
class ModuleDebugCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('module:debug')
            ->setDescription('Displays information about the currently loaded modules')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command dumps information about the currently loaded modules.

  <info>php %command.full_name%</info>

EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $appName = 'PPI';
        $mm = $this->getServiceManager()->get('ModuleManager');
        $modules = $mm->getLoadedModules(true);

        $output->writeln(sprintf('%s is running with <info>%d</info> modules loaded.', $appName, count($modules)));

        foreach ($modules as $module) {
            $output->writeln(PHP_EOL . '<info>' . $module->getName() . '</info>');
            $output->writeln(' - <comment>namespace:</comment> ' . $module->getNamespace());
            $output->writeln(' - <comment>path:</comment>      ' . $module->getPath());
        }

    }
}
