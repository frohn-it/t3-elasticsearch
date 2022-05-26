<?php


namespace BeFlo\T3Elasticsearch\Filter;


class FilterManager
{

    /**
     * @var FilterInterface[]
     */
    private $availableFilter;

    /**
     * FilterManager constructor.
     */
    public function __construct($filter)
    {
        $this->availableFilter = $filter;
    }

    /**
     * @return FilterInterface[]
     */
    public function getAllFilter()
    {
        return $this->availableFilter;
    }

    /**
     * @param string $filterClassName
     *
     * @return FilterInterface|null
     */
    public function getFilter(string $filterClassName): ?FilterInterface
    {
        $result = null;
        foreach($this->availableFilter as $filter) {
            if(get_class($filter) === $filterClassName) {
                $result = clone $filter;
            }
        }

        return $result;
    }
}