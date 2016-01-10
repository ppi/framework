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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * Outputs all the configuration processed by the Framework, after merging.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 */
class ConfigDebugCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:debug')
            ->setDescription('Dumps the configuration in use')
            ->addOption('app-only', null, InputOption::VALUE_NONE, 'Show only the configuration set in the app/ directory')
            ->addOption('write-php', null, InputOption::VALUE_REQUIRED, 'Save the configuration in PHP format')
            ->addOption('write-yaml', null, InputOption::VALUE_REQUIRED, 'Save the configuration in YAML format')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command dumps the configuration after being merged and processed
by the framework:

  <info>%command.full_name%</info>

If you only want to see the configuration defined in the app/ directory (excluding modules)
use the <info>--app-only</info> option. This is the "raw" configuration, not processed by the framework.

  <info>%command.full_name% --app-only</info>
EOF
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indentation = 4;

        if ($input->getOption('app-only')) {
            $message = "This is the configuration defined in the app/ directory (not processed):\n";
            $config  = $this->getServiceManager()->get('ApplicationConfig');
        } else {
            $message = "This is the configuration in use for your current setup (merged and processed):\n";
            $config  = $this->getServiceManager()->get('Config');
        }

        $files    = array();
        $contents = array();

        if (($files['php'] = $input->getOption('write-php'))) {
            $contents['php'] = "<?php\n\nreturn " . var_export($config, true) . ";\n\n?>\n";
        }

        if (($files['yaml'] = $input->getOption('write-yaml'))) {
            $dumper = new Dumper();
            $dumper->setIndentation($indentation);
            $contents['yaml'] = $dumper->dump($config, 6, 0, false, false);
        }

        if (empty($contents)) {
            $dumper = new Dumper();
            $dumper->setIndentation($indentation);
            $output->writeln($message);

            foreach ($config as $rootKey => $subConfig) {
                $output->writeln('<info>' . $rootKey . '</info>:');
                $output->writeln($dumper->dump($subConfig, 6, $indentation, false, false));
            }

            return;
        }

        foreach ($files as $format => $file) {
            $output->write('Saving configuration in <info>' . strtoupper($format) . '</info> format...');
            if ($fileExists = file_exists($file)) {
                if (!isset($dialog)) {
                    $dialog = $this->getHelperSet()->get('dialog');
                }
                if (!$dialog->askConfirmation($output,
                    " <question>File \"" . $file . "\" already exists. Proceed anyway?</question> ", false)) {
                    continue;
                }
            }

            file_put_contents($file, $contents[$format]);

            if (!$fileExists) {
                $output->writeln(' OK.');
            }
        }
    }
}
