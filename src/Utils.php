<?php
/*
 Copyright (C) 2020 Rory Murphy

    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as
    published by the Free Software Foundation, either version 3 of the
    License, or (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.

    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <https://www.gnu.org/licenses/>.
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
        return preg_replace('/[^a-zA-Z0-9]+/', '-', $string);
    }

    static function string_slugify_underscored($string){
        return preg_replace('/[^a-zA-Z0-9]+/', '_', $string);
    }
}
