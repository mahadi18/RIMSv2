@extends('case-profile')
@section('content')



<div class="profile-menu">
    <?php //dd($litigation); ?>
    <ul>
        <li><?php //dd($litigation); ?>
            <?php if($litigation->status=='open') { ?>
            @if( isset($litigation->id) )
            <a class="detail" href="/cases/{{$litigation->id}}/dashboard"><i class="fa fa-bars"></i>Case Status</a>
            @endif
            <?php }
            else {
            ?>
                <form action="/cases/{{$litigation->id}}/change_status" method="post">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="status" value="open">
                    <button type="submit" class="detail">Activate Case</button>
                </form>
            <?php } ?>
        </li>
        <li><a href="/cases/{{$litigation->id}}/case-profile">Case Timeline</a></li>
        <li class="active"><a href="/cases/{{$litigation->id}}/full-profile">Full Profile</a></li>
        <li><a href="/cases/{{$litigation->id}}/take-over">Survivor Takenover</a></li>
        <li><a href="/cases/{{$litigation->id}}/document-archive">Document Archive</a></li>
        <li><a href="/cases/{{$litigation->id}}/change-log">Update Log</a></li>
    </ul>
</div>
<section class="panel">
    <div class="panel-body">
        <div class="basic-information">
            <h2>Basic Information</h2>
            <div class="print">
                <a target="_blank" class="btn btn-success" href="/cases/print/fullprofile/{{$litigation->id}}"><span class="glyphicon glyphicon-print" aria-hidden="true"></span> Print</a>
            </div>
            <div class="row">
                <label class="col-lg-3">
                    Case ID
                </label>
                <span class="col-lg-9">{{$litigation->case_id}}</span>
            </div>
            <div class="row">
                <label class="col-lg-3">
                    Full Name
                </label>
                <span class="col-lg-9">
                    @if(isset($litigation->full_name))
                    {{$litigation->full_name}}
                    @endif
                </span>
            </div>
            <div class="row">
                <label class="col-lg-3">
                    Photo(s)
                </label>
                <span class="col-lg-9">
                    <!--<img style="" alt="" src="{!! (!empty(get_victim_attachment('Victim Personal Image', $litigation->id)['file_path'])) ? get_victim_attachment('Victim Personal Image', $litigation->id)['file_path'] : '/uploads/-text.png' !!}"/>-->
                    <div class="profile_photos">
                        <?php echo get_latest_attachments_of_victim('Victim Personal Image', $litigation->id); ?>
                    </div>
                </span>
            </div>
            <div class="row">
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Age
                        </label>
                        <span class="col-lg-6">
                            @if(isset($physical->age_year_part) 
                            || isset($physical->age_month_part))
                            {{$physical->age_year_part}} years {{$physical->age_month_part}} months
                            @elseif(isset($litigation->age_year_part) 
                            || isset($litigation->age_month_part))
                            {{$litigation->age_year_part}} years {{$litigation->age_month_part}} months
                            @else
                            Not Determined
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Nationality
                        </label>
                        <span class="col-lg-6">
                            <!-- < ?php var_dump($litigation->country($physical->nationality)); die; ?> -->
                            @if(isset($litigation->country) && $physical->nationality != 0)
                            {{$litigation->country($physical->nationality)}}
                            @else
                            {{$litigation->country($litigation->nationality)}}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Father's Name
                        </label>
                        <span class="col-lg-6">
                            @if( isset($physical->father_name) )
                            {{$physical->father_name}} 
                            @elseif(isset($litigation->father_name))
                            {{$litigation->father_name}}
                            @else
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Religion
                        </label>
                        <span class="col-lg-6">
                            {{$litigation->religion}}
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Mother's Name
                        </label>
                        <span class="col-lg-6">
                            @if( isset($physical->mother_name) )
                            {{$physical->mother_name}} 
                            @elseif(isset($litigation->mother_name))
                            {{$litigation->mother_name}}
                            @else
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Educational Qualification
                        </label>
                        <span class="col-lg-6">
                            {{$litigation->education}}
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Guardian Name
                        </label>
                        <span class="col-lg-6">
                            @if( isset($physical->father_name) )
                            {{$physical->father_name}} 
                            @elseif(isset($litigation->father_name))
                            {{$litigation->father_name}}
                            @else
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Marital Status
                        </label>
                        <span class="col-lg-6">
                            @if( isset($physical->marital_status) )
                            {{$physical->marital_status == 'S' ? 'Single' : 'Married' }}
                            @else
                            {{$litigation->marital_status == 'S' ? 'Single' : 'Married' }}
                            @endif
                        </span>
                    </div>
                </div>
                <div class="col-lg-offset-6 col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">
                            Spouse Name
                        </label>
                        <span class="col-lg-6">
                            @if( isset($physical->spouse_name) )
                            {{$physical->spouse_name}}
                            @else
                            {{$litigation->spouse_name}}
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
        <div class="address">
            <h2>
                Address
            </h2>
            <?php //dd($litigation); ?>
            <div class="row">
                @if( isset( $litigation->addresses ) )
                @foreach ($litigation->addresses as $address)
                <div class="col-lg-6">
                    <div class="{{$address->present_address}} area">
                        <h3>{{str_replace('_', ' ', $address->title)}} 11</h3>
                        <div class="content">
                            <div class="row">
                                <label class="col-lg-6">C/O</label>
                                <span class="col-lg-6">{{$address->care_of}}</span>
                            </div>
                            <div class="row">
                                <label class="col-lg-6">Relation</label>
                                <span class="col-lg-6">{{$address->relation_with_survivor}}</span>
                            </div>
                            <div class="row">
                                <label class="col-lg-6">Address</label>
                                <span class="col-lg-6">
                                    <p>{{$address->address_line_1}}</p>
                                    <p>{{$address->address_line_2}}</p>
                                </span>
                            </div>
                            <div class="row">
                                <label class="col-lg-6">Post Code</label>
                                <span class="col-lg-6">{{$address->postal_code}}</span>
                            </div>
                            <div class="row">
                                <label class="col-lg-6">Contact Number</label>
                                <span class="col-lg-6">{{$address->contact_number}}</span>
                            </div>
                        </div>
                    </div>
                </div>

                @endforeach
                @endif
            </div>
        </div>
        <div class="physical-description">
            <h2>Physical Description</h2>
            <div class="row">
                <div class="col-lg-6">
                    <div class="row">
                        <label class="col-lg-6">Height</label>
                        <span class="col-lg-6">
                             @if(isset($information['ngohir']->height_ft_part))
                            {!! $information['ngohir']->height_ft_part !!} Foot {!! $information['ngohir']->height_in_part !!} Inch
                            @endif
                        </span>
                    </div>
                    <div class="row">
                        <label class="col-lg-6">Gender</label>
                        <span class="col-lg-6">
                            @if ($litigation->sex === 'F')
                                Female
                            @elseif ($litigation->sex === 'M')
                                Male
                            @else
                                Other
                            @endif
                        </span>
                    </div>
					@if($litigation->sex === 'F')
                    <div class="row">
                        <label class="col-lg-6">Pregnant</label>
                        <span class="col-lg-6">
                            <?php
                            if($litigation->pregnancy==1){
                                echo "Yes";
                            }
                            else
                            {
                                echo "No";
                            }
                            ?>
                        </span>
                    </div>
					@endif
                    <div class="row">
                        <label class="col-lg-6">Accompanied by Child</label>
                        <span class="col-lg-6">
							@if($physical != '')
                                @if( $physical->accompanying_with_survivor == 0 ) NO
                                @else YES
                                @endif
                            @endif
							
                        </span>
                    </div>
                    <div class="row">
                        <label class="col-lg-6">Substance Abuse</label>
                        <span class="col-lg-6">
                            @if(isset($information['ngohir']->abuse))
                            {!! $information['ngohir']->if_yes_type !!}
                            @endif                           
                        </span>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="distinguishables">
                        <h3> Distinguished Features </h3>
                        <div class="row">
                            <label class="col-lg-6">Birth Mark</label>
                            <span class="col-lg-6">
                                @if(isset($information['ngohir']->birth_mark))
                                {!! $information['ngohir']->birth_mark !!}
                                @endif                                
                            </span>
                        </div>
                        <div class="row">
                            <label class="col-lg-6">Complexion</label>
                            <span class="col-lg-6">
                            @if(isset($information['ngohir']->complexion))
                                {!! $information['ngohir']->complexion !!}
                            @endif                               
                            </span>
                        </div>

                    </div>
                </div>
            </div>
        </div>

        <div class="clearfix">&nbsp;</div>
    </div>
</section>


@endsection