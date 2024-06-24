<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\InventoryCreateRequest;
use App\Http\Resources\InventoryResource;
use App\Models\Book;
use App\Models\Inventory;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class InventoryController extends Controller
{
    public function __construct(){
        Gate::authorize('admin');
    }
    /**
     * Display a listing of the resource.
     */

    /**
     * Inventory List
     * @OA\Get (
     *     path="/api/inventory",
     *     tags={"Inventory"},
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
     *                     @OA\Property(property="book", type="object",
     *                          @OA\Property(property="title", type="string", example="Burning Heat"),
     *                          @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                          @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                          @OA\Property(property="year_released", type="string", example="2021"),
     *                          @OA\Property(property="genre", type="string", example="thriller"),
     *                          @OA\Property(property="price", type="integer", example="120000"),
     *                      ),
     *                      @OA\Property(property="Stock", type="integer", example=10),
     *                      @OA\Property(property="is_available", type="boolean", example=true),
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
        $inventory = Inventory::all();

        return (InventoryResource::collection($inventory))->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Inventory Store
     * @OA\Post (
     *     path="/api/inventory",
     *     tags={"Inventory"},
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
     *                          property="stock",
     *                          type="integer"
     *                      ),
     *                 ),
     *                 example={
     *                     "book_id":1,
     *                     "stock":10
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
     *                 @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="book", type="object",
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                    @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                    @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                    @OA\Property(property="year_released", type="string", example="2021"),
     *                    @OA\Property(property="genre", type="string", example="thriller"),
     *                    @OA\Property(property="price", type="integer", example="120000"),
     *                ),
     *                @OA\Property(property="Stock", type="integer", example=10),
     *                @OA\Property(property="is_available", type="boolean", example=true),
     *             )
     *         )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function store(InventoryCreateRequest $request): JsonResponse
    {
        $data = $request->validated();

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

        $inventory = new Inventory($data);
        $inventory->save();

        return (new InventoryResource($inventory))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */

    /**
     * Display the specified resource.
     */

    /**
     * Inventory Detail
     * @OA\Get (
     *     path="/api/inventory/{inventory}",
     *     tags={"Inventory"},
     *      @OA\Parameter(
     *         name="inventory",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Inventory Id / Unique Key"
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="book", type="object",
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                    @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                    @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                    @OA\Property(property="year_released", type="string", example="2021"),
     *                    @OA\Property(property="genre", type="string", example="thriller"),
     *                    @OA\Property(property="price", type="integer", example="120000"),
     *                ),
     *              @OA\Property(property="Stock", type="integer", example=10),
     *              @OA\Property(property="is_available", type="boolean", example=true),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function show(Inventory $inventory): InventoryResource
    {
        if (!$inventory) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return new InventoryResource($inventory);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Inventory Update
     * @OA\patch (
     *     path="/api/inventory/{inventory}",
     *     tags={"Inventory"},
     *      @OA\Parameter(
     *         name="inventory",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Inventory Id / Unique Key"
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
     *                          property="stock",
     *                          type="integer"
     *                      ),
     *                 ),
     *                 example={
     *                     "book_id":1,
     *                     "stock":10
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="id", type="integer", example=1),
     *                 @OA\Property(property="book", type="object",
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                    @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                    @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                    @OA\Property(property="year_released", type="string", example="2021"),
     *                    @OA\Property(property="genre", type="string", example="thriller"),
     *                    @OA\Property(property="price", type="integer", example="120000"),
     *                ),
     *              @OA\Property(property="Stock", type="integer", example=10),
     *              @OA\Property(property="is_available", type="boolean", example=true),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function update(Request $request, Inventory $inventory):InventoryResource
    {
        try {
            $inventory->fill($request->all());
            $inventory->save();

            return new InventoryResource($inventory);
        } catch (\Throwable $th) {
            //throw $th;
            throw new HttpResponseException(response([
                'status' => false,
                'message' => $th->getMessage(),
            ]));

        }
    }

    /**
     * Remove the specified resource from storage.
     */
    /**
     * Inventory Delete
     * @OA\Delete (
     *     path="/api/inventory/{inventory}",
     *     tags={"Inventory"},
     *      @OA\Parameter(
     *         name="inventory",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Inventory Id / Unique Key"
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
    public function destroy(Inventory $inventory): JsonResponse
    {
        try {
            //code...
            $inventory->delete();
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
