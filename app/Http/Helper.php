<?php 
//*******************************************************
//Function Name : activeMenu
//Author : Vikas Katariya
//date : 12-07-2021
//*******************************************************

function activeMenu($uri = '') {
    $active = '';
    
    if (Request::is(Request::segment(1) . '/' . $uri . '/*') || Request::is(Request::segment(1) . '/' . $uri) || Request::is($uri)) {
        $active = 'active';
    }
    return $active;
}

//*******************************************************
//Function Name : random_code
//Author : Vikas Katariya
//date : 12-07-2021
//*******************************************************

function random_code($length)
{
  return substr(base_convert(sha1(uniqid(mt_rand())), 16, 36), 0, $length);
}

//*******************************************************
//Function Name : get_order_number
//Author : Vikas Katariya
//date : 12-07-2021
//*******************************************************

function get_order_number($id)
{
    return '#' . str_pad($id, 8, "0", STR_PAD_LEFT);
}
?>