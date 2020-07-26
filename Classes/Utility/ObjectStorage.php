<?php


namespace BeFlo\T3Elasticsearch\Utility;


use BeFlo\T3Elasticsearch\Index\Index;

class ObjectStorage extends \SplObjectStorage
{
    /**
     * @param string $identifier
     *
     * @return object|null
     */
    public function find(string $identifier)
    {
        $result = null;
        foreach ($this as $obj) {
            if (method_exists($obj, 'getIdentifier') && $obj->getIdentifier() === $identifier) {
                $result = $obj;
                break;
            }
        }

        return $result;
    }
}