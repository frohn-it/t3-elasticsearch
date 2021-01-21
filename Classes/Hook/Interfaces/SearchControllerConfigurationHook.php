<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


interface SearchControllerConfigurationHook
{
    public function manipulateConfiguration(array $configuration);
}