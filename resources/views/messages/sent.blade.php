@extends('layout')

@section('content')
    @if(Session::has('message'))
    <br>
        <div class="alert alert-info">
            {{ Session::get('message') }}
        </div>
    @endif

    <div class="page-header">
        <h1>Messages</h1>
    </div>

    <a class="btn btn-success" href="{{ route('messages.create') }}">Create</a>

    <div class="row">
        <div class="col-md-12">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>TO</th>
                        <th>SUBJECT</th>
                        <th>TIME</th>
                        <th>BODY</th>
                        <th class="text-right">OPTIONS</th>
                    </tr>
                </thead>

                <tbody>

                @foreach($messages as $message)
                <?php
                    //dd($messages);
                    $arr = message_receiver($message->id);
                    //dd($arr);
                    $rcvr = '';
                ?>
                <!-- < ?php //dd($m->name); ?> -->
                    
                <tr class="<?php if($message->last_viewed_by==0) { echo 'not-viewed'; }?>">
                
                    @foreach($arr as $m)
                        <?php 
                        $rcvr = $rcvr.$m->name.', '  ?>
                    @endforeach

                    <td>
                        {{ str_limit($rcvr, 20) }}
                    </td>
                
                    <td>{{$message->subject}}</td>
                    <td>{{ Carbon\Carbon::parse($message->created_at)->format('d M Y') }}</td>
                    <td>{{ str_limit($message->body, 50) }}</td>
                   <!-- <td>{{$message->parent_message}}</td>-->
                    <td class="text-right">
                        <a class="btn btn-primary" href="{{ route('messages.showMessage', $message->id) }}">View</a>

                    </td>
                </tr>
                    

                @endforeach

                </tbody>
            </table>

            
        </div>
        <div class="paginator"> <?php echo $messages->render(); ?></div>
    </div>


@endsection