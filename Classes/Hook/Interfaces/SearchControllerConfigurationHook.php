<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


interface SearchControllerConfigurationHook extends BaseHookInterface
{
    public function manipulateConfiguration(array $configuration);
}