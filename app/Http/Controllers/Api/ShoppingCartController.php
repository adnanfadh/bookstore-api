<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CartCreateRequest;
use App\Http\Resources\ShoppingCartResource;
use App\Models\Book;
use App\Models\ShoppingCart;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Auth;

class ShoppingCartController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    /**
     * Cart List
     * @OA\Get (
     *     path="/api/cart",
     *     tags={"Cart"},
     *      @OA\Response(
     *         response=200,
     *         description="Success",
     *         @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Admin"),
     *                      ),
     *                     @OA\Property(property="book", type="object",
     *                          @OA\Property(property="title", type="string", example="Burning Heat"),
     *                          @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                          @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                          @OA\Property(property="year_released", type="string", example="2021"),
     *                          @OA\Property(property="genre", type="string", example="thriller"),
     *                          @OA\Property(property="price", type="integer", example="120000"),
     *                      ),
     *                      @OA\Property(property="item", type="integer", example=1),
     *                      @OA\Property(property="total", type="integer", example=120000),
     *                 )
     *             )
     *         )
     *     ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function index(): JsonResponse
    {
        $user = Auth::user();
        $cart = ShoppingCart::where('customer_id', $user->id)->get();

        return response()->json([
            'data' => ShoppingCartResource::collection($cart),
            'items' => $cart->count(),
            'total' => $cart->sum('sub_total')
        ])->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Cart Store
     * @OA\Post (
     *     path="/api/cart",
     *     tags={"Cart"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="book_id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="qty",
     *                          type="integer"
     *                      ),
     *                 ),
     *                 example={
     *                     "book_id":1,
     *                     "qty":2
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Admin"),
     *                      ),
     *                     @OA\Property(property="book", type="object",
     *                          @OA\Property(property="title", type="string", example="Burning Heat"),
     *                          @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                          @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                          @OA\Property(property="year_released", type="string", example="2021"),
     *                          @OA\Property(property="genre", type="string", example="thriller"),
     *                          @OA\Property(property="price", type="integer", example="120000"),
     *                      ),
     *                      @OA\Property(property="item", type="integer", example=1),
     *                      @OA\Property(property="total", type="integer", example=120000),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function store(CartCreateRequest $request): JsonResponse
    {
        $data = $request->validated();
        $user = Auth::user();

        $book = Book::where('id', $data['book_id'])->first();

        if(!$book){
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "Book not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        $cart = ShoppingCart::where('customer_id', $user->id)->where('book_id', $data['book_id'])->first();

        if($cart){
            $cart->qty += $request->qty;
            $cart->sub_total = $book->price * $cart->qty;
            $cart->save();
        }else{
            $cart = new ShoppingCart($data);
            $cart->customer_id = $user->id;
            $cart->sub_total = $book->price * $request->qty;
            $cart->save();
        }


        return (new ShoppingCartResource($cart))->response()->setStatusCode(201);
    }

    /**
     * Update the specified resource in storage.
     */
    /**
     * Cart Update
     * @OA\Patch (
     *     path="/api/cart/{cart}",
     *     tags={"Cart"},
     *     @OA\Parameter(
     *         name="cart",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="cart Id"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="book_id",
     *                          type="integer"
     *                      ),
     *                      @OA\Property(
     *                          property="qty",
     *                          type="integer"
     *                      ),
     *                 ),
     *                 example={
     *                     "book_id":1,
     *                     "qty":2
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Admin"),
     *                      ),
     *                     @OA\Property(property="book", type="object",
     *                          @OA\Property(property="title", type="string", example="Burning Heat"),
     *                          @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                          @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                          @OA\Property(property="year_released", type="string", example="2021"),
     *                          @OA\Property(property="genre", type="string", example="thriller"),
     *                          @OA\Property(property="price", type="integer", example="120000"),
     *                      ),
     *                      @OA\Property(property="item", type="integer", example=1),
     *                      @OA\Property(property="total", type="integer", example=120000),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function update(Request $request, ShoppingCart $cart): JsonResponse
    {
        $book = Book::where('id', $cart->book_id)->first();
        try {

            if($request->qty < 1){
                $cart->delete();
                return response()->json([
                    'data' => true
                ])->setStatusCode(200);
            }else{
                $cart->fill($request->all());
                $cart->sub_total = $book->price * $cart->qty;
                $cart->save();
                return (new ShoppingCartResource($cart))->response()->setStatusCode(201);
            }

        } catch (\Throwable $th) {
            //throw $th;
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        $th->getMessage()
                    ]
                ]
            ])->setStatusCode(404));
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Cart Delete
     * @OA\Delete (
     *     path="/api/cart/{cart}",
     *     tags={"Cart"},
     *      @OA\Parameter(
     *         name="cart",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Cart Id"
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="boolean", example=true),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function destroy(ShoppingCart $cart): JsonResponse
    {
        try {
            //code...
            $cart->delete();

            return response()->json([
                'data' => true
            ])->setStatusCode(200);

        } catch (\Throwable $th) {
            //throw $th;
            return response()->json([
                'message' => $th->getMessage(),
            ]);
        }
    }
}
