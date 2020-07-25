<?php


namespace BeFlo\T3Elasticsearch\Index;


use BeFlo\T3Elasticsearch\Exceptions\UnexpectedObjectException;
use SplObjectStorage;

class IndexStorage extends SplObjectStorage
{
    /**
     * Adds an object in the storage
     *
     * @link https://php.net/manual/en/splobjectstorage.attach.php
     *
     * @param Index $object  <p>
     *                       The object to add.
     *                       </p>
     * @param mixed $data    [optional] <p>
     *                       The data to associate with the object.
     *                       </p>
     *
     * @return void
     */
    public function attach($object, $data = null)
    {
        if (!($object instanceof Index)) {
            throw new UnexpectedObjectException(sprintf('The index storage could only handle objects of type "%s"', Index::class));
        }
        parent::attach($object, $data);
    }

    /**
     * @param string $identifier
     *
     * @return Index|null
     */
    public function find(string $identifier): ?Index
    {
        $result = null;
        /** @var Index $obj */
        foreach ($this as $obj) {
            if ($obj->getIdentifier() === $identifier) {
                $result = $obj;
                break;
            }
        }

        return $result;
    }
}