<?php

namespace Powerhouse\Gate\Bridges;

use Powerhouse\Gate\Http\Request;

abstract class DataTrimmer
{

    /**
     * Perform an operation.
     * 
     * @param  Powerhouse\Gate\Http\Request  $request
     * @return void
     */
    public function operation(Request $request)
    {
        foreach ($_GET as $name => $value)
            $request->change($name, trim($value), 'GET');

        foreach ($_POST as $name => $value)
            $request->change($name, trim($value), 'POST');

        foreach ($request->input() as $name => $value)
            $request->change($name, trim($value), 'INPUT');
    }

}
