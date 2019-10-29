<?php
namespace Core\Model;

class Entity
{
    public function objectToArray($object)
    {
        $test = \get_class_methods($object);
        $i = 0;
        foreach ($test as $value) {
            if (strpos($test[$i], 'get') !== false) {
                $key =  strpos($test[$i], 'get') + 3;
                $key = substr($test[$i], $key, strlen($test[$i]));
                $array[lcfirst($key)] = $object->$value();
            }
            $i++;
        }
        return $array;
    }
}
