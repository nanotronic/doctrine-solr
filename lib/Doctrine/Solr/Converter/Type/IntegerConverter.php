<?php
/**
 * IntegerConverter.php
 *
 * This file is part of the SolariumExtensionsBundle.
 * Read the LICENSE file in the root of the project for information on copyright.
 *
 * @author Thomas Ploch <tp@responsive-code.de>
 * @since  2.0.1
 */
namespace Doctrine\Solr\Converter\Type;

use Symfony\Component\PropertyAccess\PropertyAccessor;
use Doctrine\Solr\Metadata\PropertyMetadata;
use Doctrine\Solr\Converter\ValueConverter;

/**
 * Class IntegerConverter
 *
 * @package TP\SolariumExtensionsBundle\Converter
 */
class IntegerConverter extends ValueConverter
{
    /**
     * @param object $object
     * @param PropertyMetadata $property
     * @param PropertyAccessor $accessor
     *
     * @return mixed|object|string
     *
     * @throws \InvalidArgumentException
     */
    public function convert($object, PropertyMetadata $property, PropertyAccessor $accessor)
    {
        $value = parent::convert($object, $property, $accessor);

        if (!is_numeric($value)) {
            $type    = gettype($value);
            $message = "Property '%s' must be a numeric value, '%s' given.";

            throw new \InvalidArgumentException(sprintf($message, $property->name, $type));
        }

        return (int) $value;
    }
}
