<?php namespace SuperClosure;

use SuperClosure\Analyzer\AstAnalyzer as DefaultAnalyzer;
use SuperClosure\Analyzer\ClosureAnalyzer;

class Serializer
{
    const OPT_ANALYZER = 'analyzer';
    const OPT_INC_BINDING = 'include_binding';

    /** @var array */
    protected static $defaultOptions = [
        self::OPT_ANALYZER    => null,
        self::OPT_INC_BINDING => true,
    ];

    /** @var array */
    private $options;

    /** @var ClosureAnalyzer */
    private $analyzer;

    /**
     * @param array $options
     *
     * @throws \InvalidArgumentException
     */
    public function __construct(array $options = [])
    {
        $this->options = $options + self::$defaultOptions;

        $this->analyzer = $this->options[self::OPT_ANALYZER] ?: new DefaultAnalyzer;
        if (!$this->analyzer instanceof ClosureAnalyzer) {
            throw new \InvalidArgumentException(
                'The "analyzer" option must be an instance of ClosureAnalyzer.'
            );
        }
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
     *
     * @return array
     */
    public function analyze(\Closure $closure)
    {
        $reflection = new \ReflectionFunction($closure);
        $data = $this->analyzer->analyze($reflection);
        $data['binding'] = $this->getClosureBinding($reflection);

        return $data;
    }

    /**
     * @param mixed $data
     */
    public function wrapClosures(&$data)
    {
        // Wrap any closures, and apply wrapClosures to their bound objects.
        if ($data instanceof \Closure) {
            $reflection = new \ReflectionFunction($data);
            $binding = $this->getClosureBinding($reflection);
            if ($binding && $binding['object']) {
                $this->wrapClosures($binding['object']);
                $data->bindTo($binding['object'], $binding['scope']);
            }
            $data = new SerializableClosure($data, $this);
        // Apply wrapClosures to all values in arrays.
        } elseif (is_array($data) || $data instanceof \stdClass) {
            foreach ($data as &$value) {
                $this->wrapClosures($value);
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
                    $this->wrapClosures($value);
                    $property->setValue($data, $value);
                }
            }
        }
    }

    private function getClosureBinding(\ReflectionFunction $reflection)
    {
        if (!$this->options[self::OPT_INC_BINDING]) {
            return null;
        }

        $binding = [
            'object' => $reflection->getClosureThis(),
            'scope'  => 'static'
        ];
        if ($scope = $reflection->getClosureScopeClass()) {
            $binding['scope'] = $scope->getName();
        }

        return $binding;
    }
}
