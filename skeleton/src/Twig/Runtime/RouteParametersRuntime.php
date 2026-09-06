<?php

namespace App\Twig\Runtime;

use Twig\Extension\RuntimeExtensionInterface;

class RouteParametersRuntime implements RuntimeExtensionInterface
{
    public function __construct()
    {
        // Inject dependencies if needed
    }

    public function generateParameters(object $entity, array $parameters)
    {
        $response = [];
        foreach($parameters as $key => $parameter) {
            $response[$key] = $this->getPropertyValue($entity, $parameter);
        }

        return $response;
    }

    private function getPropertyValue(object $entity, string $property): mixed
    {
        $getter = 'get' . ucfirst($property);
        $isser  = 'is' . ucfirst($property);
 
        if (method_exists($entity, $getter)) {
            return $entity->{$getter}();
        }
 
        if (method_exists($entity, $isser)) {
            return $entity->{$isser}();
        }
 
        if (property_exists($entity, $property)) {
            return $entity->{$property};
        }
 
        throw new \InvalidArgumentException(sprintf(
            'Impossible de lire "%s" sur l\'entité %s : ni getter, ni isser, ni propriété publique trouvée.',
            $property,
            get_class($entity)
        ));
    }
}
