<?php

namespace Pinga\Tembo\Internal;

class NullLogger
{
    public function __call($name, $arguments)
    {
        // swallow everything
    }
}