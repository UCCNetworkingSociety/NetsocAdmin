<?php namespace App\Http\Controllers;

use View;
use Auth;
use Response;
use Redirect;
use Validator;
use Hash;
use Mail;
use DB;
use Request;
use App\User;
use App\Setting;
use App\VerificationCode;
use adLDAP\classes\adLDAPUsers;
use adLDAP\adLDAP;

use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserController extends Controller
{
	
/*
|--------------------------------------------------------------------------
| User Controller
|--------------------------------------------------------------------------
|
| 
|
*/
	
	/*
	|------------------
	| VIEWS
	|------------------
	*/

	/**
	 * Render front page view
	 * @return VIEW welcome
	 */
	public function index( ){
		if( Auth::check() ){
			return View::make('admin.index');
		} else {
			return Redirect::route('login');
		}
		// return View::make( 'welcome' );
	}
	
	/**
	 * Render registration view
	 * @return VIEW register
	 */
	public function register( $token, $email ){
		if( Auth::check( ) ){
			// If user is logged in, send 'em home
			return Redirect::route( 'home' );
		}

		try {
			VerificationCode::where('email', $email)
							->where('confirmation_code', $token)
							->firstOrFail();

			$data['token'] = $token;
			$data['email'] = $email;

			return View::make( 'auth.register' )->with($data);
		} catch (ModelNotFoundException $e) {
			return Redirect::route('register');
		}
	}

	/**
	 * Render login view
	 * @return VIEW login
	 */
	public function login( ){

		if( Auth::check( ) ){
			// If user is logged in, send 'em home
			return Redirect::route( 'home' );
		}

		return View::make( 'auth.login' );
	}

	public function logout( ){
		Auth::logout();
		return Redirect::route('login');
	}

	public function changeDetails( ){
		$userInfo = Auth::user()->toArray();
		return View::make( 'admin.account.index' )->with('info', $userInfo);
	}

	public function preRegistration( ){
		if( Auth::check( ) ){
			// If user is logged in, send 'em home
			return Redirect::route( 'home' );
		}
		return View::make( 'auth.confirm_email' );
	}

	/*
	|------------------
	| FORM CONTROLS
	|------------------
	*/

	public function sendConfirmation( ){
		$data = Request::only(['email']);
		$email = $data['email'];
		$token = str_random(30);
		$domain = substr(strrchr($email, "@"), 1);

		if( in_array( $domain, explode( ',', env('ACCEPTABLE_EMAIL_DOMAINS') ) ) ){
			try {
				// Check if student exists already
				User::where('email', $email)->firstOrFail();

			} catch (ModelNotFoundException $e) {
				// Send email verification if they are
				Mail::send('emails.verification_code', ['email' => $email, 'confirmation_code' => $token], function ($m) use($email) {
		            $m->from('admin@'.env('USER_DOMAIN'), env('SITE_TITLE') );

		            $m->to($email)->subject('Verify Your Email For '.env('SITE_TITLE'));
		        });

		        VerificationCode::create([
		        	'email' => $email,
		        	'confirmation_code' => $token
		        ]);

		        return View::make('emails.thank_you');
			}
		} else{
			return Redirect::back()->withErrors(['That email is not on our approved list of student emails']);
		}
	}

	/**
	 * Creates a new user
	 * 	Data should be POSTed to this function only
	 * @return REDIRECT home
	 */
	public function store( ){
		// Only allow following fields to be submitted
		$data = Request::only( [
					'uid',
					'password',
					'password_confirmation',
					'student_id',
					'graduation_year',
					'course',
					'name',
					'token',
					'email'
				]);

		// Validate all input
		$validator = Validator::make( $data, [
					'uid'  				=> 'required|unique:users|min:5|alpha_num',
					'student_id'        => 'numeric|required|unique:users',
					'password'          => 'required|confirmed|min:5',
					'graduation_year'   => 'required|numeric|digits:4',
					'course'            => 'required',
					'name'				=> 'required',
					'email'				=> 'required|unique:users'
				]);


		$importantValues = [
			'token' => $data['token'],
			'email' => $data['email']
		];

		try {
			VerificationCode::where('email', $data['email'])
							->where('confirmation_code', $data['token'])
							->firstOrFail();
		} catch (ModelNotFoundException $e) {
			return Redirect::back()->withInput()->withErrors(['The verification code supplied is not valid']);
		}
		

		if( $validator->fails( ) ){
			// If validation fails, redirect back to 
			// registration form with errors
			return Redirect::back( )
					->withInput()
					->withErrors( $validator )
					->withInput( );
		}

		// All usernames need to be lowercase
		$data['uid'] = strtolower($data['uid']);

		$settings = $this->getLDAPDefaults( );
		$entry['objectClass'][] = 'account';
		$entry['objectClass'][] = 'top';
		$entry['objectClass'][] = 'posixAccount';
		$entry['objectClass'][] = 'mailAccount';
		$entry['dn'] = 'cn='.$data['uid'].',cn='.$settings['registration_group'].','.env('BASE_DN');
		$entry['gidNumber'] = $settings['registration_group_id'];
		$entry['uid'] = $data['uid'];

		// Get UID number, use it then increment it
		$current_uid_number = Setting::where('name', 'current_uid_number')->first();
		$entry['uidNumber'] = $current_uid_number->setting;
		$current_uid_number->setting++;
		$current_uid_number->save();

		$entry['userPassword'] = $data['password'] = $this->generateLDAPPassword( $data['password'] );


		$entry['homeDirectory'] = $settings['default_home_directory'].$data['uid'];
		$entry['loginShell'] = $settings['default_shell'];
		$entry['cn'] = $data['uid'];

		// Email of form {username}@{Top Level Domain}
		$entry['mail'] = $data['uid'] . '@' . env('USER_DOMAIN');

		// Create new user in LDAP
		$adLDAP = new adLDAP( );
		$ldapUsers = new adLDAPUsers( $adLDAP );
		$ldapUsers->create( $entry );

		// Create new user locally
		$newUser = User::create($data);

		// Get missing defaults from LDAP
		Auth::attempt(['uid' => $data['uid'], 'password' => $data['password']]);

		// login user
		Auth::login($newUser);

		if( $newUser ){
			// If successful, go to home
			return Redirect::route( 'static/ssh' );
		}
		
		// If unsuccessful, return with errors
		return Redirect::back( )
					->withInput()
					->withErrors( [
						'message' => 'We\'re sorry but registration failed, please email '. env('DEV_EMAIL') 
					] )
					->withInput( );

	}

	/**
	 * Log a user into the system
	 * @return REDIRECT home
	 */
	public function handleLogin( ){
		// Filter allowed data
		$data = Request::only([ 'uid', 'password' ]);

		// Validate user input
		$validator = Validator::make(
			$data,
			[
				'uid' => 'required',
				'password' => 'required',
			]
		);

		foreach ($data as $key => $value) {
			$data[$key] = $this->sanitizeLDAP( $value );
		}

		if($validator->fails()){
			// If validation fails, send back with errors
			return Redirect::route('login')->withErrors( $validator )->withInput( );
		}

		if( Auth::attempt( [ 'uid' => $data['uid'], 'password' => $data['password']], true ) ){
			// If login is successful, send them to home
			return Redirect::route( 'home' );
		} else {
			// Otherwise, tell them they're wrong
			return Redirect::route( 'login' )
						   ->withErrors([ 
								'message' => 'I\'m sorry, that username and password aren\'t correct.' 
							]);
		}

		return Redirect::route( 'login' )->withInput( );
	}

	public function update( ){
		$user_params = [
			'name',
			'course',
			'graduation_year',
			'password'
		];

		$data = Request::only($user_params);

		if( $data['password'] == '' ){
			unset( $data['password'] );
		}

		// Validate user input
		$validator = Validator::make(
			$data,
			[
				'name' => 'max:255|min:1',
				'course' => 'max:255|min:1',
				'graduation_year' => 'numeric|digits:4',
				'password' => 'sometimes|min:5'
			]
		);

		if($validator->fails()){
			// If validation fails, send back with errors
			return Redirect::back()->withErrors( $validator )->withInput( );
		}

		if( isset( $data['password'] ) ){
			$this->changePassword( $data['password'] );
		}

		$user = Auth::user();
		foreach ($user_params as $param) {
			isset($data[$param]) ? $user->$param = $data[$param] : '';
		}
		$user->save();

		return Redirect::back()->withErrors( ['message' => 'Updated successfully!'] );
	}

	/*
	|------------------
	| GENERAL FUNCTIONS
	|------------------
	*/

	public static function getGroupName( $gid ){
		// Explode and chunk CSV groups and gids
		$groups = explode(',', env( 'GROUPS_AND_GIDS') );
		$groups = array_chunk( $groups, 2 );

		foreach ($groups as $group) {
			if( $gid == $group[0] ){
				// [0] is the GID of the group
				return $group[1];
			}
		}

		return "member";
	}

	private function getLDAPDefaults( ){
		$setting_ids = [
			'registration_group',
			'registration_group_id',
			'default_home_directory',
			'default_shell'
		];

		$settings = [];

		foreach ($setting_ids as $id) {
			$settings[$id] = Setting::where('name', $id)->first()->setting;
		}

		return $settings;
	}

	private function generateLDAPPassword( $password_string ){
		// Generate an alphanumeric salt for the password
		$salt = substr(str_shuffle(str_repeat('ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789',4)),0,4);
		return '{crypt}'.crypt($password_string, $salt );
	}

	private function changePassword( $new_password ){
		$password = $this->generateLDAPPassword( $new_password );

		$group = $this->getGroupName( Auth::user()->gid );

		$dn = 'cn='.Auth::user()->uid.',cn='.$group.','.env('BASE_DN');

		$entry = array();
		$entry['dn'] = $dn;
		$entry['userPassword'] = $password;

		// Modify user's password
		$adLDAP = new adLDAP( );
		$ldapUsers = new adLDAPUsers( $adLDAP );
		$ldapUsers->modify( Auth::user()->uid, $entry );
	}

	/**
	 * Sanitizes ldap search strings.
	 * See rfc2254
	 * @link http://www.faqs.org/rfcs/rfc2254.html
	 * @since 1.5.1 and 1.4.5
	 * @param string $string
	 * @return string sanitized string
	 * @author Squirrelmail Team
	 */
	private function sanitizeLDAP($string) {
	    $sanitized=array('\\' => '\5c',
	                     '*' => '\2a',
	                     '(' => '\28',
	                     ')' => '\29',
	                     "\x00" => '\00');

	    return str_replace(array_keys($sanitized),array_values($sanitized),$string);
	}
}
