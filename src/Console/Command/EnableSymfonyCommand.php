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

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * ModuleDebug Command.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 */
class EnableSymfonyCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('symfony:enable')
            ->setDescription('Enables symfony support for your PPI application');
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {

        // @todo - copy sfconsole and sfkernel
        $rootDir = dirname($this->getServiceManager()->get('app')->getRootDir());
        $skeletonDir = $rootDir . '/' . 'utils/skeleton_symfony/app/';
        $appDir = $rootDir . '/app/';
        $appConfigDir = $appDir . 'config/base/symfony/';

        $copyMap = [
            $skeletonDir . 'sfconsole' => $appDir . 'sfconsole',
            $skeletonDir . 'sfkernel.php' => $appDir . 'sfkernel.php',
            $skeletonDir . 'config/bundles.yml' => $appConfigDir . 'bundles.yml',
            $skeletonDir . 'config/config.yml' => $appConfigDir . 'config.yml',
            $skeletonDir . 'config/routing.yml' => $appConfigDir . 'routing.yml'
        ];

        // Make Symfony Dir
        $filesystem = $this->getServiceManager()->get('filesystem');
        $filesystem->mkdir($appConfigDir, 0777);
        $output->writeln('Created ' . $appConfigDir);

        foreach($copyMap as $fileSrc => $fileDst) {
            if(realpath($fileSrc) === false) {
                $output->writeln('Unable to locate: ' . $fileSrc);
                return;
            }
            if(realpath(dirname($fileDst)) === false) {
                $output->writeln('Unable to copy to: ' . $fileDst);
                return;
            }
            $res = copy($fileSrc, $fileDst);
            $output->writeln('Copied: ' . $fileDst); // @todo - check $res
        }

        $filesystem->mkdir($rootDir . '/bundles', 0777);
        $output->writeln('Created ' . $rootDir . '/bundles/');

        $output->writeln('Files copied. Now include app/sfkernel.php into your public/index.php file, as per the docs');

    }
}
