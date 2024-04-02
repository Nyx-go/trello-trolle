<?php

namespace App\Trellotrolle\Lib;

use Symfony\Component\Routing\Loader\AttributeClassLoader;
use Symfony\Component\Routing\Route;

class AttributeRouteControllerLoader extends AttributeClassLoader
{
    /**
     * Configures the _controller default parameter of a given Route instance.
     */
    protected function configureRoute(Route $route, \ReflectionClass $class, \ReflectionMethod $method, object $annot): void
    {
        $route->setDefault('_controller', $this->toSnakeCase($class->getShortName()).'::'.$method->getName());
    }

    private function toSnakeCase($controllerName) : string {
        return ltrim(strtolower(preg_replace('/[A-Z]([A-Z](?![a-z]))*/', '_$0', $controllerName)), '_');
    }

}
