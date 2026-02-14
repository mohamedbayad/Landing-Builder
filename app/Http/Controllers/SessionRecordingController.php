<?php

namespace App\Http\Controllers;

use App\Models\SessionRecording;
use App\Models\Landing;
use App\Models\Workspace;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SessionRecordingController extends Controller
{
    /**
     * Display a listing of session recordings.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        // Get workspace IDs for the current user
        $workspaceIds = Workspace::where('user_id', $user->id)->pluck('id');
        
        // Get landing IDs through workspaces
        $landingIds = Landing::whereIn('workspace_id', $workspaceIds)->pluck('id');
        
        $query = SessionRecording::with('landingPage.landing')
            ->whereHas('landingPage', function ($q) use ($landingIds) {
                $q->whereIn('landing_id', $landingIds);
            })
            ->orderBy('created_at', 'desc');
        
        // Filter by landing
        if ($request->filled('landing_id')) {
            $query->whereHas('landingPage', function ($q) use ($request) {
                $q->where('landing_id', $request->landing_id);
            });
        }
        
        // Filter by date
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }
        
        // Get landings for filter dropdown
        $landings = Landing::whereIn('workspace_id', $workspaceIds)->get();
        
        $recordings = $query->paginate($request->get('per_page', 20));
        
        return view('recordings.index', compact('recordings', 'landings'));
    }
    
    /**
     * Get events data for a specific recording (for replay).
     */
    public function show(SessionRecording $recording)
    {
        $user = Auth::user();
        
        // Get workspace IDs for the current user
        $workspaceIds = Workspace::where('user_id', $user->id)->pluck('id')->toArray();
        
        // Get landing IDs through workspaces
        $landingIds = Landing::whereIn('workspace_id', $workspaceIds)->pluck('id')->toArray();
        
        if (!in_array($recording->landingPage->landing_id ?? null, $landingIds)) {
            abort(403);
        }
        
        return response()->json([
            'id' => $recording->id,
            'session_id' => $recording->session_id,
            'duration' => $recording->duration,
            'events' => $recording->events_data,
            'created_at' => $recording->created_at->format('M d, Y H:i'),
            'landing_name' => $recording->landingPage->landing->name ?? 'Unknown',
        ]);
    }
    
    /**
     * Delete a recording.
     */
    public function destroy(SessionRecording $recording)
    {
        $user = Auth::user();
        
        // Get workspace IDs for the current user
        $workspaceIds = Workspace::where('user_id', $user->id)->pluck('id')->toArray();
        
        // Get landing IDs through workspaces
        $landingIds = Landing::whereIn('workspace_id', $workspaceIds)->pluck('id')->toArray();
        
        if (!in_array($recording->landingPage->landing_id ?? null, $landingIds)) {
            abort(403);
        }
        
        $recording->delete();
        
        return redirect()->route('recordings.index')
            ->with('success', 'Recording deleted successfully.');
    }
}
