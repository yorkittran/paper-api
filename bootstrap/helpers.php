<?php

if ( ! function_exists('constants'))
{
    function constants($key)
    {
       return config('constants.' . $key);
    }
}
