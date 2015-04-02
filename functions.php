<?php
function getPrice($find)
{
    $books = array("java"=>299, "c"=>248, "php"=>267);
    
    foreach($books as $book=>$price)
    {
        if($book == $find)
        {
            return $price;
            break;
        }
    }
}
?>