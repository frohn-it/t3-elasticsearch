<?php


namespace BeFlo\T3Elasticsearch\Hook\Interfaces;


use BeFlo\T3Elasticsearch\Backend\ToolbarItems\ElasticSearchToolbarItem;

interface ElasticSearchToolbarItemAddFluidPathsHookInterface
{

    /**
     * Return additional template paths for the toolbar item to override the item itself and also the dropdown.
     *
     * @param array                    $paths
     * @param ElasticSearchToolbarItem $pObj
     */
    public function addFluidPaths(array &$paths, ElasticSearchToolbarItem $pObj): void;
}