<?php

namespace App\Http\Controllers;

use App\Models\JobApplications;
use App\Models\Opening;
use Illuminate\Http\Request;

class JobApplicationController extends Controller
{
    public function apply(Request $request, $slug)
    {
        $job = Opening::where('slug', $slug)->where('status', 1)->firstOrFail();

        $validated = $request->validate([
            'first_name'    => ['required', 'string', 'min:2', 'max:120'],
            'last_name'     => ['required', 'string', 'min:2', 'max:120'],
            'email'         => ['required', 'email:rfc,dns', 'max:190'],
            'phone'         => ['required', 'string', 'max:50'],
            'nationality'   => ['required', 'string', 'max:120'],
            'cover_letter'  => ['nullable', 'string', 'max:2000'],
            'cv'            => ['required', 'file', 'mimes:pdf,doc,docx', 'max:5120'],
        ]);

        $resumePath = $request->file('cv')->store('job-applications/resumes', 'public');

        JobApplications::create([
            'opening_id'   => $job->id,
            'first_name'   => $validated['first_name'],
            'last_name'    => $validated['last_name'],
            'email'        => $validated['email'],
            'phone'        => $validated['phone'],
            'cover_letter' => $validated['cover_letter'] ?? null,
            'resume_path'  => $resumePath,
            'nationality'  => $validated['nationality'],
            'status'       => 'pending',
        ]);

        return response()->json([
            'message' => 'Your application has been submitted successfully.',
        ]);
    }
}