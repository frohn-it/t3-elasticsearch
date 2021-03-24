<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Server\ServerStatus;

interface ServerStatusPostProcessHookInterface extends BaseHookInterface
{
    /**
     * @param array        $serverStatus
     * @param ServerStatus $pObj
     */
    public function serverStatusPostProcess(array &$serverStatus, ServerStatus $pObj): void;
}