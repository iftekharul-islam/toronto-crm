<?php

namespace App\Http\Controllers\Auth;

use App\Helpers\CrmHelper;
use App\Models\User;
use App\Models\CompanyUser;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Cache;
use App\Providers\RouteServiceProvider;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Foundation\Auth\AuthenticatesUsers;

class LoginController extends Controller
{
	 /*
	 |--------------------------------------------------------------------------
	 | Login Controller
	 |--------------------------------------------------------------------------
	 |
	 | This controller handles authenticating users for the application and
	 | redirecting them to your home screen. The controller uses a trait
	 | to conveniently provide its functionality to your applications.
	 |
	 */
	 use AuthenticatesUsers, CrmHelper;
	 
	 /**
		* Where to redirect users after login.
		*
		* @var string
		*/
	 protected string $redirectTo = RouteServiceProvider::HOME;
	 
	 /**
		* Create a new controller instance.
		*
		* @return void
		*/
	 public function __construct()
	 {
			$this->middleware( 'guest' )->except( 'logout' );
	 }
	 
	 /**
		* Handle a login request to the application.
		*
		* @param Request $request
		*
		* @return Response
		*
		* @throws ValidationException
		*/
	 public function login(Request $request): Response
	 {
			$this->validateLogin( $request );
			$user = User::query()->where( 'email', $request->email )->whereNotNull( 'password' )->first();
			if ( $user ) {
				 $active_user = CompanyUser::query()->where( 'user_id', $user->id )->where( 'status', 1 )->first();
				 if ( ! $active_user ) {
						return redirect()->route( 'login' )->with( 'inactive-user',
							'Your account has been deactivated. Please contact with admin.' )->withInput();
				 }
				 if ($request->remember) {
					Cache::put('login_remember', [ 'email' => $request->email, 'password' => $request->password, 'time' => time()], config('boston_crm.login_time'));
				 }
			}
			// If the class is using the ThrottlesLogins trait, we can automatically throttle
			// the login attempts for this application. We'll key this by the username and
			// the IP address of the client making these requests into this application.
			if ( method_exists( $this, 'hasTooManyLoginAttempts' ) && $this->hasTooManyLoginAttempts( $request ) ) {
				 $this->fireLockoutEvent( $request );
				 
				 return $this->sendLockoutResponse( $request );
			}
			if ( $this->attemptLogin( $request ) ) {
				 if ( $request->hasSession() ) {
						$request->session()->put( 'auth.password_confirmed_at', time() );
				 }

				 $this->addActivity($user, "Logged in as ".$request->get( 'email' )." and Ip Address is ".request()->ip());
				 
				 return $this->sendLoginResponse( $request );
			}
			// If the login attempt was unsuccessful we will increment the number of attempts
			// to login and redirect the user back to the login form. Of course, when this
			// user surpasses their maximum number of attempts they will get locked out.
			$this->incrementLoginAttempts( $request );
			
			return $this->sendFailedLoginResponse( $request );
	 }
}
