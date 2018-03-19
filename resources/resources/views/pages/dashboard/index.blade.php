@extends('layouts.master')

@section('content')
<div class="section">
  <div class="container">

    <div class="row">

      <div class="col-sm-12">
      {{-- <br> --}}
      <a class="btn btn-default" style="float:right;" href="{{ url('project/step-1') }}">Add a New Project</a>
        <h1 class="type--header type--thin">Scholarship Dashboard</h1>
      </div>
    </div>
  
    <div class="row">
      <div class="col-lg-3">
        <ul class="nav">
            <li class="nav__item nav__item--{{ Request::is('admin/dashboard') ? 'active' : ''}}">
              <a title="My Projects" href="{{ url('admin/dashboard') }}" class="nav__link">My Projects</a>
            </li>
            <li class="nav__item nav__item--{{ Request::is('admin/dashboard/invitations') ? 'active' : ''}}">
              <a title="Pending Invitations" href="{{ url('admin/dashboard/invitations') }}" class="nav__link" >Pending Invitations 
                  @if($pendingInvitations > 0)
                  <p class="tag tag--danger">{{ $pendingInvitations }}</p>
                  @endif
              </a>
            </li>
        </ul>
      </div>
      <div class="col-lg-9">
        @if(Request::is('admin/dashboard'))
          @include('pages.project.partials.delete-modal')
          @include('pages.dashboard.projects',['projectDashboard' => $projects])
        @endif
        @if(Request::is('admin/dashboard/invitations'))
          @include('pages.dashboard.invitations')
        @endif
      </div>
    </div>
  </div>
</div>
    {!! Html::script('js/metaphor.js') !!}
    <script>
      $('.delete-modal-btn').on('click', function(){
        $('#deleteModal form').attr({
          action: $('html').data('url') + '/project/' + $(this).data('id') + '/delete',
          method: 'GET'
        });
        $('#deleteModal .modal__content h3').text($(this).data('title'));
      })
    </script>
@stop