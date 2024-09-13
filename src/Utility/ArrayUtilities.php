<?php 

namespace App\Utility;



class ArrayUtilities
{
 
  /**
     * Convert camelCase keys to snake_case keys in an array.
     *
     * @param array $inputArray The input array with camelCase keys.
     * @return array The output array with snake_case keys.
     */
    public static function convertCamelToSnake(array $inputArray): array
    {
        $outputArray = [];

        foreach ($inputArray as $key => $value) {
            $snakeKey = strtolower(preg_replace('/[A-Z]/', '_$0', $key));
            $outputArray[$snakeKey] = $value;
        }

        return $outputArray;
    }

}