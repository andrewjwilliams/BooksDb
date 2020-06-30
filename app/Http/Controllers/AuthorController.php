<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Models\Author;
use JamesDordoy\LaravelVueDatatable\Http\Resources\DataTableCollectionResource;

class AuthorController extends Controller
{
    /**
	 * Display a listing of all the quthors.
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function index() {
        return response(Author::all()->jsonSerialize(), Response::HTTP_OK);
    }

    /**
	 * Generate the correct json for datatables based on any paremeters passed
	 * 
	 * @link https://github.com/jamesdordoy/Laravel-Vue-Datatable_Laravel-Package
	 * 
	 * @return DataTableCollectionResource
	 */
	public function datatable(Request $request)
    {   
        $length = $request->input('length');
        $orderBy = $request->input('column', 'id');
        $orderByDir = $request->input('dir', 'asc');
        $searchValue = $request->input('search');
        
		$data = Author::select('id', 'name')
				->where("name", "LIKE", "%$searchValue%")
				->orderBy($orderBy, $orderByDir)
				->paginate($length);

        return new DataTableCollectionResource($data);
    }

    /**
	 * Display the specified resource.
	 * 
	 * If a string is passed then look for a @ and split this key and value
	 * of an integer is passed then search by primary key
	 * 
	 * @TODO: Some nice error handling for non existant keys etc.
	 *
	 * @param  int | string  $id
	 * @return \Illuminate\Http\Response
	 */
	public function show($id) {
		if (is_numeric($id)) {				// If numeric then search by primary key
			return response(Author::find($id)->jsonSerialize(), Response::HTTP_OK);
		} elseif (substr_count($id, ':')) {
			$key = explode(':',$id); 
			return response(Author::where($key[0], $key[1])->get(), Response::HTTP_OK);
		} else {
			return response('',Response::HTTP_NOT_FOUND);
		}
	}


	/**
	 * Store a newly created resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function store(Request $request) {
		$author = new Author;

		foreach ($request->input() as $k => $v) {
			if (isset($v)) {
				$author->$k = $v;
			}
		} 

		if ($author->save()) {
			return response($author->jsonSerialize(), Response::HTTP_OK);
		}
	}

	/**
	 * Update the specified resource in storage.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function update(Request $request, $id) {
		$author = Author::findOrFail($id);

		foreach ($request->input() as $k => $v) {
			if (isset($v)) {
				$author->$k = $v;
			}
		} 
		
		$author->save();

		return response(null, Response::HTTP_OK);
	}

	/**
	 * Remove the specified resource from storage.
	 *
	 * @param  int  $id
	 * @return \Illuminate\Http\Response
	 */
	public function destroy($id) {
		Author::destroy($id);
		
		return response(null, Response::HTTP_OK);
	}

	/**
	 * Count
	 * 
	 * Return the number of books
	 *
	 * @return \Illuminate\Http\Response
	 */
	public function count() {
		return response(['count' => Author::all()->count()], Response::HTTP_OK);
	}
}
