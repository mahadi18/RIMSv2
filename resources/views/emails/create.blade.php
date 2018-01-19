@extends('layout')

@section('content')
    <div class="page-header">
        <h1>Contact Admin </h1>
    </div>


    <div class="row">
        <div class="col-md-12">

            <form action="/email/send" method="POST">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="form-group">
                     <label for="title">SUBJECT</label>
                     <input type="text" name="title" class="form-control" value=""/>
                     <span class="mandatory">*</span>
                </div>

                <div class="form-group">
                     <label for="title">MESSEGE</label>
                     <span class="mandatory">*</span>
                     <textarea type="text" name="body" class="form-control" value="" style="height: 300px;"></textarea>
                     
                </div>

                

                
                
                <a class="btn btn-default" href="{{ URL::previous() }}">Back</a>
            <button class="btn btn-primary" type="submit" >Send Email</button>
            </form>
        </div>
    </div>


@endsection