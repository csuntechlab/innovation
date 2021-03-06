<?php

declare(strict_types=1);

namespace Helix\Models;

use Illuminate\Database\Eloquent\Model;

class Award extends Model
{
    protected $table = 'awards';
    protected $primaryKey = 'award_id';
    public $incrementing = false;

    public function ProjectAbstract()
    {
        return $this->hasManyThrough('Helix\Models\ProjectAbstract', 'Helix\Models\ProposalAward', 'award_id', 'proposal_id');
    }
}
