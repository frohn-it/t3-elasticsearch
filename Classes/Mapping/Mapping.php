<?php


namespace BeFlo\T3Elasticsearch\Mapping;


use BeFlo\T3Elasticsearch\Hook\Interfaces\MappingPreJsonSerializeHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PostProcessMappingHookInterface;
use BeFlo\T3Elasticsearch\Hook\Interfaces\PreProcessMappingHookInterface;
use BeFlo\T3Elasticsearch\Utility\HookTrait;
use JsonSerializable;

class Mapping implements JsonSerializable
{
    use HookTrait;

    /**
     * @var array
     */
    protected $configuration = [];

    /**
     * Mapping constructor.
     *
     * @param array $configuration
     */
    public function __construct(array $configuration)
    {
        $this->parseMappingConfiguration($configuration);
    }

    /**
     * @param array $configuration
     */
    protected function parseMappingConfiguration(array $configuration): void
    {
        $parameter = [&$configuration, $this];
        $this->executeHook(PreProcessMappingHookInterface::class, $parameter);
        // @ToDo Implement basic mapping processing
        $this->executeHook(PostProcessMappingHookInterface::class, $parameter);
        $this->configuration = $configuration;
    }

    /**
     * @param array $mappingToCompareWith
     *
     * @return bool
     */
    public function isDirty(array $mappingToCompareWith): bool
    {

        return false;
    }

    /**
     * Specify data which should be serialized to JSON
     *
     * @link  https://php.net/manual/en/jsonserializable.jsonserialize.php
     * @return mixed data which can be serialized by <b>json_encode</b>,
     * which is a value of any type other than a resource.
     * @since 5.4
     */
    public function jsonSerialize()
    {
        $parameter = [$this->configuration, $this];
        $this->executeHook(MappingPreJsonSerializeHookInterface::class, $parameter);

        return $this->configuration;
    }

    /**
     * @return \Elastica\Mapping
     */
    public function get(): \Elastica\Mapping
    {
        $mapping = new \Elastica\Mapping();
        $mapping->setProperties($this->configuration);

        return $mapping;
    }
}