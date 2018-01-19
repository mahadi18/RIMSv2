@extends('full-content')

@section('content')


<div class="row">
    <div class="col-lg-9 col-lg-offset-3" style="padding-left: 0px; padding-right: 0px">
        <div class="case-banner-info">
            <div class="row">
                <div class="col-lg-5 name-country">
                    <div class="thumb">
                        <a  href="/cases/{{$litigation->id}}/case-profile"> <img style="" alt="" src="{!! (!empty(get_victim_attachment('Victim Personal Image', $litigation->id)['file_path'])) ? get_victim_attachment('Victim Personal Image', $litigation->id)['file_path'] : '/uploads/-text.png' !!}"/></a>
                    </div>
                    <h4><a style="color: #333333" href="/cases/{{$litigation->id}}/case-profile">{{$litigation->name_during_rescue}}</a></h4>
                    <p>Nationality: @if(isset($physical) && $physical->nationality != 0)
                            {{$litigation->country($physical->nationality)}}
                            @else
                            {{$litigation->country($litigation->nationality)}}
                            @endif</p>
                    <p style="margin-left: 20px;">Country: @if(isset($physical) && $physical->nationality != 0)
                            {{$litigation->country($physical->nationality)}}
                            @else
                            {{$litigation->country($litigation->nationality)}}
                            @endif</p>
                </div>
                <div class="col-lg-3 case-id">
                    Case ID: {{$litigation->case_id}}
                </div>
                <div class="col-lg-4 gender">
                    <p>Gender: <?php echo ($litigation->sex) == 'M' ? 'Male' : 'Female';?></p>
                    <p>Age: <?php
                        if(isset($litigation->date_of_birth)) {
                            list($year, $month) = calculate_age($litigation->date_of_birth);
                            echo $year . ' years, ' . $month . ' months';
                        }
                        elseif(isset($litigation->age_year_part)) {
                            echo $litigation->age_year_part. ' years, ' . $litigation->age_month_part . ' months';;
                        }
                        else {
                            echo "Not Determined";
                        }

                        ?></p>
                </div>
            </div>
        </div>
    </div>
</div>
<?php //dd($organizations); ?>
<div class="row">
    <div class="col-md-3 narrow">
        <header class="panel-heading tab-bg-dark-navy-green ">

            <ul class="nav nav-tabs cases">
                <?php
                foreach ($tasks as $key => $task) {
                    $icon = '';
                    $active_class = '';
                    $class = '';
                    if ($task->task_status_id == 1) {
                        $class = 'new';
                        $icon = '<i class="fa fa-circle-o"></i>';
                    }
                    if ($task->task_status_id == 2) {
                        $class = 'progress';
                        $icon = '<i class="fa fa-repeat"></i>';
                    }
                    if ($task->task_status_id == 3) {
                        $class = 'skip';
                        $icon = '<i class="fa fa-chain-broken"></i>';
                    }
                    if ($task->task_status_id == 4) {
                        $class = 'complete';
                        $icon = '<i class="fa fa-check-square-o"></i>';
                    }
                    ?>

                    <li class="">
                        <?php echo $icon; ?><a
                            href="/cases/<?php echo $litigation->id; ?>?tid=<?php echo $task->id; ?>"><?php echo $task->title ?></a>
                    </li>
                <?php
                }
                ?>
            </ul>
        </header>
    </div>


    <div class="col-md-9 case-accessibility">
        <h1 style="margin-top: 20px">Case Contributor Management</h1>
        @if (Session::has('message'))
        <div class="message success">
            <div class="alert alert-success">
                {{Session::get('message')}}
            </div>
        </div>
        @endif
        
        <!-- <div id="select_country" class="col-md-3">
            {!! Form::label('survivor_address_country', 'Country:', ['class' => 'control-label']) !!}

            {!! Form::select('survivor_address_country[]', get_countries_list(), '$address->country', ['id' => 'select_country', 'class' => 'form-control m-bot15']) !!}

        </div> -->

        <div class="col-lg-12">
                    <strong>Organizations' Name</strong>
                    <br>
        </div>
        <div class="spacer-top col-lg-9">
            <form class="assign" action="/cases/{{$litigation->id }}/accessibility" method="post">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">

                <div class="row spacer-top">
                    <div class="col-lg-12" id="select_div">
                        <?php
                        $permitted_organizations = array();
                        //dd($organizations);
                        ?>


                        <select name="organization[]" class="multi-select" multiple="" id="my_multi_select3">
                            @foreach($organizations as $organization) {

                            <?php

                                    $selected = '';
                                    if ($litigation->organizations()->get()->toArray()) {
                                        $permitted_organizations = $litigation->organizations()->get()->toArray();
                                        foreach ($permitted_organizations as $permitted_organization) {
                                            if ($organization->id == $permitted_organization['id']) {
                                                $selected = 'selected';
                                            }
                                        }

                                    }
                            if($organization->id != Auth::user()->organization_id)
                            {
                            ?>
                            <option value="{{$organization->id}}" <?php echo $selected; ?> >
                                @if(isset($organization->name))
                                    {{$organization->name}},
                                @endif 

                                @if(isset($organization->org_type))
                                    {{$organization->org_type($organization->org_type)}}, 
                                @endif
                                
                                @if(isset($organization->district->name))
                                    {{$organization->district->name}}, 
                                @endif
                                
                                @if(true) {{-- no condition needed here --}}
                                    {{$organization->country_name($organization->country) }}
                                @endif
                            </option>
                            
                            <?php 
                            } 
                            ?>
                            <?php //dd($tasks); ?>
                            @endforeach
                        </select>

                    </div>

                </div>

                <!-- <div class="row">
                    <div class="col-lg-12" id="select_div">
                        <select id="dummy">
                            <option>Select country first</option>
                        </select>
                    </div>
                </div>
 -->
                <div class="row spacer-top">
                    <div class="col-lg-12">
                        <button class="btn btn-success" type="submit" style="float: right;">Save</button>
                    </div>
            </form>


        </div>
    </div>

</div>

<!-- <script type="text/javascript">
    
    $("#select_country").change(function() 
    {

        var countryID = $('#select_country').find(":selected").val();
        console.log(countryID);
        //alert(countryID);

        $.ajax({
            type: "GET",
            url: '/find/organizations/country?countryID='+countryID,
            data: "",
            success: function (html) {
                //alert(html);
                $('#dummy').html(html);

            }
                       
        });
    });

</script> -->


@endsection