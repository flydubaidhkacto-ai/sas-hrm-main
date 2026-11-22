<?php

namespace App\Interfaces;

use Illuminate\Database\Eloquent\Collection;

interface RosterRepositoryInterface
{
    /**
     * Get all roster records.
     *
     * @return Collection
     */
    public function get(): Collection;
}
