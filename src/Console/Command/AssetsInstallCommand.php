<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Framework\Console\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Finder\Finder;

/**
 * Command that places module web assets into a given directory.
 *
 * @author      Fabien Potencier <fabien@symfony.com>
 * @author      Vítor Brandão <vitor@ppi.io>
 * @author      Paul Dragoonis <paul@ppi.io>
 * @package     PPI
 * @subpackage  Console
 */
class AssetsInstallCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('assets:install')
            ->setDefinition(array(
                new InputArgument('target', InputArgument::OPTIONAL, 'The target directory', 'public'),
            ))
            ->addOption('symlink', null, InputOption::VALUE_NONE, 'Symlinks the assets instead of copying it')
            ->addOption('relative', null, InputOption::VALUE_NONE, 'Make relative symlinks')
            ->setDescription('Installs modules public assets under a public directory')
            ->setHelp(<<<EOT
The <info>%command.name%</info> command installs module assets into a given
directory (e.g. the public directory).

<info>php %command.full_name% public</info>

A "modules" directory will be created inside the target directory, and the
"Resources/public" directory of each module will be copied into it.

To create a symlink to each module instead of copying its assets, use the
<info>--symlink</info> option:

<info>php %command.full_name% public --symlink</info>

To make symlink relative, add the <info>--relative</info> option:

<info>php %command.full_name% public --symlink --relative</info>

EOT
            );
    }

    /**
     * {@inheritdoc}
     *
     * @throws \InvalidArgumentException When the target directory does not exist or symlink cannot be used
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $targetArg = rtrim($input->getArgument('target'), '/');

        if (!is_dir($targetArg)) {
            throw new \InvalidArgumentException(sprintf('The target directory "%s" does not exist.', $input->getArgument('target')));
        }

        if (!function_exists('symlink') && $input->getOption('symlink')) {
            throw new \InvalidArgumentException('The symlink() function is not available on your system. You need to install the assets without the --symlink option.');
        }

        $filesystem = $this->getServiceManager()->get('filesystem');

        // Create the modules directory otherwise symlink will fail.
        $filesystem->mkdir($targetArg . '/modules/', 0777);

        $output->writeln(sprintf("Installing assets using the <comment>%s</comment> option", $input->getOption('symlink') ? 'symlink' : 'hard copy'));

        foreach ($this->getServiceManager()->get('modulemanager')->getLoadedModules() as $module) {
            if (is_dir($originDir = $module->getPath() . '/resources/public')) {
                $modulesDir = $targetArg . '/modules/';
                $targetDir  = $modulesDir . str_replace('module', '', strtolower($module->getName()));

                $output->writeln(sprintf('Installing assets for <comment>%s</comment> into <comment>%s</comment>', $module->getNamespace(), $targetDir));

                $filesystem->remove($targetDir);

                if ($input->getOption('symlink')) {
                    if ($input->getOption('relative')) {
                        $relativeOriginDir = $filesystem->makePathRelative($originDir, realpath($modulesDir));
                    } else {
                        $relativeOriginDir = $originDir;
                    }
                    $filesystem->symlink($relativeOriginDir, $targetDir);
                } else {
                    $filesystem->mkdir($targetDir, 0777);
                    // We use a custom iterator to ignore VCS files
                    $filesystem->mirror($originDir, $targetDir, Finder::create()->in($originDir));
                }
            }
        }
    }
}
