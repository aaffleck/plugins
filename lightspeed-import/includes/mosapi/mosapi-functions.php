<?php
function xml2array($xml)

      {

          $arr = array();

          foreach ($xml->children() as $r)

         {

              $t = array();

              if (count($r->children()) == 0)

             {

                  $arr[$r->getName()] = strval($r);

             }

             else

             {

                 $arr[$r->getName()][] = xml2array($r);

             } 

         }

         return $arr;

    }
    
    
function search($array, $key, $value) 
{ 
    $results = array(); 

    if (is_array($array)) 
    { 
        if (isset($array[$key]) && $array[$key] == $value) 
            $results[] = $array; 

        foreach ($array as $subarray) 
            $results = array_merge($results, search($subarray, $key, $value)); 
    } 

    return $results; 
} 


function in_multiarray($elem, $array)
    {
        $top = sizeof($array) - 1;
        $bottom = 0;
        while($bottom <= $top)
        {
            if($array[$bottom] == $elem)
                return true;
            else 
                if(is_array($array[$bottom]))
                    if(in_multiarray($elem, ($array[$bottom])))
                        return true;
                    
            $bottom++;
        }        
        return false;
    }