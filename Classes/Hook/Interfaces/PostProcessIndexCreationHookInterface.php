<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Index\Index;

interface PostProcessIndexCreationHookInterface extends BaseHookInterface
{

    /**
     * @param Index $index
     */
    public function postProcessIndexCreation(Index $index): void;
}