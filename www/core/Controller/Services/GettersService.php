<?php

namespace Core\Controller\Services;

class GettersService
{
    static public function gettersToAssocArray($entity): ?array
    {
        $methods = \get_class_methods($entity);
        if (is_array($methods)) {
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
        return null;
    }
}
