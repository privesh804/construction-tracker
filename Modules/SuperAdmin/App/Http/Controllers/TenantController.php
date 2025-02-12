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
    protected $pagelimit = 10;
    protected $page = 1;
    protected $search = [];
    protected $sort = [];

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        // dd($request->user()->getAllPermissions());
        $paginateQuery = $request->all();

        if($paginateQuery ?? false){
            $this->pagelimit = $paginateQuery['pageSize'] ?? 10;
            $this->page = $paginateQuery['pageIndex'] ?? 1;
            $this->search = $paginateQuery['searchText'] ?? "";
            $this->sort = json_decode($paginateQuery['sorting'], true) ?? [];
        }

        $tenant = Tenant::with('domains');

        $columns = [
            'name',
            'email',
            'domain'
        ];

        if(isset($this->search) && !empty($this->search)){
            $this->pagelimit = 10;
            $this->page = 1;
            $tenant->where(function($query) use($columns){
                foreach ($columns as $column) {
                    if($column == 'name'){
                        $query->orWhere($column, "like", $this->search."%");
                    } else if($column == 'email'){
                        $query->orWhere('data', "like", $this->search."%");
                    }
                }
                $query->orWhereHas('domains', function($subquery){
                    $subquery->where('domain', "like", $this->search."%");
                });    
            });
        }

        

        if(isset($this->sort) && !empty($this->sort)){
            $this->pagelimit = 10;
            $this->page = 1;
            foreach ($this->sort as $sort) {
                $tenant->orderBy($sort['id'], ($sort['desc'] == true) ? 'DESC': 'ASC');
            }
        }
        $tenant = $tenant->paginate($this->pagelimit, ['*'], 'pageIndex', $this->page);

        return response()->json(['tenants' => $tenant], 200);
    }

    public function sendInvite(Request $request)
    {

        $request->validate([
            'email' => 'required|email:rfc,dns|unique:invite_tenants,email'
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
            return response()->json(['message'=>'Error', 'errors' => $e->getMessage()], 400);
        }

    }

    public function verifyInvite(Request $request, $code=false)
    {
        try {
            $invite = InviteTenant::where('code',$code)
                ->whereDate("valid_upto", ">=",now())->first();

            if(isset($invite) && !empty($invite)){
                return redirect(env('FRONTAPP_URL_TENANT').$code);
            } else {
                return redirect(env('FRONTAPP_URL_TENANT')."invalid");
            }


        } catch (\Exception $e) {
            return response()->json(['message'=>'Error', 'error' => $e->getMessage()], 400);
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
                    'email' => $invite ->email
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
