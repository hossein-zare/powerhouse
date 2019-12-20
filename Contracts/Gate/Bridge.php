<?php

namespace Contracts\Helpers\Traits;

use Powerhouse\Gate\Http\Request;

interface MagicCallInterface
{

    public function operation(Request $request);

}