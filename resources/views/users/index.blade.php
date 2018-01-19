@extends('layout')

@section('content')
    @if(Session::has('message'))
    <br>
        <div class="alert alert-info">
            {{ Session::get('message') }}
        </div>
    @endif
    
    <div class="page-header">
        @if($organization_id>0)
        <h1>Users of <strong>{{ $organization->name }}</strong></h1>
        @else
        <h1>Users </h1>
        @endif
    </div>


    <div class="row">
        <div class="col-md-12">
            @if($organization_id>0)
            <a class="btn btn-success" 
            href="{{ route('users.create') }}?id={{$organization_id}}$&&name={{$organization->name}}&&type={{$organization->org_type($organization->org_type)}}&&district={{$organization->district_name($organization->district_id)}}&&country={{$organization->country_name($organization->country)}}">Create</a>
            @else
            <a class="btn btn-success" 
            href="{{ route('users.create') }}">Create</a>
            @endif
           <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>NAME</th>
                        <th style="width: 20%">DESCRIPTION</th>
                        <th style="width: 20%">Last Login</th>
                        <th class="text-right">OPTIONS</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($users as $user)
                <tr>
                    <td>{{$user->id}}</td>
                    <td>{{$user->name}}</td>
                    <td>{{$user->email}}</td>
                    <td style="width: 20%">
                        <?php
                            if($user->last_login!='0000-00-00 00:00:00')
                            echo \Carbon\Carbon::createFromTimeStamp(strtotime($user->last_login))->diffForHumans();
                            else
                            echo "Still not login into the system";    
                        ?>

                    </td>
                    <td class="text-right">
                        <a class="btn btn-primary" href="{{ route('users.show', $user->id) }}">View</a>
                        <a class="btn btn-warning " href="{{ route('users.edit', $user->id) }}">Edit</a>
                        <?php
                        $msg = $user->status==1? 'Deactivate':'*Activate*';

                        ?>
                        <form action="/activate-deactivate/{{$user->id}}" method="GET" style="display: inline;" onsubmit="if(confirm('Are you sure?')) { return true } else {return false };">
                            <!-- <input type="hidden" name="_method" value="PUT">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}"> -->
                            <!-- <input type="hidden" name="status" value="< ?php echo $user->status = = 1 ? '0' : '1' ?>"> -->
                            <button class="btn btn-danger" style="background-color: #E9EAED; color: #EC6459" type="submit"><?php echo $msg ?></button>
                        </form>
                    </td>
                </tr>

                @endforeach

                </tbody>
            </table>


        </div>
    </div>

    <div class="paginagor"> <?php echo $users->render(); ?></div>


@endsection