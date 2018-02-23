<?php namespace Helix\Models;

use Illuminate\Database\Eloquent\Model;
use Helix\Models\Attribute;
//Project related details
class Project extends Model
{
	protected $table = 'exploration.projects';
    protected $primaryKey = 'project_id';
    protected $fillable = [
        'project_id',
        'project_title',
        'project_url',
        'project_begin_date',
        'project_end_date',
        'abstract',
        'is_publishable'
    ];
    public $incrementing = false;

    // This is used to keep track of related search terms
    public $relatedSearchTerms;

    public function image() {
      return $this->hasOne('Helix\Models\Image', 'imageable_id', 'project_id');
    }

    public function pi()
    {
        return $this->hasOne('Helix\Models\Person', 'user_id', 'pi_members_id');
    }

    public function allMembers()
    {
      return $this->belongsToMany('Helix\Models\Person', 'nemo.memberships', 'parent_entities_id', 'individuals_id')->withPivot('role_position');
    }

    public function members()
    {
    	return $this->belongsToMany('Helix\Models\Person', 'nemo.memberships', 'parent_entities_id', 'individuals_id')->withPivot('role_position')->wherePivot('role_position','!=','Lead Principal Investigator');
    }

    public function authorities()
    {
      $authorityRoles = [
        'Lead Principal Investigator',
        // 'Principal Investigator',
        'Co-Principal Investigator',
        // 'Investigator'
      ];

      return $this->belongsToMany('Helix\Models\Person', 'nemo.memberships', 'parent_entities_id', 'individuals_id')
                  ->whereIn('role_position', $authorityRoles);
    }

    // public function pi()
    // {
    //   $investigator_positions = [
    //     'Lead Principal Investigator',
    //     'Principal Investigator',
    //     'Co-Principal Investigator',
    //     'Investigator'
    //   ];

    //   return $this->belongsToMany('Helix\Models\Person', 'nemo.memberships', 'parent_entities_id', 'individuals_id')
    //                 ->withPivot('role_position')
    //                 ->wherePivotIn('role_position', $investigator_positions)
    //                 ->orderBy('role_position', 'ASC');
    // }

    public function researchers()
    {
      $researcher_positions = [
        'Postdoctoral Research Associate',
        'Graduate Research Assistant',
        'Project Manager',
        'Undergraduate Research Assistant',
        'Project Manager'
      ];

      return $this->belongsToMany('Helix\Models\Person', 'nemo.memberships', 'parent_entities_id', 'individuals_id')
                    ->withPivot('role_position')
                    ->wherePivotIn('role_position', $researcher_positions);
    }

    public function award()
    {
        return $this->hasMany('Helix\Models\Award');
    }

    public function interests()
    {
      return $this->belongsToMany('Helix\Models\Research', 'fresco.expertise_entity', 'entities_id', 'expertise_id');
    }

    public function department()
    {
      return $this->belongsTo('Helix\Models\Departments');
    }

    //One project has one set of helix-related attributes.
    public function attributes(){
        return $this->hasOne('Helix\Models\Attribute');
    }

    /**
     * This returns the YouTube link associated with a given project
     * We may want to change things to a hasMany relationship in the future
     *
     * @return Laravel query builder
     */
    public function link() {
        return $relationship = $this->hasOne('Helix\Models\Link', 'entity_id', 'project_id');
    }

    public function isFeatured(){
      return $this->attributes()->is_featured;
    }

    /**
     * RELATES a project with its purpose
     *
     * @return
     */
    public function purpose(){

      return $this->hasManyThrough(
          $relatedTo = 'Helix\Models\Purpose',
          $through = 'Helix\Models\Attribute',
          $lastKey = 'purpose_name',
          $midKey = 'system_name',
          $localKey = 'project_id'
          );
    }

    public function policies()
    {
      return $this->hasMany('Helix\Models\ProjectPolicy');
    }

    public function invitations()
    {
      return $this->hasMany('Helix\Models\Invitation');
    }

    public function visibility()
    {
      return $this->hasOne('Helix\Models\ProjectPolicy')->where('policy_type', 'visibility');
    }

    public function getPolicyType()
    {
      if(isset($this->visibility) && count($this->policies))
      {
        $policies = [
          'visibility' => $this->visibility->policy,
          'invitation' => $this->policies()->where('policy_type', 'invitation')->first()->policy,
          'approval'   => $this->policies()->where('policy_type', 'approval')->first()->policy
        ];

        $projectTypes = [
           'open'          => ['visibility' => 'public', 'invitation' => 'self', 'approval' => 'self'],
           'showcase'      => ['visibility' => 'public', 'invitation' => 'self', 'approval' => 'pi/copi'],
           'institutional' => ['visibility' => 'internal', 'invitation' => 'self', 'approval' => 'pi/copi'],
           'stealth'       => ['visibility' => 'private', 'invitation' => 'pi/copi', 'approval' => 'pi/copi']
       ];

        return array_search($policies, $projectTypes);
      }

      return '';
    }

   public function getSelectedPolicies($project_type, $key = null)
   {
       $projectTypes = [
           'open'          => ['visibility' => 'public', 'invitation' => 'self', 'approval' => 'self'],
           'showcase'      => ['visibility' => 'public', 'invitation' => 'self', 'approval' => 'pi/copi'],
           'institutional' => ['visibility' => 'internal', 'invitation' => 'self', 'approval' => 'pi/copi'],
           'stealth'       => ['visibility' => 'private', 'invitation' => 'pi/copi', 'approval' => 'pi/copi']
       ];

       if($key == null){
           return $projectTypes[$project_type];
       }
       return array_search($project_type, $projectTypes);
   }

  public function scopeWithInterestsLike($query, $searchStr) {
        return $query->with('interests')
            ->whereHas('interests', function($q) use($searchStr){
                $q->where("title", $searchStr);
            });
  }
    /**
     * Queries Projects that have any of the given Research interests by ID
     * @param builder $query        - (implied) The scope query to build off of
     * @param array   $interestIds  - An array of primary keys for the Research model.
     * @return builder
     */
    public function scopeWithResearchInterestsIn($query, $interestIds) {
        // Get an array of primary keys.
        $query = $query->with('interests')
            ->whereHas('interests',function($interestQuery) use ($interestIds) {
                return $interestQuery->whereIn('attribute_id',$interestIds);
            });
        return $query;
    }

   /*
   |-----------------------------------------
   | Visibility Policies
   |-----------------------------------------
   */
   public function isPrivate()
   {
      return $this->visibility->policy == 'private';
   }

   public function isInternal()
   {
      return $this->visibility->policy == 'internal';
   }

   /*
   |-----------------------------------------
   | Invitation Policies
   |-----------------------------------------
   */
   public function isJoinable()
   {
      $invitation = $this->policies()->where('policy_type', 'invitation')->first();
      $approval   = $this->policies()->where('policy_type', 'approval')->first();
      return $invitation->policy == 'self' && $approval->policy == 'self';
   }

   public function isRequestable()
   {
      $invitation = $this->policies()->where('policy_type', 'invitation')->first();
      $approval   = $this->policies()->where('policy_type', 'approval')->first();

      return $invitation->policy == 'pi/copi' && $approval->policy == 'pi/copi';
    }

   public function awardTotal()
   {
      $awards = array_column($this->award->toArray(), 'award_amount');
      return number_format(array_sum($awards));
   }

   public function sponsors()
   {
      return array_unique($this->award->pluck('sponsor')->toArray());
   }

   public function pendingInvitations()
   {
    return $this->invitations()->whereNull('updated_at');
   }
      /*
   |-----------------------------------------
   | Images
   |-----------------------------------------
   */



}
