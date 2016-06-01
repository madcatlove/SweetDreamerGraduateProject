<?php

use App\Libraries\Utils;

/*
|--------------------------------------------------------------------------
| Application & Route Filters
|--------------------------------------------------------------------------
|
| Below you will find the "before" and "after" events for the application
| which may be used to do any work before or after a request into your
| application. Here you may also register your custom route filters.
|
*/

App::before(function($request)
{
	//
});


App::after(function($request, $response)
{
	//
});

/*
|--------------------------------------------------------------------------
| Authentication Filters
|--------------------------------------------------------------------------
|
| The following filters are used to verify that the user of the current
| session is logged into this application. The "basic" filter easily
| integrates HTTP Basic authentication for quick, simple checking.
|
*/

Route::filter('auth', function()
{
	if (Auth::guest())
	{
		if (Request::ajax())
		{
			return Response::make('Unauthorized', 401);
		}
		else
		{
			return Redirect::guest('login');
		}
	}
});


Route::filter('auth.basic', function()
{
	return Auth::basic();
});

/*
|--------------------------------------------------------------------------
| Guest Filter
|--------------------------------------------------------------------------
|
| The "guest" filter is the counterpart of the authentication filters as
| it simply checks that the current user is not logged in. A redirect
| response will be issued if they are, which you may freely change.
|
*/

Route::filter('guest', function()
{
	if (Auth::check()) return Redirect::to('/');
});

/*
|--------------------------------------------------------------------------
| CSRF Protection Filter
|--------------------------------------------------------------------------
|
| The CSRF filter is responsible for protecting your application against
| cross-site request forgery attacks. If this special token in a user
| session does not match the one given in this request, we'll bail.
|
*/

Route::filter('csrf', function()
{
	if (Session::token() !== Input::get('_token'))
	{
		throw new Illuminate\Session\TokenMismatchException;
	}
});



/**
 * @brief 토큰정보를 통해 유효한 멤버를 확인하기위한 필터
 */
Route::filter('verifyToken', function($route) {

	$memberToken = $route->getParameter('membertoken');
	$token = Membertoken::where('token', $memberToken)->first();

	Log::info('====> VerifyToken :: ' . $memberToken );

	if( !isset($token ) ) {
		return Utils::result( Utils::CANNOT_FIND_TOKEN, true);
	}

	$member = $token->member()->first();
	if( !isset($member)) {
		return Utils::result( Utils::CANNOT_FIND_TOKEN, true);
	}

	// 토큰 만료일 비교
	$limitTime = date('Y-m-d H:i:s', time() - 3600 );
	if( !( $token->expiretime >= $limitTime) ) {
		// 토큰 삭제
		$token->delete();

		return Utils::result( Utils::EXPIRED_TOKEN, true );
	}
	Log::info('\t\t\t Member :: ' . $member->getKey() . ' / ' . $member->userid);


	// member 토큰 시간 업데이트.
	$token->expiretime = date('Y-m-d H:i:s', time() );
	$token->save();

});