@extends('layout')

@section('content')
    <div class="page-header">
        <h1>CarePlans / Show </h1>
    </div>


    <div class="row">
        <div class="col-md-12">

            <form action="#">
                <div class="form-group">
                    <label for="nome">ID</label>
                    <p class="form-control-static">{{$careplan->id}}</p>
                </div>
                <div class="form-group">
                     <label for="title">TITLE</label>
                     <p class="form-control-static">{{$careplan->title}}</p>
                </div>
                    <div class="form-group">
                     <label for="description">DESCRIPTION</label>
                     <p class="form-control-static">{{$careplan->description}}</p>
                </div>


            </form>



            <a class="btn btn-default" href="{{ URL::previous() }}">Back</a>
            <a class="btn btn-warning" href="{{ route('careplans.edit', $careplan->id) }}">Edit</a>
            <form action="{{ route('careplans.destroy', $careplan->id) }}" method="POST" style="display: inline;" onsubmit="if(confirm('Delete? Are you sure?')) { return true } else {return false };"><input type="hidden" name="_method" value="DELETE"><input type="hidden" name="_token" value="{{ csrf_token() }}"> <button class="btn btn-danger" type="submit">Delete</button></form>
        </div>
    </div>


@endsection