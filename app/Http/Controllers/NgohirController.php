<?php namespace App\Http\Controllers;

use App\Address;
use App\Http\Requests;
use App\Http\Controllers\Controller;

use App\Litigation;
use App\Ngohir;
use Carbon\Carbon;
use Illuminate\Http\Request;
use App\Classes\Usability;
use ValidateRequests;

class NgohirController extends Controller {

	/**
	 * Display a listing of the resource.
	 *
	 * @return Response
	 */



    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('deny.admin', ['only' => ['update','store']]);
    }


	public function index()
	{
		$ngohirs = Ngohir::all();

		return view('ngohirs.index', compact('ngohirs'));
	}

	/**
	 * Show the form for creating a new resource.
	 *
	 * @return Response
	 */
	public function create($id)
	{
        $litigation = Litigation::findOrFail($id);

		return view('ngohirs.create', compact('litigation'));
	}

	/**
	 * Store a newly created resource in storage.
	 *
	 * @param Request $request
     * @param int $id
	 * @return Response
	 */

    public function store(Request $request, $id)
    {
        $btnID =  $request->input("id");

		$ngohir = new Ngohir();

        $ngohir->litigation_id  = $id;

        /** Validation START written by MAHADI */
        if($btnID == 1)
        {
            $this->validate($request, 
                [
                    'name_of_interviewer' => 'required|regex:/^[A-z ]+$/',
                    'name_of_informer'    => 'required|regex:/^[A-z ]+$/',
                    'place_of_interview'    => 'regex:/^[A-z ]+$/',
                    'survivor_informer_relation'    => 'regex:/^[A-z ]+$/',
                ]
            );

    		$date_of_interview = strtotime($request->input("date_of_interview"));
            $today = strtotime(date('d-m-Y'));
                
            if($today < $date_of_interview)
            {
                return back()->with('error', 'Date of Interview is invalid!! Please enter a valid date.');;
            }
            /* end of validation of form 1*/

            $ngohir->name_of_interviewer                    = $request->input('name_of_interviewer');
            $ngohir->place_of_interview                     = $request->input('place_of_interview');
            $ngohir->date_of_interview                      = $request->has('date_of_interview') ? Carbon::createFromFormat('d-m-Y', $request->input('date_of_interview'), session('user_current_timezone'))->setTimezone('UTC')->toDateString() : null;
            $ngohir->name_of_informer                       = $request->input('name_of_informer');
            $ngohir->survivor_informer_relation             = $request->input('survivor_informer_relation');
            $ngohir->done_over_phone                        = $request->input('done_over_phone') == 1 ? 1 : 0;
            
            $ngohir->interview_info = 1;
            if($ngohir->save()) 
            {
                /* Address Create Start */
                $survivor_address_title = ['present_address', 'native_address'];

                for ($i=0;$i<count($survivor_address_title);$i++) 
                {
                    $survivor_address = new Address();

                    $survivor_address->litigation_id            = $id;
                    $survivor_address->task_id                  = $request->input('task_id');
                    $survivor_address->title                    = $survivor_address_title[$i];
                    $survivor_address->care_of                  = $request->input('survivor_address_care_of')[$i];
                    $survivor_address->relation_with_survivor   = $request->input('relation_with_survivor')[$i];
                    $survivor_address->country                  = $request->input('survivor_address_country')[$i];
                    $survivor_address->state                    = $request->input('survivor_address_state')[$i];
                    $survivor_address->district                 = $request->input('survivor_address_district')[$i];
                    $survivor_address->postal_code              = $request->input('survivor_address_postal_code')[$i];
                    $survivor_address->address_line_1           = $request->input('survivor_address_line_1')[$i];
                    $survivor_address->address_line_2           = $request->input('survivor_address_line_2')[$i];
                    $survivor_address->contact_number           = $request->input('survivor_address_contact_number')[$i];

                    $survivor_address->save();
                }
                /* Address Create End */
                $in_progress_status_id =  Usability::TaskInProgressStatusID();
                Litigation::saveCaseTask($in_progress_status_id,$id, $request->input('task_id'));
            }
            $portion = 'Interview Information';

            $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
            Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

            return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Created Successfully');

        }
        elseif ($btnID == 2) 
        {
            //return "btn ID = 2 :: store() || $request->input('basic_info') = ".$request->input('basic_info');
            
            $this->validate($request, 
                [
                    'name_of_the_survivor_at_destination' => 'required|regex:/^[A-z ]+$/',
                    'age_select'    => 'required',
                    'nationality'    => 'required',
                ]
            );
               

            $date_of_birth = strtotime($request->input("dob"));
            $today = strtotime(date('d-m-Y'));
                
            if($today < $date_of_birth)
            {
                return back()->with('error', 'Date of Birth is invalid!! Please enter a valid date.');;
            }
            if($request->input('nationality') < 1)
            {
                return back()->with('error', 'The Nationality field is required');
            }
            if(!($request->input('dob') || $request->input('age_year_part') || $request->input('age_month_part')))
            {
                return back()->with('error', 'Age Information field is required');
            }


            $ngohir->name_of_the_survivor_at_destination = $request->input('name_of_the_survivor_at_destination');
                
            $ngohir->case_filed_by_parents = $request->input('case_filed_by_parents') == 1 ? 1 : 0;
                

            if($request->has('age_select')) 
            {
                if(strlen($request->input("dob")) > 0) 
                {
                    $ngohir->date_of_birth = date('Y-m-d', strtotime($request->input("dob")));
                    list($ngohir->age_year_part, $ngohir->age_month_part) = calculate_age($request->input("dob"));
                } 
                else 
                {
                    $ngohir->date_of_birth              = null;
                    $ngohir->age_year_part              = $request->input("age_year_part");
                    $ngohir->age_month_part             = $request->input("age_month_part");
                }
            } 
            else 
            {
                $ngohir->date_of_birth                  = $ngohir->date_of_birth ? $ngohir->date_of_birth : null;
                $ngohir->age_year_part                  = $ngohir->age_year_part ? $ngohir->age_year_part : null;
                $ngohir->age_month_part                 = $ngohir->age_month_part ? $ngohir->age_month_part : null;
            }
                
            $ngohir->father_name = $request->input('father_name');
                
            $ngohir->mother_name = $ngohir->mother_name;
                
            $ngohir->guardian_occupation = $request->input('guardian_occupation');
                
            $ngohir->guardian_monthly_income = $request->input('guardian_monthly_income');
                
            $ngohir->marital_status = $request->input('marital_status');
                
            $ngohir->spouse_name = $request->input('spouse_name');
                
            $ngohir->sex = $request->input('sex');
                
            $ngohir->nationality = $request->input('nationality');
                
            $ngohir->religion = $request->input('religion');
                
            $ngohir->education = $request->input('education');


            $ngohir->basic_info = 1; // btn id 2

            if($ngohir->save()) 
            {
                /* Address Create Start */
                $survivor_address_title = ['present_address', 'native_address'];

                for ($i=0;$i<count($survivor_address_title);$i++) 
                {
                    $survivor_address = new Address();

                    $survivor_address->litigation_id            = $id;
                    $survivor_address->task_id                  = $request->input('task_id');
                    $survivor_address->title                    = $survivor_address_title[$i];
                    $survivor_address->care_of                  = $request->input('survivor_address_care_of')[$i];
                    $survivor_address->relation_with_survivor   = $request->input('relation_with_survivor')[$i];
                    $survivor_address->country                  = $request->input('survivor_address_country')[$i];
                    $survivor_address->state                    = $request->input('survivor_address_state')[$i];
                    $survivor_address->district                 = $request->input('survivor_address_district')[$i];
                    $survivor_address->postal_code              = $request->input('survivor_address_postal_code')[$i];
                    $survivor_address->address_line_1           = $request->input('survivor_address_line_1')[$i];
                    $survivor_address->address_line_2           = $request->input('survivor_address_line_2')[$i];
                    $survivor_address->contact_number           = $request->input('survivor_address_contact_number')[$i];

                    $survivor_address->save();
                }
                /* Address Create End */
                $in_progress_status_id =  Usability::TaskInProgressStatusID();
                Litigation::saveCaseTask($in_progress_status_id,$id, $request->input('task_id'));
            }

            $portion = 'Survivors Basic Information';

            $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
            Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

            return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Created Successfully');
        }
        elseif( $btnID == 3 )
        {
            //return "Address :: store()";

            $address_is_filled = 0;

            $ngohir->address_at_source = 1; 

            /* Dummy Data */

            /* Dummy Data End */
            
                /* Address Create Start */
            $survivor_address_title = ['present_address', 'native_address'];

            for ($i=0; $i<count($survivor_address_title); $i++) 
            {
                $survivor_address = new Address();
                
               /* $this->validate($request,
                    [
                        'survivor_address_care_of' => 'regex:/^[A-z ]+$/',
                        'relation_with_survivor' => 'regex:/^[A-z ]+$/',
                        'survivor_address_country' => 'required',
                        'survivor_address_postal_code' => 'regex:/^[0-9]+$/',
                        'survivor_address_contact_number' => 'regex:/^[0-9]+$/',
                    ] 
                );*/
                $survivor_address->litigation_id            = $id;
                $survivor_address->task_id                  = $request->input('task_id');
                $survivor_address->title                    = $survivor_address_title[$i];
                echo "title = ".$survivor_address->title ."<br>";

                $survivor_address->care_of                  = $request->input('survivor_address_care_of')[$i];
                echo "C/O = ".$survivor_address->care_of."<br>";

                $survivor_address->relation_with_survivor   = $request->input('relation_with_survivor')[$i];
                echo "relation_with_survivor = ".$survivor_address->relation_with_survivor."<br>";

                $survivor_address->country                  = isset($request->input('survivor_address_country')[$i]) ? $request->input('survivor_address_country')[$i] : 0;
                   // echo "country = ".$survivor_address->country."<br>";

                    //echo "?? state = ".$request->input('survivor_address_state')[$i]."<br>";
                    //die;

                    $survivor_address->state = isset($request->input('survivor_address_state')[$i]) ? $request->input('survivor_address_state')[$i] : 0;
                    //echo "state = ".$survivor_address->state."<br>";

                    $survivor_address->district                 = isset($request->input('survivor_address_district')[$i]) ? $request->input('survivor_address_district')[$i] : 0;

                $survivor_address->postal_code              = $request->input('survivor_address_postal_code')[$i];
                echo "postal_code = ".$survivor_address->postal_code."<br>";

                $survivor_address->address_line_1           = $request->input('survivor_address_line_1')[$i];
                echo "address_line_1 = ".$survivor_address->address_line_1."<br>";

                $survivor_address->address_line_2           = $request->input('survivor_address_line_2')[$i];
                echo "address_line_2 = ".$survivor_address->address_line_2 ."<br>";

                $survivor_address->contact_number           = $request->input('survivor_address_contact_number')[$i];
                echo "contact_number = ".$survivor_address->contact_number."<br>";

                
                if(isset($survivor_address->survivor_address_care_of) ||                   
                   ($survivor_address->relation_with_survivor!="")  ||
                   ($survivor_address->country!="0")  ||
                   ($survivor_address->state!="0")  ||
                   ($survivor_address->district!="0")  ||
                   isset($survivor_address->survivor_address_postal_code)  ||
                   isset($survivor_address->survivor_address_line_1)  ||
                   isset($survivor_address->survivor_address_line_2 )  ||
                   isset($survivor_address->survivor_address_contact_number)  
                 )
                {
                    //$survivor_address->save();
                    $address_is_filled = 1;
                    $j = $i + 1;
                    echo $j." number address is filled. <br>";
                }
                else
                {
                    $j = $i + 1;
                    echo $j." number address not filled. <br>";
                    //$empty_address = $survivor_address;
                }
            }
            //die;

            /* Address Create End */

            $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
            Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

            if($address_is_filled > 0)
            {
                //$empty_address->save();
                
                $ngohir->save();

                $portion = 'Address at Source';
                return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' created Successfully');
            }
            else
                return back();
        }
        elseif($btnID==4)
        {
            //return "physical_desc";

            $ngohir->height_ft_part = $request->input('height_ft_part'); 
            $ngohir->height_in_part = $request->input('height_in_part');
            $ngohir->birth_mark = $request->input('birth_mark');
            $ngohir->complexion = $request->input('complexion');
            $ngohir->hair_color = $request->input('hair_color');
            $ngohir->identification_mark = $request->input('identification_mark');
            $ngohir->deformities = $request->input('deformities');
            
            $ngohir->physical_desc = 1;
            $portion = 'Physical Description';

            $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
            Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

            return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Created Successfully');
        }

        /** Validation END written by MAHADI **/


        
        
        $ngohir->name_of_interviewer                    = $request->input('name_of_interviewer');
        $ngohir->place_of_interview                     = $request->input('place_of_interview');
        $ngohir->date_of_interview                      = $request->has('date_of_interview') ? Carbon::createFromFormat('d-m-Y', $request->input('date_of_interview'), session('user_current_timezone'))->setTimezone('UTC')->toDateString() : null;
        $ngohir->name_of_informer                       = $request->input('name_of_informer');






        $ngohir->name_of_the_survivor_at_destination    = $request->input('name_of_the_survivor_at_destination');
        $ngohir->father_name                            = $request->input('father_name');
        $ngohir->mother_name                            = $request->input('mother_name');
        $ngohir->marital_status                         = $request->input('marital_status');
        $ngohir->spouse_name                            = $request->input('spouse_name');
        $ngohir->guardian_occupation                    = $request->input('guardian_occupation');
        $ngohir->guardian_monthly_income                = $request->input('guardian_monthly_income');
        $ngohir->eye_color                              = $request->input('eye_color');
        $ngohir->hair_color                             = $request->input('hair_color');
        $ngohir->deformities                            = $request->input('deformities');
        $ngohir->identification_mark                    = $request->input('identification_mark');
        $ngohir->case_filed_by_parents                  = $request->input('case_filed_by_parents') == 1 ? 1 : 0;

        $ngohir->case_filed_no                           = $request->input('case_file_number');

        //dd($ngohir->case_filed_by_parents);


        if(($request->input('interview_info')==1)){
            $ngohir->interview_info = 1;
            $portion = 'Interview Information';
        }

        if(($request->input('basic_info')==1)){
            $ngohir->basic_info = 1;
            $portion = 'Survivor Basic Information';
        }

        if(($request->input('address_at_source')==1)){
            $ngohir->address_at_source = 1;
            $portion = 'Address at Source';
        }

        if(($request->input('physical_desc')==1)){
            $ngohir->physical_desc = 1;
            $portion = 'Physical Description';
        }

            //        $ngohir->date_of_birth                          = Carbon::createFromFormat('d-m-Y', $request->input('date_of_birth'), session('user_current_timezone'))->setTimezone('UTC')->toDateString();

        if($request->has('age_select')) {
            if(strlen($request->input("dob")) > 0) {
                $ngohir->date_of_birth              = Carbon::createFromFormat('d-m-Y', $request->input('dob'), session('user_current_timezone'))->setTimezone('UTC')->toDateString();
                list($ngohir->age_year_part, $ngohir->age_month_part) = calculate_age($request->input("dob"));
            } else {
                $ngohir->date_of_birth              = null;
                $ngohir->age_year_part              = $request->input("age_year_part");
                $ngohir->age_month_part             = $request->input("age_month_part");
            }
        } else {
            $ngohir->date_of_birth                  = null;
            $ngohir->age_year_part                  = null;
            $ngohir->age_month_part                 = null;
        }

        $ngohir->nationality                            = $request->input('nationality');
        $ngohir->religion                               = $request->input('religion');
        $ngohir->education                              = $request->input('education');
        $ngohir->history_of_previous_stay               = $request->input('history_of_previous_stay');
        $ngohir->height_ft_part                         = $request->input('height_ft_part');
        $ngohir->height_in_part                         = $request->input('height_in_part');
        $ngohir->sex                                    = $request->input('sex');
        $ngohir->birth_mark                             = $request->input('birth_mark');
        $ngohir->complexion                             = $request->input('complexion');
        $ngohir->pregnancy                              = $request->input('pregnancy');
        $ngohir->accompanying_with_survivor             = $request->input('accompanying_with_survivor');
        $ngohir->abuse                                  = $request->input('abuse');
        $ngohir->if_yes_type                            = $request->input('if_yes_type');

        if($ngohir->save()) {
            /* Address Create Start */
            $survivor_address_title = ['present_address', 'native_address'];

            for ($i=0;$i<count($survivor_address_title);$i++) {
                $survivor_address = new Address();

                $survivor_address->litigation_id            = $id;
                $survivor_address->task_id                  = $request->input('task_id');
                $survivor_address->title                    = $survivor_address_title[$i];
                $survivor_address->care_of                  = $request->input('survivor_address_care_of')[$i];
                $survivor_address->relation_with_survivor   = $request->input('relation_with_survivor')[$i];
                $survivor_address->country                  = $request->input('survivor_address_country')[$i];
                $survivor_address->state                    = $request->input('survivor_address_state')[$i];
                $survivor_address->district                 = $request->input('survivor_address_district')[$i];
                $survivor_address->postal_code              = $request->input('survivor_address_postal_code')[$i];
                $survivor_address->address_line_1           = $request->input('survivor_address_line_1')[$i];
                $survivor_address->address_line_2           = $request->input('survivor_address_line_2')[$i];
                $survivor_address->contact_number           = $request->input('survivor_address_contact_number')[$i];

                $survivor_address->save();
            }
            /* Address Create End */
            $in_progress_status_id =  Usability::TaskInProgressStatusID();
            Litigation::saveCaseTask($in_progress_status_id,$id, $request->input('task_id'));
        }

		//return redirect()->route('/cases/show/ngohirs.index', $id)->with('message', 'Item created successfully.');
        return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Created Successfully');
	}

	/**
	 * Display the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function show($id)
	{
		$ngohir = Ngohir::findOrFail($id);

		return view('ngohirs.show', compact('ngohir'));
	}

	/**
	 * Show the form for editing the specified resource.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function edit($id)
	{
		$ngohir = Ngohir::findOrFail($id);

		return view('ngohirs.edit', compact('ngohir'));
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  int  $id
	 * @param Request $request
	 * @return Response
	 */
	public function update(Request $request, $id)
	{
        $btnID =  $request->input("id");

		$ngohir = Ngohir::findOrFail($id);

        /** Validation START written by MAHADI **/
		if($btnID == 1)
        {
            //return "btn 1 :: update()";
            /*start of validation of form 1*/
            $this->validate($request, 
                [
                    'name_of_interviewer' => 'required|regex:/^[A-z ]+$/',
                    'name_of_informer'    => 'required|regex:/^[A-z ]+$/',
                    'place_of_interview'    => 'regex:/^[A-z ]+$/',
                    'survivor_informer_relation'    => 'regex:/^[A-z ]+$/',
                ]
            );

            $date_of_interview = strtotime($request->input("date_of_interview"));
            $today = strtotime(date('d-m-Y'));
                
            if($today < $date_of_interview)
            {
                return back()->with('error', 'Date of Interview is invalid!! Please enter a valid date.');;
            }

            /*var_dump( $request->input('name_of_interviewer') );
            echo " => ";
            var_dump($ngohir->name_of_interviewer);
            echo "<br>";

            var_dump( $request->input('place_of_interview') );
            echo " => ";
            var_dump($ngohir->place_of_interview);
            echo "<br>";

            var_dump( strtotime($request->input('date_of_interview')) );
            echo " => ";
            var_dump( strtotime($ngohir->date_of_interview) );
            echo "<br>";

            var_dump( $request->input('name_of_informer') );
            echo " => ";
            var_dump($ngohir->name_of_informer);
            echo "<br>";*/


            /*var_dump( $done_over_phone );
            echo " => ";
            var_dump($ngohir->done_over_phone);
            echo "<br>";

            if($done_over_phone == $ngohir->done_over_phone)
                echo "<br>Both are ZERO <br>";
            die;*/

            $done_over_phone = 1;
            if($request->input('done_over_phone') == '')
                $done_over_phone = 0;

            if(    $request->input('name_of_interviewer') != $ngohir->name_of_interviewer 
                || $request->input('place_of_interview')  != $ngohir->place_of_interview
                || strtotime($request->input('date_of_interview'))   
                != strtotime($ngohir->date_of_interview)
                || $request->input('name_of_informer')    != $ngohir->name_of_informer
                || $request->input('survivor_informer_relation')    
                != $ngohir->survivor_informer_relation
                || $done_over_phone                       != $ngohir->done_over_phone     
            )
            {
                $ngohir->name_of_interviewer                    = $request->has('name_of_interviewer') ? $request->input('name_of_interviewer') : $ngohir->name_of_interviewer;
                $ngohir->place_of_interview                     = $request->has('place_of_interview') ? $request->input('place_of_interview') : $ngohir->place_of_interview;
                $ngohir->date_of_interview                      = $request->has('date_of_interview') ? Carbon::createFromFormat('d-m-Y', $request->input('date_of_interview'), session('user_current_timezone'))->setTimezone('UTC')->toDateString() : $ngohir->date_of_interview;
                $ngohir->name_of_informer                       = $request->has('name_of_informer') ? $request->input('name_of_informer') : $ngohir->name_of_informer;
                $ngohir->survivor_informer_relation             = $request->has('survivor_informer_relation') ? $request->input('survivor_informer_relation') : $ngohir->survivor_informer_relation;
                $ngohir->done_over_phone                        = $request->has('done_over_phone') ? $request->input('done_over_phone') : 0;

                $ngohir->interview_info = 1;
                $ngohir->save();
                $portion = 'Interview Information';

                $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
                Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

                return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Updated Successfully');
            }
            else return back();
        }
        elseif ($btnID == 2) 
        {
            //return "btn ID = 2 :: update() || $request->input('basic_info') = ".$request->input('basic_info');

            $this->validate($request, 
                [
                    'name_of_the_survivor_at_destination' => 'required|regex:/^[A-z ]+$/',
                    'age_select'    => 'required',
                    'nationality'    => 'required',
                ]
            );
               

            $date_of_birth = strtotime($request->input("dob"));
            $today = strtotime(date('d-m-Y'));
                
            if($today < $date_of_birth)
            {
                return back()->with('error', 'Date of Birth is invalid!! Please enter a valid date.');;
            }
            if($request->input('nationality') < 1)
            {
                return back()->with('error', 'The Nationality field is required');
            }
            if(!($request->input('dob') || $request->input('age_year_part') || $request->input('age_month_part')))
            {
                return back()->with('error', 'Age Information field is required');
            }

            if( $request->input("dob")     != $ngohir->date_of_birth 
                || $ngohir->age_year_part  != $request->input("age_year_part") 
                || $ngohir->age_month_part != $request->input("age_month_part") 
                || $ngohir->name_of_the_survivor_at_destination != $request->input("name_of_the_survivor_at_destination") 
                || $ngohir->case_filed_by_parents != $request->input("case_filed_by_parents") 
                || $ngohir->father_name != $request->input("father_name") 
                || $ngohir->mother_name != $request->input("mother_name") 
                || $ngohir->guardian_occupation != $request->input("guardian_occupation") 
                || $ngohir->guardian_monthly_income != $request->input("guardian_monthly_income") 
                || $ngohir->marital_status != $request->input("marital_status") 
                || $ngohir->spouse_name != $request->input("spouse_name") 
                || $ngohir->sex != $request->input("sex") 
                || $ngohir->nationality != $request->input("nationality") 
                || $ngohir->religion != $request->input("religion") 
                || $ngohir->education != $request->input("education") 
            )
            {
                $ngohir->name_of_the_survivor_at_destination    = $request->has('name_of_the_survivor_at_destination') ? $request->input('name_of_the_survivor_at_destination') : $ngohir->name_of_the_survivor_at_destination;
                
                $ngohir->case_filed_by_parents                  = $request->has('case_filed_by_parents') ? $request->input('case_filed_by_parents') : $ngohir->case_filed_by_parents ;
                

                if($request->has('age_select')) 
                {
                    if(strlen($request->input("dob")) > 0) 
                    {
                        $ngohir->date_of_birth = date('Y-m-d', strtotime($request->input("dob")));
                        list($ngohir->age_year_part, $ngohir->age_month_part) = calculate_age($request->input("dob"));
                    } 
                    else 
                    {
                        $ngohir->date_of_birth              = null;
                        $ngohir->age_year_part              = $request->input("age_year_part");
                        $ngohir->age_month_part             = $request->input("age_month_part");
                    }
                } 
                else 
                {
                    $ngohir->date_of_birth                  = $ngohir->date_of_birth ? $ngohir->date_of_birth : null;
                    $ngohir->age_year_part                  = $ngohir->age_year_part ? $ngohir->age_year_part : null;
                    $ngohir->age_month_part                 = $ngohir->age_month_part ? $ngohir->age_month_part : null;
        }
                
                $ngohir->father_name                            = $request->has('father_name') ? $request->input('father_name') : $ngohir->father_name;
                
                $ngohir->mother_name                            = $request->has('mother_name') ? $request->input('mother_name') : $ngohir->mother_name;
                
                $ngohir->guardian_occupation                    = $request->has('guardian_occupation') ? $request->input('guardian_occupation') : $ngohir->guardian_occupation ;
                
                $ngohir->guardian_monthly_income                = $request->has('guardian_monthly_income') ? $request->input('guardian_monthly_income') : $ngohir->guardian_monthly_income;
                
                $ngohir->marital_status                         = $request->has('marital_status') ? $request->input('marital_status') : $ngohir->marital_status;
                
                $ngohir->spouse_name                            = $request->has('spouse_name') ? $request->input('spouse_name'): $ngohir->spouse_name;
                
                $ngohir->sex                                    = $request->has('sex') ? $request->input('sex') : $ngohir->sex;
                
                $ngohir->nationality                            = $request->has('nationality') ? $request->input('nationality') : $ngohir->nationality;
                
                $ngohir->religion                               = $request->has('religion') ? $request->input('religion') : $ngohir->religion;
                
                $ngohir->education                              = $request->has('education') ? $request->input('education') : $ngohir->education;


                $ngohir->basic_info = 1; // btn id 2

                if($ngohir->save()) 
                {
                    /* Address Edit Start */
                    if($request->has('survivor_address_title')) 
                    {
                        $survivor_address_title = array_merge(['present_address', 'native_address'], $request->input('survivor_address_title'));
                    } 
                    else 
                    {
                        $survivor_address_title = ['present_address', 'native_address'];
                    }

                    //dd(count($request->input("address_id")));
                    for( $i=0; $i<count($request->input("address_id")); $i++ )
                    {

                        $survivor_address = Address::findOrFail($request->input("address_id")[$i]);

                        $survivor_address->title                    = $survivor_address_title[$i];
                        $survivor_address->care_of                  = $request->input("survivor_address_care_of")[$i];
                        $survivor_address->relation_with_survivor   = $request->input("relation_with_survivor")[$i];
                        $survivor_address->country                  = $request->input("survivor_address_country")[$i];
                        $survivor_address->state                    = $request->input("survivor_address_state")[$i];
                        $survivor_address->district                 = $request->input("survivor_address_district")[$i];
                        $survivor_address->postal_code              = $request->input("survivor_address_postal_code")[$i];
                        $survivor_address->address_line_1           = $request->input("survivor_address_line_1")[$i];
                        $survivor_address->address_line_2           = $request->input("survivor_address_line_2")[$i];
                        $survivor_address->contact_number           = $request->input("survivor_address_contact_number")[$i];

                        $survivor_address->save();
                    }
                    /* Address Edit End */

                    $in_progress_status_id =  Usability::TaskInProgressStatusID();
                    //dd($in_progress_status_id);
                    Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));
                }

                $portion = 'Survivor Basic Information';
                
                $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
                Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));
                return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Updated Successfully');
            }

            else  return back();

        }
        elseif($btnID == 3)
        {
            //return "btn 1 :: update()";
            $address_is_filled = 0;
            $ngohir->address_at_source = 1;
            if($ngohir->save()) 
            {
                /* Address Edit Start */
                if($request->has('survivor_address_title')) 
                {
                    $survivor_address_title = array_merge(['present_address', 'native_address'], $request->input('survivor_address_title'));
                } 
                else 
                {
                    $survivor_address_title = ['present_address', 'native_address'];
                }

                //dd(count($request->input("address_id")));
                for( $i=0; $i<count($request->input("address_id")); $i++ )
                {

                    $survivor_address = Address::findOrFail($request->input("address_id")[$i]);
                    //$empty_address = Address::findOrFail($request->input("address_id")[$i]);

                    //echo "<br><br>";
                    //echo "title = ".$survivor_address->title ."<br>";

                    $survivor_address->care_of                  = $request->input('survivor_address_care_of')[$i];
                    ///echo "C/O = ".$survivor_address->care_of."<br>";

                    $survivor_address->relation_with_survivor   = $request->input('relation_with_survivor')[$i];
                    //echo "relation_with_survivor = ".$survivor_address->relation_with_survivor."<br>";

                    $survivor_address->country                  = isset($request->input('survivor_address_country')[$i]) ? $request->input('survivor_address_country')[$i] : 0;
                   // echo "country = ".$survivor_address->country."<br>";

                    //echo "?? state = ".$request->input('survivor_address_state')[$i]."<br>";
                    //die;

                    $survivor_address->state = isset($request->input('survivor_address_state')[$i]) ? $request->input('survivor_address_state')[$i] : 0;
                    //echo "state = ".$survivor_address->state."<br>";

                    $survivor_address->district                 = isset($request->input('survivor_address_district')[$i]) ? $request->input('survivor_address_district')[$i] : 0;
                    //echo "district = ".$survivor_address->district."<br>" ;

                    $survivor_address->postal_code              = $request->input('survivor_address_postal_code')[$i];
                    //echo "postal_code = ".$survivor_address->postal_code."<br>";

                    $survivor_address->address_line_1           = $request->input('survivor_address_line_1')[$i];
                    //echo "address_line_1 = ".$survivor_address->address_line_1."<br>";

                    $survivor_address->address_line_2           = $request->input('survivor_address_line_2')[$i];
                    //echo "address_line_2 = ".$survivor_address->address_line_2 ."<br>";

                    $survivor_address->contact_number           = $request->input('survivor_address_contact_number')[$i];
                    //echo "contact_number = ".$survivor_address->contact_number."<br>";

                    
                    if(isset($survivor_address->survivor_address_care_of) ||                   
                       ($survivor_address->relation_with_survivor!="")  ||
                       ($survivor_address->country!="0")  ||
                       ($survivor_address->state!="0")  ||
                       ($survivor_address->district!="0")  ||
                       isset($survivor_address->survivor_address_postal_code)  ||
                       isset($survivor_address->survivor_address_line_1)  ||
                       isset($survivor_address->survivor_address_line_2 )  ||
                       isset($survivor_address->survivor_address_contact_number)  
                     )
                    {
                        $survivor_address->save();
                        $address_is_filled = 1;
                        
                        //$j = $i + 1;
                        //echo $j." number address is filled. <br>";
                    }
                    else
                    {
                        //$j = $i + 1;
                        //echo $j." number address not filled. <br>";
                        //$empty_address = $survivor_address;
                    }
                }
                //echo "<br><br>";
                //die;
				/* Address Edit End */

				$in_progress_status_id =  Usability::TaskInProgressStatusID();
                
				Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));
				if($address_is_filled > 0)
				{
					//$empty_address->save();
					
					$ngohir->save();

					$portion = 'Address at Source';
					return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Updated Successfully');
				}
				else
					return back();
			}
        }
        elseif($btnID==4)
        {
            //return "physical_desc";

            if(
            $ngohir->height_ft_part != $request->input('height_ft_part') || 
            $ngohir->height_in_part != $request->input('height_in_part') ||
            $ngohir->birth_mark != $request->input('birth_mark') ||
            $ngohir->complexion != $request->input('complexion') || 
            $ngohir->hair_color != $request->input('hair_color') ||
            $ngohir->identification_mark != $request->input('identification_mark') ||
            $ngohir->deformities != $request->input('deformities')
            )
            {
                $ngohir->physical_desc = 1;
                $portion = 'Physical Description';
                
                $ngohir->height_ft_part                         = $request->has('height_ft_part') ? $request->input('height_ft_part') : $ngohir->height_ft_part;
                $ngohir->height_in_part                         = $request->has('height_in_part') ? $request->input('height_in_part') : $ngohir->height_in_part;
                $ngohir->birth_mark                             = $request->has('birth_mark') ? $request->input('birth_mark') : $ngohir->birth_mark;
                $ngohir->complexion                             = $request->has('complexion') ? $request->input('complexion') : $ngohir->complexion;
                $ngohir->hair_color                             = $request->has('hair_color') ? $request->input('hair_color') : $ngohir->hair_color;
                $ngohir->identification_mark                    = $request->has('identification_mark') ? $request->input('identification_mark') : $ngohir->identification_mark;
                $ngohir->deformities                            = $request->has('deformities') ? $request->input('deformities') : $ngohir->deformities;

                $ngohir->save();

                $in_progress_status_id =  Usability::TaskInProgressStatusID();
                
                Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));

                return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Updated Successfully');
                
            }
            else return back();
        }

        /** validation END written by MAHADI **/



//      $ngohir->litigation_id                          = $id;
//        $ngohir->name_of_the_survivor_at_source         = $request->input('name_of_the_survivor_at_source');


        








        $ngohir->history_of_previous_stay               = $request->has('history_of_previous_stay') ? $request->input('history_of_previous_stay') : $ngohir->history_of_previous_stay;

        $ngohir->height_ft_part                         = $request->has('height_ft_part') ? $request->input('height_ft_part') : $ngohir->height_ft_part;
        $ngohir->height_in_part                         = $request->has('height_in_part') ? $request->input('height_in_part') : $ngohir->height_in_part;
        $ngohir->birth_mark                             = $request->has('birth_mark') ? $request->input('birth_mark') : $ngohir->birth_mark;
        $ngohir->complexion                             = $request->has('complexion') ? $request->input('complexion') : $ngohir->complexion;
        $ngohir->hair_color                             = $request->has('hair_color') ? $request->input('hair_color') : $ngohir->hair_color;
        $ngohir->identification_mark                    = $request->has('identification_mark') ? $request->input('identification_mark') : $ngohir->identification_mark;
        $ngohir->deformities                            = $request->has('deformities') ? $request->input('deformities') : $ngohir->deformities;

        $ngohir->pregnancy                              = $request->has('pregnancy') ? $request->input('pregnancy') : $ngohir->pregnancy;
        $ngohir->accompanying_with_survivor             = $request->has('accompanying_with_survivor') ? $request->input('accompanying_with_survivor') : $ngohir->accompanying_with_survivor;
        $ngohir->abuse                                  = $request->has('abuse') ? $request->input('abuse') : $ngohir->abuse;
        $ngohir->if_yes_type                            = $request->has('if_yes_type') ? $request->input('if_yes_type') : $ngohir->if_yes_type;
        $ngohir->case_filed_no                          = $request->has('case_file_number') ? $request->input('case_file_number') : '';
        $ngohir->survivor_informer_relation             = $request->has('survivor_informer_relation') ? $request->input('survivor_informer_relation') : $ngohir->survivor_informer_relation;
        $ngohir->eye_color                              = $request->has('eye_color') ? $request->input('eye_color') : $ngohir->eye_color;
        

        
        if(($request->input('interview_info')==1)){
            $ngohir->interview_info = 1;
            $portion = 'Interview Information';
        }

        if(($request->input('basic_info')==1)){
            $ngohir->basic_info = 1;
            $portion = 'Survivor Basic Information';
        }

        if(($request->input('address_at_source')==1)){
            $ngohir->address_at_source = 1;
            $portion = 'Address at Source';
        }

        if(($request->input('physical_desc')==1)){
            $ngohir->physical_desc = 1;
            $portion = 'Physical Description';
        }


        if($ngohir->save()) 
        {
            /* Address Edit Start */
            if($request->has('survivor_address_title')) 
            {
                $survivor_address_title = array_merge(['present_address', 'native_address'], $request->input('survivor_address_title'));
            } 
            else 
            {
                $survivor_address_title = ['present_address', 'native_address'];
            }

            //dd(count($request->input("address_id")));
            for( $i=0; $i<count($request->input("address_id")); $i++ )
            {

                $survivor_address = Address::findOrFail($request->input("address_id")[$i]);

                $survivor_address->title                    = $survivor_address_title[$i];
                $survivor_address->care_of                  = $request->input("survivor_address_care_of")[$i];
                $survivor_address->relation_with_survivor   = $request->input("relation_with_survivor")[$i];
                $survivor_address->country                  = $request->input("survivor_address_country")[$i];
                $survivor_address->state                    = $request->input("survivor_address_state")[$i];
                $survivor_address->district                 = $request->input("survivor_address_district")[$i];
                $survivor_address->postal_code              = $request->input("survivor_address_postal_code")[$i];
                $survivor_address->address_line_1           = $request->input("survivor_address_line_1")[$i];
                $survivor_address->address_line_2           = $request->input("survivor_address_line_2")[$i];
                $survivor_address->contact_number           = $request->input("survivor_address_contact_number")[$i];

                $survivor_address->save();
            }
            /* Address Edit End */

            $in_progress_status_id =  Usability::TaskInProgressStatusID();
            //dd($in_progress_status_id);
            Litigation::saveCaseTask($in_progress_status_id,$request->input('litigation_id'), $request->input('task_id'));
        }

        //return redirect()->route('/cases/show/ngohirs.index', $id)->with('message', 'Item created successfully.');
        $portion = "Form";
        return redirect('/cases/'.$request->input('litigation_id').'?tid='.$request->input('task_id'))->with('message', $portion.' Updated Successfully');
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return Response
	 */
	public function destroy($id)
	{
		$ngohir = Ngohir::findOrFail($id);
		$ngohir->delete();

		return redirect()->route('ngohirs.index')->with('message', 'Item deleted successfully.');
	}

}
