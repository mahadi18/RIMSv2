<?php  
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue
use Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Contract\Mailer;

class SendMail extends Mailable
{
	
	function __construct($request)
	{
		$this->request = $request;
	}

	public function build()
	{
		$mail_from = Auth::user()->email;
        $mail_to_admins = DB::table('role_user as ru')
                            ->where('user_id', '=', 1)
                            ->get();
        $mail_to_array = [];
		foreach ($mail_to_admins as $mail_to) 
        {
        	$mail_to_array = $mail_to->email;
        }
		return $this
	        ->to($mail_to_array)
	        ->subject('HackerPair Inquiry')
	        ->view('emails.mailbody', compact($this->request));
	}
}





?>