<?php

namespace DirectokiBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *  @license 3-clause BSD
 *  @link https://github.com/Directoki/Directoki-Core/blob/master/LICENSE.txt
 */
class ImportCSVCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('directoki:import-csv')
            ->setDescription('Import a CSV.')
            ->setHelp('This command allows you to import a CSV.')
            ->addArgument('config', InputArgument::REQUIRED, 'Config');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('Import CSV');

        $doctrine = $this->getContainer()->get('doctrine');

        $config = parse_ini_file($input->getArgument('config'), true);

        $project = $doctrine->getRepository('DirectokiBundle:Project')->findOneByPublicId($config['general']['project']);
        if (!$project) {
            $output->writeln('Cant load Project');
            return;
        }
        $output->writeln('Project: '. $project->getTitle());

        $directory = $doctrine->getRepository('DirectokiBundle:Directory')->findOneBy(array('project'=>$project, 'publicId'=>$config['general']['directory']));
        if (!$directory) {
            $output->writeln('Cant load Directory');
            return;
        }
        $output->writeln('Directory: '. $directory->getTitleSingular());

        if (!file_exists($config['general']['file']) || !is_readable($config['general']['file'])) {
            $output->writeln('Cant load File');
            return;
        }

        $fields = array();
        foreach($config as $header=>$section) {
            if (substr($header,0,6) == 'field_') {
                $field = $doctrine->getRepository('DirectokiBundle:Field')->findOneBy(array('directory'=>$directory, 'publicId'=>substr($header, 6)));
                if (!$field) {
                    $output->writeln('Cant load Field');
                    return;
                }
                $fields[substr($header, 6)] = array(
                    'field'=>$field,
                    'fieldType'=>$fieldType = $this->getContainer()->get( 'directoki_field_type_service' )->getByField( $field ),
                    'config'=>$section
                );
            }
        }


        $file = fopen($config['general']['file'], 'r');
        while($line = fgetcsv($file)) {

            $output->writeln('Line ...');


            foreach($fields as $fieldName=>$fieldData) {
                $return = $fieldType->parseCSVLineData($fieldData['field'], $fieldData['config'], $line);
                if ($return) {

                    $output->writeln(' ... '. $fieldName . ' : ' . $return->getDebugOutput());


                    // TODO actually save!! :-)
                }
            }


        }
        fclose($file);

        $output->writeln('Done!');

    }
}

