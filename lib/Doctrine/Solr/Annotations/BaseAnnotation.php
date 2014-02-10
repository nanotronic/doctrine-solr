<?php
namespace Doctrine\Solr\Annotations;

use Doctrine\Solr\Annotations\Field;
use Doctrine\Solr\Annotations\Operation;

abstract class BaseAnnotation
{
    /**
     * Error handler for unknown property accessor in BaseAnnotation class.
     *
     * @param string $name Unknown property name
     *
     * @throws \BadMethodCallException
     */
    public function __get($name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }

    /**
     * Error handler for unknown property mutator in BaseAnnotation class.
     *
     * @param string $name Unkown property name
     * @param mixed $value Property value
     *
     * @throws \BadMethodCallException
     */
    public function __set($name, $value)
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;

            return;
        }
        throw new \BadMethodCallException(
            sprintf("Unknown property '%s' on annotation '%s'.", $name, get_class($this))
        );
    }
    
     /**
    * Checks if a given field type is multiValued
    *
    * @param string $type
    *
    * @return bool
    */
    public function isMultiValuedType($type)
    {
        return in_array((string) $type, Field::getMultiFieldTypes());
    }

    /**
    * Checks if a given field type is valid.
    *
    * @param string $type
    *
    * @return Boolean
    */
    public function isValidType($type)
    {
        return in_array((string) $type, Field::getFieldTypes());
    }

    /**
    * @param string $operation
    *
    * @return bool
    */
    public function isValidOperation($operation)
    {
        return in_array((string) $operation, Operation::getOperationTypes());
    }
}
