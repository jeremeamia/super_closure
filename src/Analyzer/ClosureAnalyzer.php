<?php namespace SuperClosure\Analyzer;

use SuperClosure\Exception\ClosureAnalysisException;

interface ClosureAnalyzer
{
    /**
     * @param \ReflectionFunction $reflection
     *
     * @return array
     * @throws ClosureAnalysisException
     */
    public function analyze(\ReflectionFunction $reflection);
}
