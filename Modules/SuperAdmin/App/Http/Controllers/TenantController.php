<?php

namespace Modules\SuperAdmin\App\Http\Controllers;

use Str;
Use App\Models\Tenant;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Modules\SuperAdmin\App\Models\InviteTenant;
use Modules\SuperAdmin\App\Notifications\InviteTenantNotification;

class TenantController extends Controller
{

    public function sendInvite(Request $request)
    {
        $request->validate([
            'email' => 'required|email:rfc,dns'
        ]);

        try {
            do {
                $token = Str::random(20);
            } while (InviteTenant::where('code', $token)->first());

            InviteTenant::create([
                'email' => $request->email,
                'code'  => $token,
                'valid_upto' => now()->addDay()
            ]);

            $url = \URL::temporarySignedRoute(
                'tenant.verify-invite', now()->addDay(), ['hash' => $token]
            );

            \Notification::route('mail', $request->input('email'))->notify(new InviteTenantNotification($url));

            return response()->json(["url"=>$url], 201);

        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }

    }

    public function verifyInvite(Request $request, $code=false)
    {
        try {
            $invite = InviteTenant::where('code',$code)
                ->whereDate("valid_upto", ">=",now())->first();

            if(isset($invite) && !empty($invite)){
                return response()->json(['status'=>'valid'], 200);
            } else {
                return response()->json(['status'=>'expired.'], 410);
            }


        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function store(Request $request)
    {

        $request->validate([
            'code' => 'required',
            'name' => 'required|min:2|max:100',
            'domain' => ['required'],
        ]);

        try {

            $invite = InviteTenant::where('code',$request->code)
                ->whereDate("valid_upto", ">=",now())->first();

            if(isset($invite)){
                $tenant = Tenant::create([
                    'name' => $request->name,
                ]);
    
                $tenant->domains()->create([
                    'domain' => Str::lower(str_replace(' ', '', $request->domain)).'.'.config('app.domain'),
                ]); 
                return response()->json(['organisation' => $tenant], 201);
            } else {
                return response()->json(['error' => "Verification code expired"], 410);
            }

        } catch (\Exception $e) {
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }

    public function delete($id)
    {
        try {
            $tenant = Tenant::with('domains')->find($id);
            $tenant->delete();
            return response()->json([], 204);
        } catch(\Exception $e){
            return response()->json(["message"=> "Error", "error" => $e->getMessage()], 400);
        }
    }
}
