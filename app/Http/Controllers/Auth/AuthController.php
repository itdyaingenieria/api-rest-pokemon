<?php

namespace App\Http\Controllers\Auth;

use Illuminate\Support\Facades\Auth;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\RegisterRequest;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Mail;
use App\Mail\ResetPasswordMail;
use App\Repositories\AuthRepository;
use App\Traits\ResponseTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use App\Models\User;


class AuthController extends Controller
{
    /**
     * Response trait to handle return responses.
     */
    use ResponseTrait;

    /**
     * Auth related functionalities.
     *
     * @var AuthRepository
     */
    public $authRepository;

    /**
     * Create a new AuthController instance.
     *
     * @return void
     */
    public function __construct(AuthRepository $ar)
    {
        $this->authRepository = $ar;
    }

    /**
     * @OA\POST(
     *     path="/api/auth/login",
     *     tags={"Authentication"},
     *     summary="Login",
     *     description="Login",
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="email", type="string", example="admin@itdyaingenieria.com"),
     *              @OA\Property(property="password", type="string", example="123456")
     *          ),
     *      ),
     *      @OA\Response(response=200, description="Login" ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     * @OA\SecurityScheme(
     *   securityScheme="Bearer",type="apiKey",description="JWT",name="Authorization",in="header",
     * )
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credentials = $request->only('email', 'password');

            if ($token = $this->guard()->attempt($credentials)) {
                // get authenticated user
                $user = $this->guard()->user();

                // invalidate previous token if present
                if (! empty($user->current_token) && $user->current_token !== $token) {
                    try {
                        JWTAuth::setToken($user->current_token)->invalidate();
                    } catch (\Exception $e) {
                        // ignore invalidation failures
                    }
                }

                // persist token and jti to enforce single session
                $payload = JWTAuth::setToken($token)->getPayload();
                $jti = $payload->get('jti');
                $user->current_token = $token;
                $user->current_jti = $jti;
                $user->save();

                $data = $this->respondWithToken($token);
            } else {
                return $this->responseError(null, 'Invalid Email and Password !', Response::HTTP_UNAUTHORIZED);
            }

            return $this->responseSuccess($data, 'Logged In Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/auth/register",
     *     tags={"Authentication"},
     *     summary="Register User",
     *     description="Register New User",
     *     @OA\RequestBody(
     *          @OA\JsonContent(
     *              type="object",
     *              @OA\Property(property="name", type="string", example="Ariana Abigail"),
     *              @OA\Property(property="email", type="string", example="ariannabigail@itdyaingenieria.com"),
     *              @OA\Property(property="password", type="string", example="999999"),
     *              @OA\Property(property="password_confirmation", type="string", example="999999")
     *          ),
     *      ),
     *      @OA\Response(response=200, description="Register New User Data" ),
     *      @OA\Response(response=400, description="Bad request"),
     *      @OA\Response(response=404, description="Resource Not Found")
     * )
     */
    public function register(RegisterRequest $request)
    {
        //$this->authorize('create', User::class);
        try {
            $requestData = $request->only('name', 'email', 'password', 'password_confirmation');
            $user = $this->authRepository->register($requestData);
            if ($user) {
                if ($token = $this->guard()->attempt($requestData)) {
                    $data =  $this->respondWithToken($token);
                    return $this->responseSuccess($data, 'User Registered and Logged in Successfully', Response::HTTP_OK);
                }
            }
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Send password reset link to email
     */
    public function forgotPassword(Request $request)
    {
        $request->validate(['email' => 'required|email']);
        $user = \App\Models\User::where('email', $request->input('email'))->first();
        if (! $user) {
            // do not reveal whether user exists
            return $this->responseSuccess(null, 'If the email exists, a reset link has been sent');
        }

        $token = Password::createToken($user);
        Mail::to($user->email)->send(new ResetPasswordMail($token));

        return $this->responseSuccess(null, 'If the email exists, a reset link has been sent');
    }

    /**
     * Reset password using token
     */
    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required|string',
            'email' => 'required|email',
            'password' => ['required', 'confirmed', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
        ]);

        $status = Password::reset($request->only('email', 'password', 'password_confirmation', 'token'), function ($user, $password) {
            $user->password = \Illuminate\Support\Facades\Hash::make($password);
            $user->save();
        });

        if ($status == Password::PASSWORD_RESET) {
            return $this->responseSuccess(null, 'Password reset successfully');
        }

        return $this->responseError(null, trans($status), 400);
    }

    /**
     * @OA\GET(
     *     path="/api/auth/me",
     *     tags={"Authentication"},
     *     summary="Authenticated User Profile",
     *     description="Authenticated User Profile",
     *     @OA\Response(response=200, description="Authenticated User Profile" ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function me(): JsonResponse
    {
        try {
            $data = $this->guard()->user();
            return $this->responseSuccess($data, 'Profile Fetched Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/auth/logout",
     *     tags={"Authentication"},
     *     summary="Logout",
     *     description="Logout",
     *     @OA\Response(response=200, description="Logout" ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function logout(): JsonResponse
    {
        try {
            $user = $this->guard()->user();
            // invalidate token in jwt and clear user's current token/jti
            try {
                JWTAuth::parseToken()->invalidate();
            } catch (\Exception $e) {
                // ignore
            }

            if ($user) {
                $user->current_token = null;
                $user->current_jti = null;
                $user->save();
            }
            return $this->responseSuccess(null, 'Logged out successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * @OA\POST(
     *     path="/api/auth/refresh",
     *     tags={"Authentication"},
     *     summary="Refresh",
     *     description="Refresh",
     *     @OA\Response(response=200, description="Refresh" ),
     *     @OA\Response(response=400, description="Bad request"),
     *     @OA\Response(response=404, description="Resource Not Found"),
     * )
     */
    public function refresh(): JsonResponse
    {
        try {
            $data = $this->respondWithToken($this->guard()->refresh());
            return $this->responseSuccess($data, 'Token Refreshed Successfully !');
        } catch (\Exception $e) {
            return $this->responseError(null, $e->getMessage(), Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Get the token array structure.
     *
     * @param  string $token
     *
     * @return \Illuminate\Http\JsonResponse
     */
    protected function respondWithToken($token): array
    {
        $data = [[
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => $this->guard()->factory()->getTTL() * 5 * 60,
            //'user' => $this->guard()->user()
            'user' => [
                'id' => $this->guard()->user()->id,
                'name' => $this->guard()->user()->name,
                'email' => $this->guard()->user()->email,
                'created_at' => $this->guard()->user()->created_at,
                'updated_at' => $this->guard()->user()->updated_at,
                //'roles' => $this->guard()->user()->getRoleNames(),
                //'permissions' => $this->guard()->user()->getAllPermissions()->pluck('name')
            ]
        ]];
        return $data[0];
    }

    /**
     * Get the guard to be used during authentication.
     *
     * @return \Illuminate\Contracts\Auth\Guard
     */
    public function guard(): \Illuminate\Contracts\Auth\Guard|\Illuminate\Contracts\Auth\StatefulGuard
    {
        return Auth::guard();
    }
}
