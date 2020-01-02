<?php

namespace Contracts\Gate;

use Powerhouse\Gate\Http\Request;

interface BridgeInterface
{

    public function operation(Request $request);

}