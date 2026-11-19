<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Models\Classroom;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone_number', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        $users = $query->latest()->paginate(15)->withQueryString();
        $classrooms = Classroom::orderBy('name')->get(['id', 'uuid', 'name', 'academic_year']);

        return view('users.index', compact('users', 'classrooms'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $subjects = Subject::all()->groupBy('category');

        return view('users.create', compact('subjects'));
    }

    public function store(StoreUserRequest $request)
    {
        $validated = $request->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'] ?? null,
            'phone_number' => $validated['phone_number'] ?? null,
            'password' => bcrypt($validated['password']),
            'role' => $validated['role'],
            'account_type' => config("permissions.role_to_account_type.{$validated['role']}"),
            'must_change_password' => true,
        ]);

        if (in_array($validated['role'], ['guru', 'bk'])) {
            $user->subjects()->sync($validated['subjects'] ?? []);
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil ditambahkan');
    }

    public function edit(string $id)
    {
        $user = $this->resolveUser($id);
        if ($user->role === 'superadmin' && auth()->user()?->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat mengelola akun Superadmin.');
        }

        $subjects = Subject::all()->groupBy('category');
        $userSubjects = $user->subjects->pluck('id')->toArray();

        return view('users.edit', compact('user', 'subjects', 'userSubjects'));
    }

    public function update(UpdateUserRequest $request, string $id)
    {
        $user = $this->resolveUser($id);
        $validated = $request->validated();

        $user->name = $validated['name'];
        $user->email = $validated['email'] ?? null;
        $user->phone_number = $validated['phone_number'] ?? null;
        $user->role = $validated['role'];
        $user->account_type = config("permissions.role_to_account_type.{$validated['role']}") ?? $user->account_type;

        if (! empty($validated['password'])) {
            $user->password = bcrypt($validated['password']);
        }

        $user->save();

        if (in_array($validated['role'], ['guru', 'bk'])) {
            $user->subjects()->sync($validated['subjects'] ?? []);
        } else {
            $user->subjects()->detach();
        }

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil diperbarui');
    }

    public function destroy(string $id)
    {
        $user = $this->resolveUser($id);

        if ($user->id === auth()->id()) {
            abort(403, 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        if ($user->role === 'superadmin' && auth()->user()?->role !== 'superadmin') {
            abort(403, 'Hanya Superadmin yang dapat menghapus akun Superadmin.');
        }

        $user->delete();

        return redirect()->route('users.index')->with('success', 'Pengguna berhasil dihapus');
    }

    private function resolveUser(string $identifier): User
    {
        return is_numeric($identifier)
            ? User::findOrFail((int) $identifier)
            : User::where('uuid', $identifier)->firstOrFail();
    }
}
