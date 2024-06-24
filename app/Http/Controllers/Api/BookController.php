<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\BookCreateRequest;
use App\Http\Resources\BookResource;
use App\Models\Book;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;

class BookController extends Controller
{
    public function __construct(){
        Gate::authorize('admin');
    }
    /**
     * Display a listing of the resource.
     */
    /**
     * Books List
     * @OA\Get (
     *     path="/api/books",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="Title",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by Title"
     *     ),
     *     @OA\Parameter(
     *         name="author",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by author"
     *     ),
     *     @OA\Parameter(
     *         name="publisher",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by publisher"
     *     ),
     *     @OA\Parameter(
     *         name="year_released",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by year_released"
     *     ),
     *     @OA\Parameter(
     *         name="genre",
     *         in="query",
     *         required=false,
     *         @OA\Schema(type="string"),
     *         description="Filter by genre"
     *     ),
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
     *                     @OA\Property(property="title", type="string", example="Burning Heat"),
     *                     @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                     @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                     @OA\Property(property="year_released", type="string", example="2021"),
     *                     @OA\Property(property="genre", type="string", example="thriller"),
     *                     @OA\Property(property="price", type="integer", example="120000"),
     *                 )
     *             )
     *         )
     *     ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function index(Request $request): JsonResponse
    {
        $book = Book::all();

        if($request->has('title')){
            $book = $book->filter(function ($key) use ($request){
                return false !== stripos($key['title'], $request->title);
            });
        }
        if($request->has('author')){
            $book = $book->filter(function ($key) use ($request){
                return false !== stripos($key['author'], $request->author);
            });
        }
        if($request->has('publisher')){
            $book = $book->filter(function ($key) use ($request){
                return false !== stripos($key['publisher'], $request->publisher);
            });
        }
        if($request->has('year_released')){
            $book = $book->filter(function ($key) use ($request){
                return false !== stripos($key['year_released'], $request->year_released);
            });
        }
        if($request->has('genre')){
            $book = $book->filter(function ($key) use ($request){
                return false !== stripos($key['genre'], $request->genre);
            });
        }

        if(!$book){
            return response()->json([
                'data' => [],
                'message' => 'Book Not Found'
            ]);
        }

        return (BookResource::collection($book))->response()->setStatusCode(200);
    }

    /**
     * Store a newly created resource in storage.
     */

    /**
     * Books Store
     * @OA\Post (
     *     path="/api/books",
     *     tags={"Books"},
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="author",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="publisher",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="year_released",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="genre",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="price",
     *                          type="integer"
     *                      )
     *                 ),
     *                 example={
     *                     "title":"Burning Heat",
     *                     "author":"Akiyoshi Rikako",
     *                     "publisher":"Elexmedia",
     *                     "year_released":"2021",
     *                     "genre":"thriller",
     *                     "price":120000
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                   @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                   @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                   @OA\Property(property="year_released", type="string", example="2021"),
     *                   @OA\Property(property="genre", type="string", example="thriller"),
     *                   @OA\Property(property="price", type="integer", example="120000")
     *              )
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function store(BookCreateRequest $request): JsonResponse
    {
        $data = $request->validated();

        $book = new Book($data);
        $book->save();

        return (new BookResource($book))->response()->setStatusCode(201);
    }

    /**
     * Display the specified resource.
     */

    /**
     * Books Detail
     * @OA\Get (
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *      @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Book Id / Unique Key"
     *     ),
     *      @OA\Response(
     *          response=200,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                   @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                   @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                   @OA\Property(property="year_released", type="string", example="2021"),
     *                   @OA\Property(property="genre", type="string", example="thriller"),
     *                   @OA\Property(property="price", type="integer", example="120000")
     *              ),
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function show(Book $book): BookResource
    {
        if (!$book) {
            throw new HttpResponseException(response()->json([
                'errors' => [
                    "message" => [
                        "not found"
                    ]
                ]
            ])->setStatusCode(404));
        }

        return new BookResource($book);
    }

    /**
     * Update the specified resource in storage.
     */

    /**
     * Books Update
     * @OA\Patch (
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="integer"),
     *         description="Book ID"
     *     ),
     *     @OA\RequestBody(
     *         @OA\MediaType(
     *             mediaType="application/json",
     *             @OA\Schema(
     *                 @OA\Property(
     *                      type="object",
     *                      @OA\Property(
     *                          property="title",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="author",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="publisher",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="year_released",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="genre",
     *                          type="string"
     *                      ),
     *                      @OA\Property(
     *                          property="price",
     *                          type="integer"
     *                      )
     *                 ),
     *                 example={
     *                     "title":"Burning Heat",
     *                     "author":"Akiyoshi Rikako",
     *                     "publisher":"Elexmedia",
     *                     "year_released":"2021",
     *                     "genre":"thriller",
     *                     "price":120000
     *                }
     *             )
     *         )
     *      ),
     *      @OA\Response(
     *          response=201,
     *          description="Success",
     *          @OA\JsonContent(
     *              @OA\Property(property="data", type="object",
     *                   @OA\Property(property="id", type="integer", example=1),
     *                   @OA\Property(property="title", type="string", example="Burning Heat"),
     *                   @OA\Property(property="author", type="string", example="Akiyoshi Rikako"),
     *                   @OA\Property(property="publisher", type="string", example="Elexmedia"),
     *                   @OA\Property(property="year_released", type="string", example="2021"),
     *                   @OA\Property(property="genre", type="string", example="thriller"),
     *                   @OA\Property(property="price", type="integer", example="120000")
     *              )
     *          )
     *      ),
     *      security={
     *         {"token": {}}
     *     }
     * )
     */
    public function update(Request $request, Book $book): BookResource
    {
        try {
            $book->fill($request->all());
            $book->save();

            return new BookResource($book);
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
     * Books Delete
     * @OA\Delete (
     *     path="/api/books/{book}",
     *     tags={"Books"},
     *      @OA\Parameter(
     *         name="book",
     *         in="path",
     *         required=true,
     *         @OA\Schema(type="string|integer"),
     *         description="Book Id / Unique Key"
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
    public function destroy(Book $book): JsonResponse
    {
        try {
            //code...
            $book->delete();
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
