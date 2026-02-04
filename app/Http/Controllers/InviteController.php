<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Invite;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class InviteController extends Controller
{
    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $roles = Role::orderBy('name')->get();

        return view('admin.invite-create', compact('companies', 'roles'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'company_id' => 'required|exists:companies,id',
            'role_id' => 'required|exists:roles,id',
        ]);

        $invite = Invite::create([
            'email' => $request->email,
            'company_id' => $request->company_id,
            'role_id' => $request->role_id,
            'created_by' => $request->user()?->id,
            'token' => Str::random(64),
            'expires_at' => now()->addDays(7),
        ]);

        $link = route('invite.accept', ['token' => $invite->token]);

        if ($request->wantsJson()) {
            return response()->json(['invite' => $invite, 'link' => $link], 201);
        }

        return redirect()->route('admin.invites.create')->with('success', 'Invite created. Link: ' . $link);
    }
}
