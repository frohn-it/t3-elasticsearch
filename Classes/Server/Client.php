<?php


namespace BeFlo\T3Elasticsearch\Server;



use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Hook\Interfaces\ClientPreConnectionHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;

class Client
{
    use HookTrait;

    /**
     * @var Client[]
     */
    protected static $clientStorage = [];

    /**
     * @var \Elastica\Client
     */
    protected $elasticaClient;

    /**
     * @var Server
     */
    protected $server;

    /**
     * Client constructor.
     *
     * @param Server $server
     */
    protected function __construct(Server $server)
    {
        $this->initHooks(Client::class);
        $this->server = $server;
        $this->connect();
    }

    /**
     * Connect the client
     */
    protected function connect(): void
    {
        $serverConfiguration = [
            'host' => $this->server->getHost(),
            'port' => $this->server->getPort()
        ];
        $parameter = [&$serverConfiguration, $this->server, $this];
        $this->executeHook(ClientPreConnectionHookInterface::class, $parameter);
        $this->elasticaClient = new \Elastica\Client($serverConfiguration);
    }

    /**
     * @param Server $server
     *
     * @return Client|\Elastica\Client
     */
    public static function get(Server $server): Client
    {
        if(empty(self::$clientStorage[$server->getIdentifier()])) {
            self::$clientStorage[$server->getIdentifier()] = new self($server);
        }

        return self::$clientStorage[$server->getIdentifier()];
    }

    /**
     * is triggered when invoking inaccessible methods in an object context.
     *
     * @param $name      string
     * @param $arguments array
     *
     * @return mixed
     * @link https://php.net/manual/en/language.oop5.overloading.php#language.oop5.overloading.methods
     */
    public function __call($name, $arguments)
    {
        $result = null;
        if(method_exists($this->elasticaClient, $name)) {
            $result = $this->elasticaClient->{$name}(...$arguments);
        }

        return $result;
    }


}