<?php
namespace drsdre\HelloCash\Facades;

use Illuminate\Support\Facades\Facade;

class HelloCash extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'hellocash';
    }
}
