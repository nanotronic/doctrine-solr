<?php
namespace Doctrine\Solr;

use Solarium\Client,
    Doctrine\Solr\Annotations\Field,
    Metadata\MetadataFactory,
    Symfony\Component\PropertyAccess\PropertyAccess;

class Builder{

    private $classMap = array(
        Field::TYPE_INT           => 'Doctrine\Solr\Converter\Type\IntegerConverter',
        Field::TYPE_INT_MULTI     => 'Doctrine\Solr\Converter\Type\IntegerConverter',
        Field::TYPE_STRING        => 'Doctrine\Solr\Converter\Type\StringConverter',
        Field::TYPE_STRING_MULTI  => 'Doctrine\Solr\Converter\Type\StringConverter',
        Field::TYPE_LONG          => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_LONG_MULTI    => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_TEXT          => 'Doctrine\Solr\Converter\Type\StringConverter',
        Field::TYPE_TEXT_MULTI    => 'Doctrine\Solr\Converter\Type\StringConverter',
        Field::TYPE_BOOLEAN       => 'Doctrine\Solr\Converter\Type\BooleanConverter',
        Field::TYPE_BOOLEAN_MULTI => 'Doctrine\Solr\Converter\Type\BooleanConverter',        
        Field::TYPE_FLOAT         => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_FLOAT_MULTI   => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_DOUBLE        => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_DOUBLE_MULTI  => 'Doctrine\Solr\Converter\Type\FloatConverter',
        Field::TYPE_DATE          => 'Doctrine\Solr\Converter\Type\DateConverter',
        Field::TYPE_DATE_MULTI    => 'Doctrine\Solr\Converter\Type\DateConverter',        
        Field::TYPE_LOCATION      => 'Doctrine\Solr\Converter\ValueConverter'
);

    private $solrClient;
    private $metadateFactory;
    private $annotationDriver;
    private $serviceManager;
    private $propertyAccessor;
    private $converterCollection;
    private $processor;
    private $doctrineListener;

    public static function create($clientConfig)
    {
        return new static($clientConfig);
    }

    public function __construct($clientConfig)
    {
        $this->solrClient = new Client($clientConfig);
        $this->annotationDriver = new MetaData\Driver\AnnotationDriver(new \Doctrine\Common\Annotations\AnnotationReader());
        $this->metadateFactory = new MetadataFactory($this->annotationDriver);
        $this->serviceManager = new Manager\SolariumServiceManager();
        $this->serviceManager->setClient($this->solrClient, 'solarium.client.default');
        $this->propertyAccessor = PropertyAccess::createPropertyAccessor();
        $this->converter = new Converter\ConverterCollection();
        $this->registerConverters();

        $this->processor = new Processor\Processor(
            $this->metadateFactory,
            $this->serviceManager,
            $this->propertyAccessor,
            $this->converter
        );

        $this->doctrineListener = new EventListener\DoctrineListener($this->processor);
    }

    public function registerConverters(){
        foreach (Field::getFieldTypes() as $field) {
            $this->converter->registerConverter($field, $this->classMap[$field]);
        }
    }

    /**
     * Gets the value of solrClient.
     *
     * @return mixed
     */
    public function getSolrClient()
    {
        return $this->solrClient;
    }
    
    /**
     * Sets the value of solrClient.
     *
     * @param mixed $solrClient the solr client
     *
     * @return self
     */
    public function setSolrClient($solrClient)
    {
        $this->solrClient = $solrClient;

        return $this;
    }

    /**
     * Gets the value of metadateFactory.
     *
     * @return mixed
     */
    public function getMetadateFactory()
    {
        return $this->metadateFactory;
    }
    
    /**
     * Sets the value of metadateFactory.
     *
     * @param mixed $metadateFactory the metadate factory
     *
     * @return self
     */
    public function setMetadateFactory($metadateFactory)
    {
        $this->metadateFactory = $metadateFactory;

        return $this;
    }

    /**
     * Gets the value of annotationDriver.
     *
     * @return mixed
     */
    public function getAnnotationDriver()
    {
        return $this->annotationDriver;
    }
    
    /**
     * Sets the value of annotationDriver.
     *
     * @param mixed $annotationDriver the annotation driver
     *
     * @return self
     */
    public function setAnnotationDriver($annotationDriver)
    {
        $this->annotationDriver = $annotationDriver;

        return $this;
    }

    /**
     * Gets the value of serviceManager.
     *
     * @return mixed
     */
    public function getServiceManager()
    {
        return $this->serviceManager;
    }
    
    /**
     * Sets the value of serviceManager.
     *
     * @param mixed $serviceManager the service manager
     *
     * @return self
     */
    public function setServiceManager($serviceManager)
    {
        $this->serviceManager = $serviceManager;

        return $this;
    }

    /**
     * Gets the value of propertyAccessor.
     *
     * @return mixed
     */
    public function getPropertyAccessor()
    {
        return $this->propertyAccessor;
    }
    
    /**
     * Sets the value of propertyAccessor.
     *
     * @param mixed $propertyAccessor the property accessor
     *
     * @return self
     */
    public function setPropertyAccessor($propertyAccessor)
    {
        $this->propertyAccessor = $propertyAccessor;

        return $this;
    }

    /**
     * Gets the value of converterCollection.
     *
     * @return mixed
     */
    public function getConverterCollection()
    {
        return $this->converterCollection;
    }
    
    /**
     * Sets the value of converterCollection.
     *
     * @param mixed $converterCollection the converter collection
     *
     * @return self
     */
    public function setConverterCollection($converterCollection)
    {
        $this->converterCollection = $converterCollection;

        return $this;
    }

    /**
     * Gets the value of processor.
     *
     * @return mixed
     */
    public function getProcessor()
    {
        return $this->processor;
    }
    
    /**
     * Sets the value of processor.
     *
     * @param mixed $processor the processor
     *
     * @return self
     */
    public function setProcessor($processor)
    {
        $this->processor = $processor;

        return $this;
    }

    /**
     * Gets the value of doctrineListener.
     *
     * @return mixed
     */
    public function getDoctrineListener()
    {
        return $this->doctrineListener;
    }
    
    /**
     * Sets the value of doctrineListener.
     *
     * @param mixed $doctrineListener the doctrine listener
     *
     * @return self
     */
    public function setDoctrineListener($doctrineListener)
    {
        $this->doctrineListener = $doctrineListener;

        return $this;
    }
}
