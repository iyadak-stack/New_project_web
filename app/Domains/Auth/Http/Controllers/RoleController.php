<?php

namespace App\Domains\Auth\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function switchRole(Request $request)
    {
        $request->validate([
            'role' => ['required', 'in:tutor,student'],
        ]);

        $user = auth()->user();

        if ($request->role === 'tutor' && !$user->tutorProfile) {
            return back()->with('error', 'You do not have a tutor profile yet.');
        }

        if ($request->role === 'student' && !$user->studentProfile) {
            return back()->with('error', 'You do not have a student profile yet.');
        }

        $user->current_role = $request->role;
        $user->save();

        return back()->with(
            'success',
            'Switched to ' . ucfirst($request->role) . ' mode.'
        );
    }
}