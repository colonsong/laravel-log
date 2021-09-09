<?php
namespace Colin\Log;

use Illuminate\Support\Facades\Facade;

class LogSplitFacade extends Facade {

    protected static function getFacadeAccessor()
    {
        return 'logsplit';
    }

}
