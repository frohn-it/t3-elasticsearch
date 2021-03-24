<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Server\Client;

interface ClientPreConnectionHookInterface extends BaseHookInterface
{

    /**
     * @param array  $configuration
     * @param Server $server
     * @param Client $client
     */
    public function preConnect(array &$configuration, Server $server, Client $client): void;
}