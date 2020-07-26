<?php


namespace BeFlo\T3Elasticsearch\Command;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Index\Index;
use LogicException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Formatter\OutputFormatterStyle;
use Symfony\Component\Console\Helper\FormatterHelper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class IndexStartCommand extends Command
{
    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('Index the search. If no server or index is specified all server with all indexes will be indexed!')
            ->addOption('server', 's', InputOption::VALUE_OPTIONAL, 'List of server which should be used')
            ->addOption('index', 'i', InputOption::VALUE_OPTIONAL, 'List of indexes which should be used');
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
        $serverList = $input->getOption('server');
        $indexList = $input->getOption('index');
        if (empty($serverList) && empty($indexList)) {
            $this->indexAll($output);
        } else if (!empty($serverList)) {
            $this->indexSpecificServer($serverList, $output, $indexList);
        } else {
            $this->indexSpecificIndexes($indexList, $output);
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     */
    protected function indexAll(OutputInterface $output): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration();
        $formatter = $this->getFormatter($output);
        /** @var Server $server */
        foreach ($configuration['server'] ?? [] as $server) {
            foreach ($server->getIndexes() as $index) {
                $this->startIndexing($index, $formatter, $output);
            }
        }
    }

    /**
     * @param OutputInterface $output
     *
     * @return FormatterHelper
     */
    protected function getFormatter(OutputInterface $output): FormatterHelper
    {
        /** @var FormatterHelper $formatter */
        $formatter = $this->getHelper('formatter');
        $successStyle = new OutputFormatterStyle('white', 'green');
        $output->getFormatter()->setStyle('success', $successStyle);
        $output->writeln('');

        return $formatter;
    }

    /**
     * @param Index           $index
     * @param FormatterHelper $formatter
     * @param OutputInterface $output
     */
    protected function startIndexing(Index $index, FormatterHelper $formatter, OutputInterface $output): void
    {
        $index->index();
        $block = $formatter->formatBlock(sprintf('Index "%s" on server "%s" successfully indexed!', $index->getIdentifier(), $index->getServer()->getIdentifier()), 'success', true);
        $output->writeln($block);
        $output->writeln('');
    }

    /**
     * @param string          $serverList
     * @param OutputInterface $output
     * @param string|null     $indexList
     */
    protected function indexSpecificServer(string $serverList, OutputInterface $output, ?string $indexList = null): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $formatter = $this->getFormatter($output);
        $serverArray = GeneralUtility::trimExplode(',', $serverList, true);
        $indexArray = !empty($indexList) ? GeneralUtility::trimExplode(',', $indexList, true) : [];
        foreach ($serverArray as $serverIdentifier) {
            $server = $configurationManager->getServer($serverIdentifier);
            if ($server instanceof Server) {
                if (empty($indexArray)) {
                    foreach ($server->getIndexes() as $index) {
                        $this->startIndexing($index, $formatter, $output);
                    }
                } else {
                    $this->indexOnlySpecificIndexes($indexArray, $server, $formatter, $output);
                }
            }
        }
    }

    /**
     * @param array           $indexArray
     * @param Server          $server
     * @param FormatterHelper $formatter
     * @param OutputInterface $output
     */
    protected function indexOnlySpecificIndexes(array $indexArray, Server $server, FormatterHelper $formatter, OutputInterface $output): void
    {
        foreach ($indexArray as $indexIdentifier) {
            $index = $server->getIndexes()->find($indexIdentifier);
            if ($index instanceof Index) {
                $this->startIndexing($index, $formatter, $output);
            }
        }
    }

    /**
     * @param string          $indexList
     * @param OutputInterface $output
     */
    protected function indexSpecificIndexes(string $indexList, OutputInterface $output): void
    {
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration();
        $formatter = $this->getFormatter($output);
        $indexArray = GeneralUtility::trimExplode(',', $indexList, true);
        /** @var Server $server */
        foreach ($configuration['server'] ?? [] as $server) {
            $this->indexOnlySpecificIndexes($indexArray, $server, $formatter, $output);
        }
    }
}