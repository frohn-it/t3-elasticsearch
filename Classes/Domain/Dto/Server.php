<?php


namespace BeFlo\T3Elasticsearch\Domain\Dto;


use BeFlo\T3Elasticsearch\Index\Index;
use BeFlo\T3Elasticsearch\Server\ServerStatus;
use BeFlo\T3Elasticsearch\Utility\ObjectStorage;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class Server
{

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var string
     */
    private $host;

    /**
     * @var int
     */
    private $port = 9200;

    /**
     * @var ServerStatus
     */
    private $status;

    /**
     * @var ObjectStorage|Index[]
     */
    private $indexes;

    /**
     * Server constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
        $this->indexes = new ObjectStorage();
    }

    /**
     * @return string
     */
    public function getHost(): string
    {
        return $this->host;
    }

    /**
     * @param string $host
     *
     * @return Server
     */
    public function setHost(string $host): Server
    {
        $this->host = $host;

        return $this;
    }

    /**
     * @return int
     */
    public function getPort(): int
    {
        return $this->port;
    }

    /**
     * @param int $port
     *
     * @return Server
     */
    public function setPort(int $port): Server
    {
        $this->port = $port;

        return $this;
    }

    /**
     * @return array
     */
    public function getStatus(): array
    {
        if (empty($this->status)) {
            $this->status = GeneralUtility::makeInstance(ServerStatus::class, $this);
        }

        return $this->status->get();
    }

    /**
     * @return string
     */
    public function getIdentifier(): string
    {
        return $this->identifier;
    }

    /**
     * @return ObjectStorage|Index[]
     */
    public function getIndexes(): ObjectStorage
    {
        return $this->indexes;
    }

    /**
     * @param ObjectStorage $indexes
     *
     * @return Server
     */
    public function setIndexes(ObjectStorage $indexes): Server
    {
        $this->indexes = $indexes;

        return $this;
    }

    /**
     * @param Index $index
     *
     * @return $this
     */
    public function addIndex(Index $index): Server
    {
        $this->indexes->attach($index);

        return $this;
    }
}