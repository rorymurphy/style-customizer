<?php
/*
Copyright (C) 2020 Rory Murphy
This is copyrighted software. Please see the LICENSE.TXT in the root of the project for permitted uses.
*/

namespace StyleCustomizer;

class Utils {
    static function canonicalize($address)
    {
        $address = explode('/', $address);
        $keys = array_keys($address, '..');
    
        foreach($keys as $keypos => $key)
        {
            array_splice($address, $key - ($keypos * 2 + 1), 2);
        }
    
        $address = implode('/', $address);
        $address = str_replace('./', '', $address);
        return $address;
    }
    
    static function string_slugify($string){
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '-', $string));
    }

    static function string_slugify_underscored($string){
        return strtolower(preg_replace('/[^a-zA-Z0-9]+/', '_', $string));
    }
}
