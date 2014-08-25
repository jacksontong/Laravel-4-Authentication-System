<?php
Route::get('/', [
	'as' => 'home',
	'uses' => 'HomeController@home'
]);

Route::get('/user/{username}', [
	'as'	=> 'profile-user',
	'uses'	=> 'ProfileController@user'
]);
/**
 * Authenticated group
 */
Route::group(['before' => 'auth'], function()
{
	/**
	 * CSRF protection group
	 */
	Route::group(['before' => 'csrf'], function()
	{
		/**
		 * Change password (POST)
		 */
		Route::post('/account/change-password', [
			'as' 	=> 'account-change-password-post',
			'uses' 	=> 'AccountController@postChangePassword'
		]);
	});

	/**
	 * Sign out (GET)
	 */
	Route::get('account/sign-out', [
		'as'	=> 'account-sign-out',
		'uses'	=> 'AccountController@getSignOut'
	]);

	/**
	 * Change password (GET)
	 */
	Route::get('/account/change-password', [
		'as' 	=> 'account-change-password',
		'uses' 	=> 'AccountController@getChangePassword'
	]);

});

/**
 * Unauthenticated group
 */
Route::group(['before' => 'guest'], function()
{
	/**
	 * CSRF protection group
	 */
	Route::group(['before' => 'csrf'], function()
	{
		/**
		 * Create account (POST)
		 */
		Route::post('/account/create', [
			'as' => 'account-create-post',
			'uses' => 'AccountController@postCreate'
		]);

		/**
		 * Sign in (POST)
		 */
		Route::post('/account/signin', [
			'as' => 'account-sign-in-post',
			'uses' => 'AccountController@postSignIn'
		]);

		/**
		 * Forgot password (POST)
		 */
		Route::post('/account/forgot-password', [
			'as'	=> 'account-forgot-password-post',
			'uses'	=> 'AccountController@postForgotPassword'
		]);
	});

	/**
	 * Forgot password (GET)
	 */
	Route::get('/account/forgot-password', [
		'as'	=> 'account-forgot-password',
		'uses'	=> 'AccountController@getForgotPassword'
	]);

	Route::get('/acount/recover/{code}', [
		'as'	=> 'account-recover',
		'uses'	=> 'AccountController@getRecover'
	]);

	/**
	 * Sign in (GET)
	 */
	Route::get('/account/signin', [
		'as' => 'account-sign-in',
		'uses' => 'AccountController@getSignIn'
	]);

	/**
	 * Create account (GET)
	 */
	Route::get('/account/create', [
		'as' => 'account-create',
		'uses' => 'AccountController@getCreate'
	]);

	/**
	 * Active account
	 */
	Route::get('/account/activate/{code}', [
		'as' => 'account-activate',
		'uses' => 'AccountController@getActivate'
	]);
});