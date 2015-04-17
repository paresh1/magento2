<?php
/**
 * Copyright © 2015 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Magento\Indexer\Console;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * Command for setting index mode for indexers.
 */
class IndexerSetModeCommand extends AbstractIndexerCommand
{
    /**
     * Names of input arguments or options
     */
    const INPUT_KEY_MODE = 'mode';
    const INPUT_KEY_REALTIME = 'realtime';
    const INPUT_KEY_SCHEDULE = 'schedule';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->setName('indexer:set-mode')
            ->setDescription(
                'Sets index mode type'
            )->setDefinition($this->getOptionsList());
        parent::configure();
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $errors = $this->validate($input);
        if ($errors) {
            throw new \InvalidArgumentException(implode("\n", $errors));
        }

        $indexers = $this->getIndexers($input);

        foreach ($indexers as $indexer) {
            try {
                $previousStatus = $indexer->isScheduled() ? 'Update by Schedule' : 'Update on Save';
                $indexer->setScheduled($input->getArgument(self::INPUT_KEY_MODE) === self::INPUT_KEY_SCHEDULE);
                $currentStatus = $indexer->isScheduled() ? 'Update by Schedule' : 'Update on Save';
                if ($previousStatus !== $currentStatus) {
                    $output->writeln(
                        'Index mode for Indexer ' . $indexer->getTitle() . ' was changed from \''
                        . $previousStatus . '\' to \'' . $currentStatus . '\''
                    );
                } else {
                    $output->writeln('Index mode for Indexer ' . $indexer->getTitle() . ' has not been changed');
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $output->writeln($e->getMessage() . PHP_EOL);
            } catch (\Exception $e) {
                $output->writeln($indexer->getTitle() . " indexer process unknown error:" . PHP_EOL);
                $output->writeln($e->getMessage() . PHP_EOL);
            }
        }

        return $this;
    }

    /**
     * Get list of arguments for the command
     *
     * @return InputOption[]
     */
    public function getOptionsList()
    {
        $modeOptions[] = new InputArgument(
            self::INPUT_KEY_MODE,
            InputArgument::OPTIONAL,
            'Indexer mode type ['. self::INPUT_KEY_REALTIME . '|' . self::INPUT_KEY_SCHEDULE .']'
        );
        $optionsList = array_merge($modeOptions, parent::getOptionsList());
        return $optionsList;
    }

    /**
     * Check if all admin options are provided
     *
     * @param InputInterface $input
     * @return string[]
     */
    public function validate(InputInterface $input)
    {
        $errors = [];
        $acceptedValues = ' Accepted values for ' . self::INPUT_KEY_MODE . ' are \''
            . self::INPUT_KEY_REALTIME . '\' or \'' . self::INPUT_KEY_SCHEDULE . '\'';

        $inputMode = $input->getArgument(self::INPUT_KEY_MODE);
        if (!$inputMode) {
            $errors[] = 'Missing argument \'' . self::INPUT_KEY_MODE .'\'.' . $acceptedValues;
        } elseif (!in_array($inputMode, [self::INPUT_KEY_REALTIME, self::INPUT_KEY_SCHEDULE])) {
            $errors[] = $acceptedValues;
        }
        return $errors;
    }
}
