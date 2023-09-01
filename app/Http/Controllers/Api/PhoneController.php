<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Phone;
use Illuminate\Http\Request;
use App\Http\Resources\PhoneResource;
use App\Http\Requests\PhoneRequest;
use Illuminate\Support\Facades\Storage;

class PhoneController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $phones = Phone::all();
        foreach ($phones as $phone) {
            if ($phone->image) {
                $imageUrl = asset('storage/' . substr($phone->image, 7));
                $phone->image = $imageUrl;
            }
        }
        return response()->json(['data' => $phones]);
    }

    /**
     * Store a newly created resource in storage.
     */

    public function store(PhoneRequest $request)
    {
        
        $data = $request->validated();

        if( $request->image){
            $picture = $request->image;
            $extension = explode('/', mime_content_type($picture))[1];
            $fileName = 'public/uploads/directory/' . time() . '.' . $extension;
            $replace = substr($picture, 0, strpos($picture, ',')+1); 
            $image = str_replace($replace, '', $picture); 
            $image = str_replace(' ', '+', $image); 
            Storage::disk('local')->put( $fileName, base64_decode($image));
            $data['image'] = $fileName; 
        }
        
        $phone = Phone::create($data);

        return response()->json([
            'message' => 'Phone entry created successfully',
            'phone' => $phone,
        ], 201);

    }

    /**
     * Display the specified resource.
     */
    public function show(Phone $phone)
    {
        if ($phone->image) {
            $imageUrl = asset('storage/' . substr($phone->image, 7));
            $phone->image = $imageUrl;
        }
        return new PhoneResource($phone);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(PhoneRequest $request, Phone $phone)
    {

        
        if ($request->image) {
            $picture = $request->image;
            $extension = explode('/', mime_content_type($picture))[1];
            $fileName = 'public/uploads/directory/' . time() . '.' . $extension;
            $replace = substr($picture, 0, strpos($picture, ',')+1); 
            $image = str_replace($replace, '', $picture); 
            $image = str_replace(' ', '+', $image); 
            Storage::disk('local')->put($fileName, base64_decode($image));
            $phone->image = $fileName; 
        }
      
        $phone->update($request->validated());

        return response()->json([
            'message' => 'Phone entry updated successfully',
            'phone' => $phone,
        ], 201);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Phone $phone)
    {
       $phone->delete();
       return response()->json(['message' => 'Successfully Deleted!']);
    }
    public function archive()
    {
        $archiveData = Phone::onlyTrashed()->get();
        foreach ($archiveData as $phone) {
            if ($phone->image) {
                $imageUrl = asset('storage/' . substr($phone->image, 7));
                $phone->image = $imageUrl;
            }
        }
        return response()->json([
            'data' => $archiveData,
        ]);
    }

    public function retrieve($id)
    {
        try {
            $data = Phone::withTrashed()->findOrFail($id);
            
            if ($data->trashed()) {
                $data->restore();
                return response()->json(['message' => 'Data restored successfully']);
            } else {
                return response()->json(['message' => 'Phone not found'], 404);
            }
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Phone not found'], 404);
        }
    }

}
