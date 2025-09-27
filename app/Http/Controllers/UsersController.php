<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;
use Illuminate\Validation\Rules\Password;

class UsersController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth','role:admin']);
    }

    public function index(Request $request)
    {
        $query = \App\Models\User::query()->with('roles');

        if ($request->filled('q')) {
            $q = $request->q;
            $query->where(function($qq) use ($q) {
                $qq->where('name', 'like', "%{$q}%")
                    ->orWhere('email', 'like', "%{$q}%");
            });
        }

        if ($request->filled('role')) {
            $query->whereHas('roles', fn($r) => $r->where('name', $request->role));
        }

        $users = $query->orderBy('name')->paginate(10)->withQueryString();

        // ¡OJO! => get() devuelve objetos Role (lo que queremos)
        $roles = \Spatie\Permission\Models\Role::orderBy('name')->get();

        return view('users.index', compact('users','roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email:rfc,dns','unique:users,email'],
            'password' => [
                'required',
                'confirmed',
                Password::min(8)        // mínimo 8 caracteres
                ->letters()         // al menos una letra
                ->mixedCase()       // mayúscula y minúscula
                ->numbers()         // al menos un número
                ->symbols()         // al menos un símbolo
                ->uncompromised(),  // no aparezcan en brechas conocidas
            ],
            'role'     => ['required','exists:roles,name'],
        ], [
            // Mensajes personalizados
            'password.min' => 'La contraseña debe tener al menos :min caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'role.required' => 'Debes seleccionar un rol.',
        ]);

        $user = \App\Models\User::create([
            'name'     => $validated['name'],
            'email'    => $validated['email'],
            'password' => bcrypt($validated['password']),
        ]);

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('ok','Usuario creado.');
    }

    public function update(Request $request, \App\Models\User $user)
    {
        $validated = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','email:rfc,dns', \Illuminate\Validation\Rule::unique('users','email')->ignore($user->id)],
            'password' => [
                'nullable',
                'confirmed',
                Password::min(8)->letters()->mixedCase()->numbers()->symbols()->uncompromised(),
            ],
            'role'     => ['required','exists:roles,name'],
        ]);

        $user->name  = $validated['name'];
        $user->email = $validated['email'];
        if (!empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }
        $user->save();

        $user->syncRoles([$validated['role']]);

        return redirect()->route('users.index')->with('ok','Usuario actualizado.');
    }

    public function destroy(User $user)
    {
        if (auth()->id() === $user->id) {
            return back()->withErrors('No puedes eliminar tu propio usuario.');
        }
        $user->delete();
        return redirect()->route('users.index')->with('ok','Usuario eliminado.');
    }
}
