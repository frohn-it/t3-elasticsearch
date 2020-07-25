<?php


namespace BeFlo\T3Elasticsearch\Command;


use BeFlo\T3Elasticsearch\Configuration\ConfigurationManager;
use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Server\ServerLoader;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class ListCommand extends Command
{

    /**
     * Configures the current command.
     */
    protected function configure()
    {
        $this->setDescription('This command list all servers with their indexes');
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
        $configurationManager = GeneralUtility::makeInstance(ConfigurationManager::class);
        $configuration = $configurationManager->getConfiguration();
        $serverLoader = GeneralUtility::makeInstance(ServerLoader::class);
        $output->writeln('Server overview');
        $output->writeln(str_pad('', 40, '='));
        $output->writeln('');
        /** @var Server $server */
        foreach($configuration['server'] ?? [] as $server) {
            $this->printValue($output, 'Server', $server->getIdentifier());
            $this->printValue($output, 'Host', $server->getHost());
            $this->printValue($output, 'Port', $server->getPort());
            $status = $server->getStatus();
            $this->printValue($output, 'Connected', ($status['connected'] ? 'Yes' : 'No'));
            $output->writeln('');
            $output->writeln('Indexes: ');
            $table = new Table($output);
            $table->setHeaders(['Name', 'Exist', 'Up2Date']);
            foreach($server->getIndexes() as $index) {
                $table->addRow([$index->getIdentifier(), ($index->exist() ? 'Yes' : 'No'), ($index->isMappingDirty() ? 'No' : 'Yes')]);
            }
            $table->render();
        }

        return 0;
    }

    /**
     * @param OutputInterface $output
     * @param string          $key
     * @param string          $value
     */
    protected function printValue(OutputInterface $output, string $key, string $value): void
    {
        $output->writeln(str_pad($key . ': ', 15, ' ') . $value);
    }

}