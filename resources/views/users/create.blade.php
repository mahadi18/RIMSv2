@extends('layout')

@section('content')
    <div class="page-header">
        <h1>User / Create </h1>
    </div>


    <div class="row">
        <div class="col-md-12">

            <!--<form class="form-horizontal" role="form" method="POST" action="{{ url('/users/store') }}">-->
                <form class="form-horizontal" role="form"  action="{{ route('users.store') }}" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                    <label class="col-md-4 control-label">Name</label>
                    <div class="col-md-6">
                        <input type="text" class="form-control" name="name" value="{{ old('name') }}"><span class="mandatory">*</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">E-Mail Address</label>
                    <div class="col-md-6">
                        <input type="email" class="form-control" name="email" value="{{ old('email') }}"><span class="mandatory">*</span>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 control-label">Organization</label>
                    <div class="col-md-6">
                        <select name="organization_id" class="form-control">
                            @if(isset($_GET['id']) AND isset($_GET['name']) AND isset($_GET['type']) AND isset($_GET['district']) AND isset($_GET['country']) )    
                                <?php
                                     $organization_id       = $_GET['id']; 
                                     $organization_name     = $_GET['name']; 
                                     $organization_type     = $_GET['type']; 
                                     $organization_district = $_GET['district']; 
                                     $organization_country  = $_GET['country']; 
                                 ?>
                                <option value="{{$_GET['id']}}">
                                    {{ $organization_name }},{{$organization_type}},{{$organization_district}},{{$organization_country}}
                                </option>
                            @else
                                <?php $organization_id=""; ?>
                                <option value="">select organization</option>
                            @endif

                            @foreach ($organizations as $organization)
                                @if($organization->id != $organization_id)
                                    <option value="{{$organization->id}}">
                                        {{$organization->name}}, {{$organization->org_type($organization->org_type)}}, {{$organization->district_name($organization->district_id)}}, {{$organization->country_name($organization->country)}}
                                    </option>
                                @endif
                            @endforeach

                        </select><span class="mandatory">*</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Role</label>
                    <div class="col-md-6">
                        <select name="role" class="form-control">
                            @foreach ($roles as $role)
                            <option value="{{$role->id}}" selected="3">{{$role->display_name}}</option>
                            @endforeach

                        </select><span class="mandatory">*</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Password</label>
                    <div class="col-md-6">
                        <input type="password" class="form-control" name="password">
                        <span class="mandatory">*</span>
                    </div>
                </div>

                <div class="form-group">
                    <label class="col-md-4 control-label">Confirm Password</label>
                    <div class="col-md-6">
                        <input type="password" class="form-control" name="password_confirmation"><span class="mandatory">*</span>
                    </div>
                </div>

                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Create
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>


@endsection