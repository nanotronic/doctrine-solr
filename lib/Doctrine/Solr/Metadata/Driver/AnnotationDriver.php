<?php
namespace Doctrine\Solr\Metadata\Driver;

use Doctrine\Common\Persistence\Mapping\Driver\AnnotationDriver as DoctrineAnnotationDriver;
use Doctrine\Common\Annotations\AnnotationReader;
use Doctrine\Common\Annotations\AnnotationRegistry;
#use Doctrine\Common\Persistence\Mapping\ClassMetadata;
#use Doctrine\Solr\Mapping\Annotations as SOLR;
#use Doctrine\Solr\Metadata\DocumentMetadata;

use Doctrine\Solr\Annotations\Document;
use Doctrine\Solr\Annotations\Field;
use Doctrine\Solr\Annotations\Mapping;
use Doctrine\Solr\Annotations\Operation;
use Doctrine\Solr\Annotations\Id;

use Doctrine\Solr\Metadata\PropertyMetadata;
use Doctrine\Solr\Metadata\ClassMetadata;

/**
 * Designed to load metadata into DocumentMetadata container.
 *
 * @author Jakub Sawicki <jakub.sawicki@slkt.pl>
 */
class AnnotationDriver extends DoctrineAnnotationDriver
{
    const ANNOTATION_DOCUMENT = 'Doctrine\Solr\Annotations\Document';
    const ANNOTATION_OPERATION = 'Doctrine\Solr\Annotations\Operation';
    const ANNOTATION_FIELD = 'Doctrine\Solr\Annotations\Field';
    const ANNOTATION_ID = 'Doctrine\Solr\Annotations\Id';
    const ANNOTATION_MAPPING = 'Doctrine\Solr\Annotations\Mapping';

    /**
* @return array
*/
    public static function getClassAnnotationClasses()
    {
        return array(
            self::ANNOTATION_DOCUMENT,
            self::ANNOTATION_MAPPING
        );
    }

    /**
    * @return array
    */
    public static function getPropertyAnnotationClasses()
    {
        return array(
            self::ANNOTATION_FIELD,
            self::ANNOTATION_ID
        );
    }

    /**
    * @var \Doctrine\Common\Annotations\AnnotationReader
    */
    private $reader;

    /**
    * @param AnnotationReader $reader
    */
    public function __construct(AnnotationReader $reader)
    {
        $this->reader = $reader;
    }

    /**
    * @param \ReflectionClass $class
    *
    * @throws \LogicException
    * @return ClassMetadata
    */
    public function loadMetadataForClass(\ReflectionClass $class)
    {
        $classMetadata = new ClassMetadata($class->getName());

        /** @var Document $annotation */
        $annotation = $this->reader->getClassAnnotation(
            $classMetadata->reflection,
            self::ANNOTATION_DOCUMENT
        );

        if (null === $annotation) {
            return null;
        }

        $this->setDocumentDataToClassMetadata($annotation, $classMetadata);

        /** @var Mapping $annotation */
        $annotation = $this->reader->getClassAnnotation(
            $classMetadata->reflection,
            self::ANNOTATION_MAPPING
        );

        if (null !== $annotation) {
            $classMetadata->mappingTable = $annotation->getMappings();
        } else {
            $classMetadata->mappingTable = Mapping::getDefaultMapping();
        }

        $hasIdField = false;

        foreach ($class->getProperties() as $reflectionProperty) {
            $propertyMetadata = new PropertyMetadata($class->getName(), $reflectionProperty->getName());

            /** @var Id $annotation */
            $annotation = $this->reader->getPropertyAnnotation(
                $reflectionProperty,
                self::ANNOTATION_ID
            );

            if (null !== $annotation) {
                $classMetadata->id = $annotation->name;
                $classMetadata->idPropertyAccess = $annotation->propertyAccess;

                if (true === $hasIdField) {
                    $message = "Duplicate Id field declaration for class '%s' found.";
                    throw new \LogicException(sprintf($message, $classMetadata->reflection->getName()));
                }
                $hasIdField = true;

                continue;
            }

            /** @var Field $annotation */
            $annotation = $this->reader->getPropertyAnnotation(
                $reflectionProperty,
                self::ANNOTATION_FIELD
            );

            if (null === $annotation) {
                continue;
            }

            $propertyMetadata->boost = $annotation->boost;
            $propertyMetadata->multi = $annotation->isMultiValuedType($annotation->type);
            $propertyMetadata->type = $annotation->type;
            $propertyMetadata->propertyAccess = $annotation->propertyAccess;

            $mapping = array($annotation->type => $classMetadata->mappingTable[$annotation->type]);
            $name = (null !== $annotation->name) ? $annotation->name : $reflectionProperty->getName();

            $propertyMetadata->fieldName = $annotation->getFieldName($mapping, $name);

            $classMetadata->addPropertyMetadata($propertyMetadata);
        }

        if (false === $hasIdField) {
            $message = "The class '%s' has a Solarium Document Declaration, but no Id field.";

            throw new \LogicException(sprintf($message, $classMetadata->reflection->getName()));
        }

        return $classMetadata;
    }

    /**
    * @param Document $documentAnnotation
    * @param ClassMetadata $classMetadata
    */
    private function setDocumentDataToClassMetadata(Document $documentAnnotation, ClassMetadata $classMetadata)
    {
        /** @var Operation $operation */
        foreach ($documentAnnotation->operations as $type => $operation) {
            $classMetadata->operations[$type] = $operation->service;
            $classMetadata->endpoints[$type] = $operation->endpoint;
        }

        $classMetadata->boost = $documentAnnotation->boost;
    }

    /**
     * Registers Annotations namespace for bootstrapping.
     */
    public static function registerAnnotationClasses()
    {
        // directory must match this file directory
        AnnotationRegistry::registerAutoloadNamespace("Doctrine\\Solr\\Mapping\\Annotations", __DIR__.'/../../../../');
    }
}
