<?php

class AccountController extends \BaseController {

	public function getSignIn()
	{
		return View::make('account.signin');
	}

	public function postSignIn()
	{
		$validator = Validator::make(Input::all(), [
			'email'		=> 'required|email',
			'password'	=> 'required'
		]);

		if ($validator->fails()) {
			// Redirect to the sign in page
			return Redirect::route('account-sign-in')
					->withErrors($validator)
					->withInput();
		} else {
			$remember = (Input::has('remember')) ? true : false;
			// Attempt user sign in
			$auth = Auth::attempt([
				'email' 	=> Input::get('email'),
				'password' 	=> Input::get('password'),
				'active' 	=> 1
			], $remember);

			if ($auth) {
				// Redirect to the intended page
				return Redirect::intended('/');
			} else {
				return Redirect::route('account-sign-in')
						->with('global', 'Email/password wrong, or account not activated.');
			}
		}

		return Redirect::route('account-sign-in')
				->with('global', 'There was a problem signing you in.');
	}

	public function getSignOut()
	{
		Auth::logout();
		return Redirect::route('home');
	}

	public function getCreate()
	{
		return View::make('account.create');
	}

	public function postCreate()
	{
		$validator = Validator::make(Input::all(),
		[
			'email' 			=> 'required|max:50|email|unique:users',
			'username' 			=> 'required|max:20|min:3|unique:users',
			'password' 			=> 'required|min:6',
			'password_again'	=> 'required|same:password'
		]);

		if ($validator->fails()) {
			return Redirect::route('account-create')
					->withErrors($validator)
					->withInput();
		} else {
			// Create account
			$email 		= Input::get('email');
			$username 	= Input::get('username');
			$password 	= Input::get('password');

			// Activation code
			$code 		=  str_random(60);

			$user = User::create([
				'email' 	=> $email,
				'username' 	=> $username,
				'password' 	=> Hash::make($password),
				'code' 		=> $code,
				'active'	=> 0
			]);

			if ($user) {
				// Send email
				Mail::send('emails.auth.activate', ['link' => URL::route('account-activate', $code), 'username' => $username], function($message) use ($user){
					$message->to($user->email, $user->username)->subject('Activate your account');
				});
				return Redirect::route('home')
						->with('global', 'Your account has been created! We have sent you an email to activate your account.');
			}
		}
	}

	public function getActivate($code)
	{
		$user = User::where('code', '=', $code)->where('active', '=', 0);

		if ($user->count()) {
			$user = $user->first();

			// Update user to active state
			$user->active 	= 1;
			$user->code 	= '';

			if($user->save()) {
				return Redirect::route('home')
						->with('global', 'Your account has been activated, now you can login!');
			}
		}

		return Redirect::route('home')
					->with('global', 'We could not activate your account please try again later!');
	}

	public function getChangePassword()
	{
		return View::make('account.password');
	}

	public function postChangePassword()
	{
		$validator = Validator::make(Input::all(), [
			'old_password' 		=> 'required',
			'password'			=> 'required|min:6',
			'password_again'	=> 'required|same:password'
		]);

		if ($validator->fails()) {
			// Redirect
			return Redirect::route('account-change-password')
					->withErrors($validator);
		} else {
			// Change password
			$user 			= User::find(Auth::user()->id);

			$old_password 	= Input::get('old_password');
			$password 		= Input::get('password');

			if(Hash::check($old_password, $user->getAuthPassword())) {
				// Password user provided matches!
				$user->password = Hash::make($password);

				if ($user->save()) {
					return Redirect::route('home')
							->with('global', 'Your password has been changed.');
				}
			} else {
				return Redirect::route('account-change-password')
					->with('global' ,'Your old password is incorrect.');
			}
		}

		return Redirect::route('account-change-password')
				->with('global' ,'Your password could not be changed.');
	}

	public function getForgotPassword()
	{
		return View::make('account.forgot');
	}

	public function postForgotPassword()
	{
		$validator = Validator::make(Input::all(), [
			'email' => 'required|email'
		]);

		if ($validator->fails()) {
			return Redirect::route('account-forgot-password')
					->withErrors($validator)
					->withInput();
		} else {
			// Change password
			$user = User::where('email', '=', Input::get('email'));

			if($user->count()) {
				$user = $user->first();

				// Generate a new code and password
				$code 					= str_random(60);
				$password 				= str_random(10);

				$user->code 			= $code;
				$user->password_temp 	= Hash::make($password);

				if ($user->save()) {
					Mail::send('emails.auth.forgot', ['url' => URL::route('account-recover', $code), 'username' => $user->username, 'password' => $password], function($message) use ($user)
					{
						$message->to($user->email, $user->username)->subject('Your new password');
					});

					return Redirect::route('home')
							->with('global', 'We have sent you a new password by email.');
				}
			}
		}

		return Redirect::route('account-forgot-password')
				->with('global', 'Could not request new password.');
	}

	public function getRecover($code)
	{
		$user = User::where('code', '=', $code)
				->where('password_temp', '!=', '');

		if ($user->count()) {
			$user = $user->first();

			$user->password 		= $user->password_temp;
			$user->password_temp 	= '';
			$user->code 			= '';

			if ($user->save()) {
				return Redirect::route('home')
						->with('global', 'Your account has been recovered and youcan sign in with your new password.');
			}
		}

		return Redirect::route('home')
				->with('global', 'Could not recover you account.');
	}
}
