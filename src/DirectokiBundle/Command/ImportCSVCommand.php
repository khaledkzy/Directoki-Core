<?php

namespace DirectokiBundle\Command;

use DirectokiBundle\Entity\Record;
use DirectokiBundle\Entity\RecordHasState;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
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
            ->addArgument('config', InputArgument::REQUIRED, 'Config')
            ->addOption('save',null,InputOption::VALUE_NONE,'Actually Save Changes');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {

        $save = $input->getOption('save');

        $output->writeln('Import CSV '.($save ? '(SAVE)' : '(test run)'));

        $doctrine = $this->getContainer()->get('doctrine')->getManager();

        $config = parse_ini_file($input->getArgument('config'), true);
        $publish = isset($config['general']['publish']) ? boolval($config['general']['publish']) : false;

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

        $event = $this->getContainer()->get('directoki_event_builder_service')->build(
            $project,
            null,
            null,
            isset($config['general']['comment']) ? $config['general']['comment'] : ''
        );
        if ($save) {
            $doctrine->persist($event);
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
                    'fieldType'=>$this->getContainer()->get( 'directoki_field_type_service' )->getByField( $field ),
                    'config'=>$section
                );
            }
        }


        $file = fopen($config['general']['file'], 'r');
        while($line = fgetcsv($file)) {

            $output->writeln('Line ...');

            $record = new Record();
            $record->setCreationEvent( $event );
            $record->setDirectory($directory);
            $record->setCachedState($publish ? RecordHasState::STATE_PUBLISHED : RecordHasState::STATE_DRAFT);

            if ($save) {
                $doctrine->persist($record);

                if ($publish) {
                    // import published
                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord( $record );
                    $recordHasState->setCreationEvent( $event );
                    $recordHasState->setApprovalEvent($event);
                    $recordHasState->setState( RecordHasState::STATE_PUBLISHED );
                    $doctrine->persist( $recordHasState );
                } else {
                    // Also record a request to publish this record but don't approve it - moderator will do that.
                    $recordHasState = new RecordHasState();
                    $recordHasState->setRecord( $record );
                    $recordHasState->setCreationEvent( $event );
                    $recordHasState->setState( RecordHasState::STATE_PUBLISHED );
                    $doctrine->persist( $recordHasState );
                }
            }

            foreach($fields as $fieldName=>$fieldData) {
                $return = $fieldData['fieldType']->parseCSVLineData($fieldData['field'], $fieldData['config'], $line, $record, $event, $publish);
                if ($return) {

                    $output->writeln(' ... '. $fieldName . ' : ' . $return->getDebugOutput());

                    if ($save) {
                        foreach ( $return->getEntitiesToSave() as $entityToSave ) {
                            $doctrine->persist($entityToSave);
                        }
                    }

                }
            }

            if ($save) {
                $doctrine->flush();
                $output->writeln(' ... ... Saved as: '.$record->getPublicId());
            }

        }
        fclose($file);

        $output->writeln('Done!');

    }
}

