@extends('layout')

@section('content')
    @if(Session::has('message'))
        <div class="alert alert-info">
            {{ Session::get('message') }}
        </div>
    @endif
    
    <div class="page-header">
        <h1>Action Points / Show </h1>
    </div>


    <div class="row">
        <div class="col-md-12">

            <form action="#">
                <div class="form-group">
                    <label for="nome">ID</label>
                    <p class="form-control-static">{{$actpoint->id}}</p>
                </div>
                <div class="form-group">
                     <label for="title">TITLE</label>
                     <p class="form-control-static">{{$actpoint->title}}</p>
                </div>
            </form>



            <a class="btn btn-default" href="{{ URL::previous() }}">Back</a>
            <a class="btn btn-warning" href="{{ route('actpoints.edit', $actpoint->id) }}">Edit</a>            
            <form action="{{ route('actpoints.destroy', $actpoint->id) }}" method="POST" style="display: inline;" onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };">
                <input type="hidden" name="_method" value="DELETE">
                <input type="hidden" name="_token" value="{{ csrf_token() }}"> 
                <button class="btn btn-danger" type="submit">Delete</button>
            </form>
        </div>
    </div>


@endsection