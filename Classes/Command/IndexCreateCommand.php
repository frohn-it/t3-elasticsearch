<?php


namespace BeFlo\T3Elasticsearch\Command;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Exceptions\MissingOptionException;
use BeFlo\T3Elasticsearch\Index\Index;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Exception\InvalidArgumentException;
use Symfony\Component\Console\Exception\InvalidOptionException;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexCreateCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Create an index in the given server')
            ->addArgument('server', InputArgument::REQUIRED, 'Specify the server, where the index should becreated')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'Specify the index you want to create', null)
            ->addOption('all', null, InputOption::VALUE_OPTIONAL, 'Create/Update all indexes which are specified for the server')
            ->addOption('recreate', null, InputOption::VALUE_OPTIONAL, 'Set this option to recreate the index if allready existing (Caution: All data will be lost!)');
    }

    /**
     * Executes the current command.
     *
     * This method is not abstract because you can use this class
     * as a concrete class. In this case, instead of defining the
     * execute() method, you set the code to execute by passing
     * a Closure to the setCode() method.
     *
     * @return int 0 if everything went fine, or an exit code
     *
     * @throws LogicException When this abstract method is not implemented
     *
     * @see setCode()
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $serverIdentifier = $input->getArgument('server');
        $indexName = $input->getOption('index');
        $all = $input->getParameterOption('--all') !== false;
        $recreate = $input->getParameterOption('--recreate') !== false;
        if (!$indexName && !$all) {
            throw new MissingOptionException('You must either specify a single index or add the "all" flag!');
        }
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $server = $configurationManager->getServer($serverIdentifier);
        if (!($server instanceof Server)) {
            throw new InvalidArgumentException(sprintf('The given server "%s" does not exist!', $server));
        }
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');
        $successStyle = new OutputFormatterStyle('white', 'green');
        $output->getFormatter()->setStyle('success', $successStyle);
        $output->writeln('');
        if (!empty($indexName)) {
            $index = $server->getIndexes()->find($indexName);
            if (!($index instanceof Index)) {
                throw new InvalidOptionException(sprintf('The given index "%s" does not exist!', $indexName));
            }
            $index->create($recreate);
            $block = $formatter->formatBlock(sprintf('Index "%s" successfully created!', $indexName), 'success', true);
            $output->writeln($block);
        } else {
            foreach ($server->getIndexes() as $index) {
                $index->create($recreate);
                $block = $formatter->formatBlock(sprintf('Index "%s" successfully created!', $index->getIdentifier()), 'success', true);
                $output->writeln($block);
                $output->writeln('');
            }
        }

        return 0;
    }

}