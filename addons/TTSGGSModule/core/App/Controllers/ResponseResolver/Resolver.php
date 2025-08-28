<?php

namespace ModulesGarden\TTSGGSModule\Core\App\Controllers\ResponseResolver;

use Exception;

class Resolver
{
    protected $response;

    public function __construct($response)
    {
        $this->response = $response;
    }

    public function resolve()
    {
        foreach ($this->getResolvers() as $resolver)
        {
            $resolved = $this->tryResolve($resolver);
            if ($resolved)
            {
                return $resolved;
            }
        }

        return false;

        throw new Exception('Cannot resolve response');
    }

    /**
     * Find available resolves
     * @return string[]
     */
    protected function getResolvers()
    {
        $files = scandir(__DIR__ . DIRECTORY_SEPARATOR . 'Resolvers');
        $files = array_filter($files, function($file) {
            return !in_array($file, ['.', '..']);
        });
        $files = array_map(function($file) {
            $file = str_replace('.php', '', $file);
            $file = __NAMESPACE__ . '\\Resolvers\\' . $file;

            return $file;
        }, $files);

        return $files;
    }

    /**
     * Create resolve and check in can be used
     * @param $resolverClassName
     * @return mixed|void
     */
    protected function tryResolve($resolverClassName)
    {
        $resolver = new $resolverClassName();
        if ($resolver->canResolve($this->response))
        {
            return $resolver->resolve($this->response);
        }
    }
}
