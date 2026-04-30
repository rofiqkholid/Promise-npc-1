<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class PromiseUserController extends Controller
{
    public function index()
    {
        $users = \App\Models\User::orderBy('name')->get();
        return view('master.promise-users.index', compact('users'));
    }

    public function create()
    {
        return view('master.promise-users.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        \App\Models\User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => bcrypt($request->password),
        ]);

        return redirect()->route('master.promise-users.index')->with('success', 'Promise User created successfully.');
    }

    public function edit(\App\Models\User $promise_user)
    {
        return view('master.promise-users.edit', ['user' => $promise_user]);
    }

    public function update(Request $request, \App\Models\User $promise_user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $promise_user->nik . ',nik',
            'password' => 'nullable|string|min:8',
        ]);

        $updateData = [
            'name' => $request->name,
            'email' => $request->email,
        ];

        if ($request->filled('password')) {
            $updateData['password'] = bcrypt($request->password);
        }

        $promise_user->update($updateData);

        return redirect()->route('master.promise-users.index')->with('success', 'Promise User updated successfully.');
    }

    public function destroy(\App\Models\User $promise_user)
    {
        // Prevent deleting the current logged in user
        if ($promise_user->nik === auth()->id()) {
            return redirect()->route('master.promise-users.index')->with('error', 'You cannot delete your own account.');
        }

        $promise_user->delete();
        return redirect()->route('master.promise-users.index')->with('success', 'Promise User deleted successfully.');
    }
}
