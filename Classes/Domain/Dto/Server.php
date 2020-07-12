<?php


namespace BeFlo\T3Elasticsearch\Domain\Dto;


use BeFlo\T3Elasticsearch\Server\ServerStatus;
use Elastica\Status;
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
     * @var array
     */
    private $indexes = [];

    /**
     * Server constructor.
     *
     * @param string $identifier
     */
    public function __construct(string $identifier)
    {
        $this->identifier = $identifier;
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
        if(empty($this->status)) {
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
     * @return array
     */
    public function getIndexes(): array
    {
        return $this->indexes;
    }

    /**
     * @param array $indexes
     *
     * @return Server
     */
    public function setIndexes(array $indexes): Server
    {
        $this->indexes = $indexes;

        return $this;
    }

}