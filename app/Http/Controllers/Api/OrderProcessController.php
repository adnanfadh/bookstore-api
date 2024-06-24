<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\OrderCreateRequest;
use App\Http\Requests\OrderPaymentRequest;
use App\Http\Resources\OrderProcessResource;
use App\Http\Resources\UserResource;
use App\Models\Inventory;
use App\Models\OrderProcess;
use App\Models\ShoppingCart;
use App\Models\User;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class OrderProcessController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    /**
     * Order Add
     * @OA\Post (
     *     path="/api/cart/order",
     *     tags={"Order"},
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
     *                     @OA\Property(property="order_id", type="string"),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Customer"),
     *                      ),
     *                     @OA\Property(
     *                          property="item",
     *                          type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="book_id", type="integer", example=1),
     *                              @OA\Property(property="qty", type="integer", example=2),
     *                              @OA\Property(property="sub_total", type="boolean", example=240000),
     *                          ),
     *                     ),
     *                     @OA\Property(property="total", type="integer", example=240000),
     *                     @OA\Property(property="payment_method", type="string", example=NULL),
     *                     @OA\Property(property="address", type="text", example=NULL),
     *                     @OA\Property(property="delivery_service", type="string", example=NULL),
     *                     @OA\Property(property="is_paid", type="boolean", example=false),
     *                     @OA\Property(property="payment_proof", type="string", example=NULL),
     *                     @OA\Property(property="status", type="string", example="Pending"),
     *                     @OA\Property(property="is_completed", type="boolean", example=false),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function store(OrderCreateRequest $request): JsonResponse
    {
        $user = Auth::user();

        $dataCart = [];
        $total = 0;

        foreach ($request->item as $key => $value) {
            # code...
            $cart = ShoppingCart::where('customer_id', $user->id)->where('book_id', $value)->first();

            $dataCart[$key] = [
                'book_id' => $value,
                'qty' => $cart->qty,
                'sub_total' => $cart->sub_total,
            ];

            $total += $cart->sub_total;
        }

        $item = json_encode($dataCart);

        $order = new OrderProcess();
        $order->user_id = $user->id;
        $order->item = $item;
        $order->total = $total;
        $order->save();

        return (new OrderProcessResource($order))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */
    public function show(OrderProcess $order)
    {

        if (!$order) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }


        return new OrderProcessResource($order);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Order Payment
     * @OA\patch (
     *     path="/api/order/payment/{order}",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order Id"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="payment_method",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="address",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="delivery_service",
     *                          type="string"
     *                      ),
     *                 ),
     *                 example={
     *                     "payment_method":"transfer - BCA",
     *                     "address":"Jl Bandung Jawa Barat",
     *                     "delivery_service":"JNT",
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
     *                     @OA\Property(property="order_id", type="string"),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Customer"),
     *                      ),
     *                     @OA\Property(
     *                          property="item",
     *                          type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="book_id", type="integer", example=1),
     *                              @OA\Property(property="qty", type="integer", example=2),
     *                              @OA\Property(property="sub_total", type="boolean", example=240000),
     *                          ),
     *                     ),
     *                     @OA\Property(property="total", type="integer", example=240000),
     *                     @OA\Property(property="payment_method", type="string", example="transfer - BCA"),
     *                     @OA\Property(property="address", type="text", example="Jl Bandung Jawa Barat"),
     *                     @OA\Property(property="delivery_service", type="string", example="JNT"),
     *                     @OA\Property(property="is_paid", type="boolean", example=false),
     *                     @OA\Property(property="payment_proof", type="string", example=NULL),
     *                     @OA\Property(property="status", type="string", example="Waiting"),
     *                     @OA\Property(property="is_completed", type="boolean", example=false),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function payment(OrderPaymentRequest $request, OrderProcess $order)
    {
        try {

            $order->fill($request->all());
            $order->recipient = $request->recipient ?? $order->customer->name;
            $order->order_id = str_pad(rand(0, pow(10, 3)-1), 3, '0', STR_PAD_LEFT);
            $order->total += $order->order_id;
            $order->status = "Waiting";
            $order->save();
            return (new OrderProcessResource($order))->response()->setStatusCode(201);

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
     * Order Payment Verify
     * @OA\patch (
     *     path="/api/order/payment-verify/{order}",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order Id"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="payment_proof",
     *                          type="string"
     *                      ),
     *                 ),
     *                 example={
     *                     "payment_proof":"bukti-tranfer.jpg",
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
     *                     @OA\Property(property="order_id", type="string"),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Customer"),
     *                      ),
     *                     @OA\Property(
     *                          property="item",
     *                          type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="book_id", type="integer", example=1),
     *                              @OA\Property(property="qty", type="integer", example=2),
     *                              @OA\Property(property="sub_total", type="boolean", example=240000),
     *                          ),
     *                     ),
     *                     @OA\Property(property="total", type="integer", example=240000),
     *                     @OA\Property(property="payment_method", type="string", example="transfer - BCA"),
     *                     @OA\Property(property="address", type="text", example="Jl Bandung Jawa Barat"),
     *                     @OA\Property(property="delivery_service", type="string", example="JNT"),
     *                     @OA\Property(property="is_paid", type="boolean", example=true),
     *                     @OA\Property(property="payment_proof", type="string", example="bukti-tranfer.jpg"),
     *                     @OA\Property(property="status", type="string", example="Waiting"),
     *                     @OA\Property(property="is_completed", type="boolean", example=false),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function paymentVerify(Request $request, OrderProcess $order)
    {
        $request->validate([
            'payment_proof' => ['required', 'max:2048']
        ]);

        try {

            $order->payment_proof = $request->payment_proof;
            $order->is_paid = true;
            $order->status = "Process";
            $order->save();

            if ($order->is_paid) {

                $item = collect(json_decode($order->item));
                // var_dump($value->book_id);
                foreach ($item as $key => $value) {

                    $inventory = Inventory::where('book_id', $value->book_id)->first();
                    $inventory->update([
                        'stock' => $inventory->stock - $value->qty,
                    ]);

                    $cart = ShoppingCart::where('customer_id', $order->user_id)->where('book_id', $value->book_id)->first();
                    $cart->delete();
                }

                # code...
            }
            return (new OrderProcessResource($order))->response()->setStatusCode(201);

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
     * Order Completed
     * @OA\patch (
     *     path="/api/order/completed/{order}",
     *     tags={"Order"},
     *     @OA\Parameter(
     *         name="order",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Order Id"
     *     ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *             type="object",
     *             @OA\Property(
     *                 property="data",
     *                 type="object",
     *                     @OA\Property(property="id", type="integer", example=1),
     *                     @OA\Property(property="order_id", type="string"),
     *                     @OA\Property(property="customer", type="object",
     *                          @OA\Property(property="id", type="number", example=1),
     *                          @OA\Property(property="name", type="string", example="test"),
     *                          @OA\Property(property="email", type="string", example="test@gmail.com"),
     *                          @OA\Property(property="role", type="string", example="Customer"),
     *                      ),
     *                     @OA\Property(
     *                          property="item",
     *                          type="array",
     *                          @OA\Items(type="object",
     *                              @OA\Property(property="book_id", type="integer", example=1),
     *                              @OA\Property(property="qty", type="integer", example=2),
     *                              @OA\Property(property="sub_total", type="boolean", example=240000),
     *                          ),
     *                     ),
     *                     @OA\Property(property="total", type="integer", example=240000),
     *                     @OA\Property(property="payment_method", type="string", example="transfer - BCA"),
     *                     @OA\Property(property="address", type="text", example="Jl Bandung Jawa Barat"),
     *                     @OA\Property(property="delivery_service", type="string", example="JNT"),
     *                     @OA\Property(property="is_paid", type="boolean", example=true),
     *                     @OA\Property(property="payment_proof", type="string", example="bukti-tranfer.jpg"),
     *                     @OA\Property(property="status", type="string", example="Selesai"),
     *                     @OA\Property(property="is_completed", type="boolean", example=true),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */

    public function completeVerify(OrderProcess $order)
    {
        try {
            $order->status = "Selesai";
            $order->is_completed = true;
            $order->save();

            return (new OrderProcessResource($order))->response()->setStatusCode(201);

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

}
