<?php
namespace Core\Controller\Helpers;

use Core\Model\Entity;

class GettersController
{
    static public function gettersToAssocArray(Entity $entity): array
    {
        $methods = \get_class_methods($entity);
        foreach ($methods as $value) {
            /* if "get" is fount in $value expected 'getId' */
            if (strpos($value, 'get') !== false) {
                $key = substr($value, 3, strlen($value)); // expected 'Id'
                /* Build the new array */
                $array[lcfirst($key)] = $entity->$value(); // $array['id'] = Core\Model\Entity->getId();
            }
        }
        return $array;
    }
}
