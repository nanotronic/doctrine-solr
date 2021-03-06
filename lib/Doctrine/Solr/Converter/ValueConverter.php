<?php
/**
 * ValueConverter.php
 *
 * This file is part of the SolariumExtensionsBundle.
 * Read the LICENSE file in the root of the project for information on copyright.
 *
 * @author Thomas Ploch <tp@responsive-code.de>
 * @since  2.0.1
 */
namespace Doctrine\Solr\Converter;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\Solr\Converter\ValueConverterInterface;
use Doctrine\Solr\Metadata\PropertyMetadata;

/**
 * Class ValueConverter
 *
 * @package Doctrine\Solr\Converter
 */
class ValueConverter implements ValueConverterInterface
{
    /**
     * @param object $object
     * @param PropertyMetadata $property
     * @param PropertyAccessor $accessor
     *
     * @return mixed|object
     */
    public function convert($object, PropertyMetadata $property, PropertyAccessor $accessor)
    {
        $value = $object;

        if (!$property->multi) {
            $value = $property->getValue($object);
            //$fieldName = isset($property->propertyAccess) ? $property->propertyAccess : $property->reflection->name;
            //$value = $accessor->getValue($object, $fieldName);
        }
        #var_dump($property);
        if ($value && $property->propertyAccess && $property->propertyAccess !== PropertyMetadata::TYPE_RAW) {
            $value = $accessor->getValue(
                $value,
                $property->propertyAccess
            );
        }

        if($property->convertFunction && method_exists($object, $property->convertFunction)){
            $cFn = $property->convertFunction;
            $value = $object->$cFn($value);
        }

        return $value;
    }

    /**
     * @param object $object
     * @param PropertyMetadata $property
     * @param PropertyAccessor $accessor
     *
     * @throws \InvalidArgumentException
     * @return array
     */
    public function convertMulti($object, PropertyMetadata $property, PropertyAccessor $accessor)
    {
        $traversable = $property->getValue($object);

        if (!is_array($traversable) && !$traversable instanceof \Traversable && !$traversable instanceof \stdClass) {
            $traversable = array();
            /*$message = "Field '%s' is declared as multi field, but property value is not traversable.";

            throw new \InvalidArgumentException(sprintf($message, $property->name));*/
        }

        $values = array();

        foreach ($traversable as $item) {
            $value = $this->convert($item, $property, $accessor);
            if (!is_array($value)) {
                $value = array($value);
            }
            foreach ($value as $val) {
                $values[] = $val;
            }
        }

        return array_unique($values);
    }
}
