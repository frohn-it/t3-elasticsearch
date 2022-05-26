<?php


namespace BeFlo\T3Elasticsearch\Filter;


abstract class AbstractFilter implements FilterInterface
{
    /**
     * @var array
     */
    protected $configuration;

    /**
     * @param array $configuration
     */
    public function setConfiguration(array $configuration): void
    {
        $this->configuration = $configuration;
    }
}