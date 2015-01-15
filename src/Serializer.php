<?php namespace SuperClosure;

use SuperClosure\Analyzer\AstAnalyzer as DefaultAnalyzer;
use SuperClosure\Analyzer\ClosureAnalyzer;

class Serializer
{
    const RECURSION = "\0RECURSION\0";

    private static $dataToKeep = [
        'code'    => true,
        'context' => true,
        'binding' => true,
        'scope'   => true
    ];

    /** @var ClosureAnalyzer */
    private $analyzer;

    /**
     * @param ClosureAnalyzer $analyzer
     */
    public function __construct(ClosureAnalyzer $analyzer = null)
    {
        $this->analyzer = $analyzer ?: new DefaultAnalyzer;
    }

    /**
     * @param \Closure $closure
     *
     * @return string
     */
    public function serialize(\Closure $closure)
    {
        return serialize(new SerializableClosure($closure, $this));
    }

    /**
     * @param string $serialized
     *
     * @return \Closure
     */
    public function unserialize($serialized)
    {
        /** @var SerializableClosure $unserialized */
        $unserialized = unserialize($serialized);

        return $unserialized->getClosure();
    }

    /**
     * @param \Closure $closure
     * @param bool     $forSerialization
     *
     * @return \Closure
     */
    public function getClosureData(\Closure $closure, $forSerialization = false)
    {
        // Use the closure analyzer to get data about the closure.
        $data = $this->analyzer->analyze($closure);

        // If the closure data is getting retrieved solely for the purpose of
        // serializing the closure, then make some modifications to the data.
        if ($forSerialization) {
            // If there is no reference to the binding, don't serialize it.
            if (!$data['hasThis']) {
                $data['binding'] = null;
            }

            // Remove data about the closure that does not get serialized.
            $data = array_intersect_key($data, self::$dataToKeep);

            // Wrap any other closures within the context.
            foreach ($data['context'] as &$value) {
                if ($value instanceof \Closure) {
                    $value = ($value === $closure)
                        ? self::RECURSION
                        : new SerializableClosure($value, $this);
                }
            }

            // Include the serializer in the data for serialization.
            $data['serializer'] = $this;
        }

        return $data;
    }

    /**
     * @param mixed $data
     */
    public function wrapClosuresWithin(&$data)
    {
        // Wrap any closures, and apply wrapClosures to their bound objects.
        if ($data instanceof \Closure) {
            $reflection = new \ReflectionFunction($data);
            if ($binding = $reflection->getClosureThis()) {
                $this->wrapClosuresWithin($binding);
                if ($scope = $reflection->getClosureScopeClass()) {
                    $scope = $scope->getName();
                } else {
                    $scope = 'static';
                }
                $data->bindTo($binding, $scope);
            }
            $data = new SerializableClosure($data, $this->analyzer);
        // Apply wrapClosures to all values in arrays.
        } elseif (is_array($data) || $data instanceof \stdClass) {
            foreach ($data as &$value) {
                $this->wrapClosuresWithin($value);
            }
        // Apply wrapClosures() to all members of objects that don't already
        // have specific serialization handlers defined.
        } elseif (is_object($data) && !$data instanceof \Serializable) {
            $reflection = new \ReflectionObject($data);
            if (!$reflection->hasMethod('__sleep')) {
                foreach ($reflection->getProperties() as $property) {
                    if ($property->isPrivate() || $property->isProtected()) {
                        $property->setAccessible(true);
                    }
                    $value = $property->getValue($data);
                    $this->wrapClosuresWithin($value);
                    $property->setValue($data, $value);
                }
            }
        }
    }
}
