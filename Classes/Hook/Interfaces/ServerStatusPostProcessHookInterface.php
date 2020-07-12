<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Server\ServerStatus;

interface ServerStatusPostProcessHookInterface
{
    /**
     * @param array        $serverStatus
     * @param ServerStatus $pObj
     */
    public function serverStatusPostProcess(array &$serverStatus, ServerStatus $pObj): void;
}