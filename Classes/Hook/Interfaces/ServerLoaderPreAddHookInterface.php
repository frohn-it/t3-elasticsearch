<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Domain\Dto\Server;
use BeFlo\T3Elasticsearch\Server\ServerLoader;

interface ServerLoaderPreAddHookInterface
{
    /**
     * @param Server       $server
     * @param array        $ServerConfiguration
     * @param ServerLoader $pObj
     */
    public function preAddServer(Server $server, array $ServerConfiguration, ServerLoader $pObj): void;
}