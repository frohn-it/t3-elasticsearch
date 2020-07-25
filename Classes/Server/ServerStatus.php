<?php


namespace BeFlo\T3Elasticsearch\Server;


use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\ServerStatusPostProcessHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use Elastica\Client;
use Psr\Log\LoggerAwareInterface;
use Psr\Log\LoggerAwareTrait;

class ServerStatus implements LoggerAwareInterface
{
    use LoggerAwareTrait;
    use HookTrait;

    /**
     * @var Server
     */
    protected $server;

    /**
     * @var array
     */
    protected $status = [];

    /**
     * ServerStatus constructor.
     *
     * @param Server $server
     */
    public function __construct(Server $server)
    {
        $this->server = $server;
    }

    /**
     * @return array
     */
    public function get(): array
    {
        if(empty($this->status)) {
            $this->init();
        }

        return $this->status;
    }

    /**
     * Initialize the server status
     */
    protected function init()
    {
        $this->initHooks(ServerStatus::class);
        try {
            $client = \BeFlo\T3Elasticsearch\Server\Client::get($this->server);
            $status = $client->getStatus();

            $this->status = [
                'data'      => $status->getData(),
                'version'   => $client->getVersion(),
                'connected' => $client->hasConnection()
            ];
        } catch (\Throwable $exception) {
            $this->logger->info($exception->getMessage());
            $this->status = [
                'data' => [],
                'version' => '0.0.0',
                'connected' => false
            ];
        }
        $parameter = [&$this->status, $this];
        $this->executeHook(ServerStatusPostProcessHookInterface::class, $parameter);
    }

}