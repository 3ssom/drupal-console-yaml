<?php

/**
 * @file
 * Contains \Drupal\Console\Component\Yaml\Command\GetValueCommand.
 */

namespace Drupal\Console\Yaml\Command;

use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Yaml\Parser;
use Drupal\Console\Core\Command\Command;
use Drupal\Console\Core\Style\DrupalStyle;
use Drupal\Console\Core\Utils\NestedArray;
use Drupal\Console\Annotations\DrupalCommand;

/**
 * Class GetValueCommand.
 *
 * @DrupalCommand (
 *     extension="drupal/console-yaml",
 *     extensionType="library"
 * )
 */
class GetValueCommand extends Command
{
    /**
     * @var NestedArray
     */
    protected $nestedArray;

    /**
     * GetValueCommand constructor.
     *
     * @param NestedArray $nestedArray
     */
    public function __construct(NestedArray $nestedArray)
    {
        $this->nestedArray = $nestedArray;
        parent::__construct();
    }

    protected function configure()
    {
        $this
            ->setName('yaml:get:value')
            ->setDescription($this->trans('commands.yaml.get.value.description'))
            ->addArgument(
                'yaml-file',
                InputArgument::REQUIRED,
                $this->trans('commands.yaml.get.value.arguments.yaml-file')
            )
            ->addArgument(
                'yaml-key',
                InputArgument::REQUIRED,
                $this->trans('commands.yaml.get.value.arguments.yaml-key')
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new DrupalStyle($input, $output);

        $yaml = new Parser();

        $yaml_file = $input->getArgument('yaml-file');
        $yaml_key = $input->getArgument('yaml-key');

        try {
            $yaml_parsed = $yaml->parse(file_get_contents($yaml_file), true);
        } catch (\Exception $e) {
            $io->error($this->trans('commands.yaml.merge.messages.error-parsing').': '.$e->getMessage());
            return;
        }

        if (empty($yaml_parsed)) {
            $io->info(
                sprintf(
                    $this->trans('commands.yaml.merge.messages.wrong-parse'),
                    $yaml_file
                )
            );
        } else {
            $key_exists = null;
            $parents = explode(".", $yaml_key);
            $yaml_value = $this->nestedArray->getValue($yaml_parsed, $parents, $key_exists);

            if (!$key_exists) {
                $io->info(
                    sprintf(
                        $this->trans('commands.yaml.get.value.messages.invalid-key'),
                        $yaml_key,
                        $yaml_file
                    )
                );
            }

            $io->writeln($yaml_value);
        }
    }
}
