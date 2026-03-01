<?php

namespace Engine\Entities\Contracts;

use Engine\Values\Timestamps;

interface HasTimestamps
{
    /**
     * Get timestamps.
     *
     * @return \Engine\Values\Timestamps
     */
    public function getTimestamps(): Timestamps;
}
