<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\UserLoginRequest;
use App\Http\Requests\UserRegisterRequest;
use App\Http\Resources\UserResource;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    /**
     * Register
     * @OA\Post (
     *     path="/api/register",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          type="string"
     *                      )
     *                 ),
     *                 example={
     *                     "name":"test",
     *                     "email":"test@gmail.com",
     *                     "password":"12345"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="number", example=1),
     *                   @OA\Property(property="name", type="string", example="test"),
     *                   @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                   @OA\Property(property="role", type="string", example="customer"),
     *              ),
     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="email", type="array", collectionFormat="multi",
     *                     @OA\Items(type="string",example="The email has already been taken.",)
     *                 ),
     *              ),
     *          )
     *      )
     * )
     */
    public function register(UserRegisterRequest $request): JsonResponse
    {

        $data = $request->validated();

        $user = new User($data);
        $user->password = Hash::make($data['password']);
        $user->role = 'customer';
        $user->save();


        return (new UserResource($user))->response()->setStatusCode(201);
    }

    /**
     * Login
     * @OA\Post (
     *     path="/api/login",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="password",
     *                          type="string"
     *                      )
     *                 ),
     *                 example={
     *                     "email":"test@example.com",
     *                     "password":"12345"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="number", example=1),
     *                   @OA\Property(property="name", type="string", example="test"),
     *                   @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                   @OA\Property(property="role", type="string", example="customer"),
     *              ),
     *              @OA\Property(property="token", type="string", example="randomtokenasfhajskfhajf398rureuuhfdshk"),

     *          )
     *      ),
     *      @OA\Response(
     *          response=400,
     *          description="Validation error",
     *          @OA\JsonContent(
     *              @OA\Property(property="errors", type="object",
     *                 @OA\Property(property="message", type="array", collectionFormat="multi",
     *                     @OA\Items(type="string",example="email or password wrong",)
     *                 ),
     *              ),
     *          )
     *      )
     * )
     */
    public function login(UserLoginRequest $request): JsonResponse
    {

        //get credentials from request
        $credentials = $request->only('email', 'password');

        //if auth failed
        if (!$token = auth()->guard('api')->attempt($credentials)) {
            throw new HttpResponseException(response([
                "errors" => [
                    "message" => [
                        "email or password wrong"
                    ]
                ]
            ], 401));
        }

        return response()->json([
            'data' => new UserResource(auth()->guard('api')->user()),
            'token' => $token
        ])->setStatusCode(201);
    }

    /**
     * Logout
     * @OA\Post (
     *     path="/api/logout",
     *     tags={"Auth"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *
     *                 ),
     *                 example={}
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", example={}),
     *              @OA\Property(property="message", type="string", example="Logout Success!"),
     *          )
     *      ),
     *      @OA\Response(
     *          response=401,
     *          description="Invalid token",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object", example={}),
     *              @OA\Property(property="message", type="string", example="Unauthenticated"),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function logout(Request $request)
    {
        try {
            //code...
            $removeToken = JWTAuth::invalidate(JWTAuth::getToken());

            if ($removeToken) {
                return response()->json([
                    'data' => [],
                    'message' => 'Logout Success!',
                ])->setStatusCode(200);
            }
        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'data' => [],
                'message' => 'Unauthenticated',
            ])->setStatusCode(401);
        }
    }

    /**
     * Users List
     * @OA\Get (
     *     path="/api/users/list",
     *     tags={"Users"},
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(
     *                     type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="name", type="string", example="test"),
     *                     @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                     @OA\Property(property="role", type="string", example="admin")
     *                 )
     *             )
     *         )
     *     ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function list(): JsonResponse
    {
        Gate::authorize('admin');

        $user = User::get();

        return (UserResource::collection($user))->response()->setStatusCode(200);
    }

    /**
     * User Profile
     * @OA\Get (
     *     path="/api/profile",
     *     tags={"Users"},
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="number", example=1),
     *                   @OA\Property(property="name", type="string", example="test"),
     *                   @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                   @OA\Property(property="role", type="string", example="customer"),
     *              ),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function profile(): UserResource
    {
        $user = Auth::user();
        return new UserResource($user);
    }

    /**
     * Login
     * @OA\Patch (
     *     path="/api/users/update/{user}",
     *     tags={"Users"},
     *      @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="User ID"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="name",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="email",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="role",
     *                          type="string"
     *                      ),
     *                 ),
     *                 example={
     *                     "name":"test",
     *                     "email":"test@gmail.com",
     *                     "role":"Admin"
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="number", example=1),
     *                   @OA\Property(property="name", type="string", example="test"),
     *                   @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                   @OA\Property(property="role", type="string", example="Admin"),
     *              ),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function update(Request $request, User $user): UserResource
    {
        $user->name = $request->name ?? $user->name;
        $user->role = $request->role ?? $user->role;
        if (isset($request->password)) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return new UserResource($user);
    }
}
