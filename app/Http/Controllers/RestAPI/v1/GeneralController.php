<?php

namespace App\Http\Controllers\RestAPI\v1;

use App\Http\Controllers\Controller;
use App\Models\Contact;
use App\Models\GuestUser;
use App\Models\HelpTopic;
use App\Utils\Helpers;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use DB;

class GeneralController extends Controller
{
    public function faq(): JsonResponse
    {
        return response()->json(HelpTopic::orderBy('ranking')->get(), 200);
    }

    public function get_guest_id(Request $request): JsonResponse
    {
        $guestId = GuestUser::create([
            'ip_address' => $request->ip(),
            'created_at' => now(),
        ]);
        return response()->json(['guest_id' => $guestId?->id], 200);
    }

    public function contact_store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'mobile_number' => 'required',
            'subject' => 'required',
            'message' => 'required',
            'email' => 'required',
            'name' => 'required',
        ], [
            'name.required' => 'Name is Empty!',
            'mobile_number.required' => 'Mobile Number is Empty!',
            'subject.required' => ' Subject is Empty!',
            'message.required' => 'Message is Empty!',
            'email.required' => 'Email is Empty!',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => Helpers::validationErrorProcessor($validator)], 403);
        }

        Contact::create([
            'name' => $request['name'],
            'email' => $request['email'],
            'mobile_number' => $request['mobile_number'],
            'subject' => $request['subject'],
            'message' => $request['message']
        ]);

        return response()->json(['message' => 'your_message_send_successfully'], 200);
    }
    
     public function district()
    {
    $districts = DB::table('districts')->get();
    // ->map(function ($district) {
    //     // Cast the id to a string
    //     $district->id = (string) $district->id;
    //     return $district;
    // });

    return response()->json($districts);
}
    
    public function registerPoint()
    {
        $registerpoint = DB::table('business_settings')
        ->whereIn('id', [197, 198, 199, 200])
        ->orderBy('id', 'asc')->select('id', 'type', 'value')
        ->get();

    return response()->json($registerpoint);
    }
    
    public function updateNotification($id)
    {
      $registerpoint= DB::table('users')
        ->where('id', $id)->update(['notification' => 1]);

    return response()->json($registerpoint);
    }
    
    
    public function showThana($id)
{
    $thana = DB::table('thanas')->where('district_id', $id)->get();

    if (!$thana) {
        return response()->json(['message' => 'Thana not found'], 404);
    }

    return response()->json($thana);
}
}
