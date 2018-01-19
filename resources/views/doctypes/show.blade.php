@extends('layout')

@section('content')
    <div class="page-header">
        <h1>DocTypes / Show </h1>
    </div>


    <div class="row">
        <div class="col-md-12">

            <form action="#">
                <div class="form-group">
                    <label for="nome">Id</label>
                    <p class="form-control-static">{{$doctype->id}}</p>
                </div>
                <div class="form-group">
                     <label for="title">Name</label>
                     <p class="form-control-static">{{$doctype->name}}</p>
                </div>
            </form>



            <a class="btn btn-default" href="{{ URL::previous() }}">Back</a>
            <a class="btn btn-warning" href="{{ route('doctypes.edit', $doctype->id) }}">Edit</a>

            <!-- <form action="{{ route('doctypes.destroy') }}/{{$doctype->id}}" method="DELETE" style="display: inline;" onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };"><button class="btn btn-danger" type="submit">Delete</button></form> -->
            
            <form action="{{ route('doctypes.destroy', $doctype->id) }}" method="POST" style="display: inline;" onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"> <button class="btn btn-danger" type="submit">Delete</button></form>
        </div>
    </div>


@endsection