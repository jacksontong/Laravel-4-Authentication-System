<?php
Route::get('/', [
	'as' => 'home',
	'uses' => 'HomeController@home'
]);

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
	});

	/**
	 * Create account (GET)
	 */
	Route::get('/account/create', [
		'as' => 'account-create',
		'uses' => 'AccountController@getCreate'
	]);

	Route::get('/account/activate/{code}', [
		'as' => 'account-activate',
		'uses' => 'AccountController@getActivate'
	]);
});