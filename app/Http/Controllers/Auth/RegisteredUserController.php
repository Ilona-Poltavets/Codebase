<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Company;
use App\Models\Invite;
use App\Models\Role;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        $token = request()->route('token') ?? request()->query('token');
        $invite = null;
        if ($token) {
            $invite = Invite::with('company', 'role')
                ->where('token', $token)
                ->whereNull('accepted_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();
        }

        return view('auth.register', compact('invite', 'token'));
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'full_name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'register_type' => ['nullable', 'in:normal,owner,invite'],
            'company_name' => ['nullable', 'string', 'max:255'],
            'company_domain' => ['nullable', 'string', 'max:255'],
            'company_plan' => ['nullable', 'in:free,pro,pro_enterprise'],
            'invite_token' => ['nullable', 'string', 'size:64'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'full_name' => $request->full_name,
        ]);

        $registerType = $request->input('register_type', 'normal');

        if ($request->filled('invite_token')) {
            $invite = Invite::where('token', $request->invite_token)
                ->whereNull('accepted_at')
                ->where(function ($q) {
                    $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
                })
                ->first();

            if (! $invite || strtolower($invite->email) !== strtolower($request->email)) {
                return back()->withErrors(['email' => 'Invite is invalid or email does not match.'])->withInput();
            }

            $user->company_id = $invite->company_id;
            $user->save();

            $user->roles()->sync([$invite->role_id]);

            $invite->accepted_at = now();
            $invite->save();
        } elseif ($registerType === 'owner') {
            $companyName = $request->input('company_name');
            if (! $companyName) {
                return back()->withErrors(['company_name' => 'Company name is required for owner registration.'])->withInput();
            }

            $domain = Company::normalizeDomain($request->input('company_domain'), $companyName);
            $plan = $request->input('company_plan', 'free');

            $company = Company::create([
                'name' => $companyName,
                'domain' => $domain,
                'owner_id' => $user->id,
                'plan' => $plan,
            ]);

            $user->company_id = $company->id;
            $user->save();

            $ownerRoleId = Role::where('name', 'owner')->value('id');
            if ($ownerRoleId) {
                $user->roles()->sync([$ownerRoleId]);
            }
        } else {
            $memberRoleId = Role::where('name', 'member')->orWhere('name', 'user')->value('id');
            if ($memberRoleId) {
                $user->roles()->sync([$memberRoleId]);
            }
        }

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
