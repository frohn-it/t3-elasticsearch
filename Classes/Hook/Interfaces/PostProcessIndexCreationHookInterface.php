<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PostProcessIndexCreationHookInterface
{

    /**
     * @param Index $index
     */
    public function postProcessIndexCreation(Index $index): void;
}