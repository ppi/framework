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
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Dumper;

/**
 * Outputs all the configuration processed by the Framework, after merging.
 *
 * @author      Vítor Brandão <vitor@ppi.io>
 */
class ConfigDumpCommand extends AbstractCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('config:dump')
            ->setDescription('Dumps the configuration in use')
            ->addOption('app-only', null, InputOption::VALUE_NONE, 'Show only the configuration set in the app/ directory')
            ->addOption('write-php', null, InputOption::VALUE_REQUIRED, 'Save the configuration in PHP format')
            ->addOption('write-yaml', null, InputOption::VALUE_REQUIRED, 'Save the configuration in YAML format')
            ->setHelp(<<<EOF
The <info>%command.name%</info> command dumps the configuration after being merged and processed
by the framework:

  <info>%command.full_name%</info>

If you only want to see the configuration defined in the app/ directory (excluding modules)
use the <info>--app-only</info> option.

  <info>%command.full_name% --app-only</info>
EOF
            )
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $indentation = 4;

        if ($input->getOption('app-only')) {
            $message = "This is the configuration defined in the app/ directory:\n";
            $config = $this->getServiceManager()->get('ApplicationConfig');
        } else {
            $message = "This is the configuration in use for your current setup:\n";
            $config = $this->getServiceManager()->get('Config');
        }

        if (($file = $input->getOption('write-php'))) {
            $content = "<?php\n\nreturn ".var_export($config, true).";\n\n?>\n";
        } elseif (($file = $input->getOption('write-yml')) || ($file = $input->getOption('write-yaml'))) {
            $dumper = new Dumper();
            $dumper->setIndentation($indentation);
            $content = $dumper->dump($config, 6, 0, false, false);
        }

        if ($file) {
            if (file_exists($file)) {
                $dialog = $this->getHelperSet()->get('dialog');
                if (!$dialog->askConfirmation($output,
                    '<question>File "'.$file.'" already exists. Proceed anyway?</question> ',
                    false
                )) {
                    return;
                }
            }

            file_put_contents($file, $content);

            return;
        } else {
            $dumper = new Dumper();
            $dumper->setIndentation($indentation);
            $output->writeln($message);

            foreach ($config as $rootKey => $subConfig) {
                $output->writeln('<info>'.$rootKey.'</info>:');
                $output->writeln($dumper->dump($subConfig, 6, $indentation, false, false));
            }
        }
    }
}
