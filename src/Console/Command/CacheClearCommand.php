<?php
/**
 * This file is part of the PPI Framework.
 *
 * @copyright  Copyright (c) 2011-2013 Paul Dragoonis <paul@ppi.io>
 * @license    http://opensource.org/licenses/mit-license.php MIT
 * @link       http://www.ppi.io
 */

namespace PPI\Console\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Clear the cache.
 *
 * @author Vítor Brandão <vitor@ppi.io>
 */
class CacheClearCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('cache:clear')
            ->setDescription('Clears the cache')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command clears the application cache for a given environment
and debug mode:

<info>php %command.full_name% --env=dev</info>
<info>php %command.full_name% --env=prod --no-debug</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $cacheDir   = $this->getServiceManager()->getParameter('app.cache_dir');
        $filesystem = $this->getServiceManager()->getService('filesystem');

        if (!is_writable($cacheDir)) {
            throw new \RuntimeException(sprintf('Unable to write in the "%s" directory', $cacheDir));
        }

        $app = $this->getServiceManager()->get('app');
        $output->writeln(sprintf('Clearing the cache for the <info>%s</info> environment with debug <info>%s</info>', $app->getEnvironment(), var_export($app->isDebug(), true)));
        if (!$filesystem->exists($cacheDir)) {
            return;
        }

        $filesystem->remove(glob($cacheDir . '/*'));
    }
}
