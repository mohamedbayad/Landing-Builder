<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SessionRecording;
use App\Models\LandingPage;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class SessionController extends Controller
{
    /**
     * Create or update a session recording (Update or Create logic).
     * If session_id exists, appends events and sorts by timestamp.
     * If not, creates new record.
     */
    public function store(Request $request)
    {
        // DEBUG: Log incoming payload size to detect truncation
        $rawContent = $request->getContent();
        $contentLength = strlen($rawContent);
        $eventsRaw = $request->input('events');
        $eventsCount = is_array($eventsRaw) ? count($eventsRaw) : 0;
        
        // Check for FullSnapshot (type 2) in the payload
        $hasFullSnapshot = false;
        if (is_array($eventsRaw)) {
            foreach ($eventsRaw as $event) {
                if (isset($event['type']) && $event['type'] === 2) {
                    $hasFullSnapshot = true;
                    break;
                }
            }
        }
        
        \Log::info('SESSION RECORDING PAYLOAD RECEIVED', [
            'content_length_bytes' => $contentLength,
            'content_length_kb' => round($contentLength / 1024, 2),
            'content_length_mb' => round($contentLength / 1024 / 1024, 2),
            'events_count' => $eventsCount,
            'has_full_snapshot' => $hasFullSnapshot,
            'session_id' => $request->input('session_id'),
        ]);
        
        $validated = $request->validate([
            'landing_page_id' => 'required|exists:landing_pages,id',
            'events' => 'required|array',
            'session_id' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
        ]);
        
        $incomingSessionId = $validated['session_id'] ?? null;
        $newEvents = $validated['events'];
        
        // Filter out null/invalid events from incoming data
        $newEvents = array_filter($newEvents, function ($event) {
            return is_array($event) && isset($event['timestamp']) && isset($event['type']);
        });
        $newEvents = array_values($newEvents); // Re-index array
        
        // Check if session already exists
        $existingRecording = null;
        if ($incomingSessionId) {
            $existingRecording = SessionRecording::where('session_id', $incomingSessionId)->first();
        }
        
        if ($existingRecording) {
            // === UPDATE EXISTING SESSION ===
            // Decode existing events from database
            $existingEvents = $existingRecording->events_data ?? [];
            
            // If events_data is a string (shouldn't happen but safety check)
            if (is_string($existingEvents)) {
                $existingEvents = json_decode($existingEvents, true) ?? [];
            }
            
            // Filter out null/invalid events from existing data
            $existingEvents = array_filter($existingEvents, function ($event) {
                return is_array($event) && isset($event['timestamp']) && isset($event['type']);
            });
            
            // Merge existing and new events
            $mergedEvents = array_merge($existingEvents, $newEvents);
            
            // Sort events by timestamp to prevent player crash
            usort($mergedEvents, function ($a, $b) {
                $timestampA = $a['timestamp'] ?? 0;
                $timestampB = $b['timestamp'] ?? 0;
                return $timestampA <=> $timestampB;
            });
            
            // CRITICAL FIX: Remove duplicate FullSnapshot events (type 2)
            // rrweb player can only handle ONE FullSnapshot at the beginning
            // Keep only the FIRST FullSnapshot, remove all subsequent ones
            $hasFullSnapshot = false;
            $mergedEvents = array_filter($mergedEvents, function ($event) use (&$hasFullSnapshot) {
                // rrweb event types: 0=DomContentLoaded, 1=Load, 2=FullSnapshot, 3=IncrementalSnapshot, 4=Meta, 5=Custom
                if (isset($event['type']) && $event['type'] === 2) {
                    if ($hasFullSnapshot) {
                        // This is a duplicate FullSnapshot (from page reload) - remove it
                        return false;
                    }
                    $hasFullSnapshot = true;
                }
                return true;
            });
            $mergedEvents = array_values($mergedEvents); // Re-index array
            
            // Update duration (take the max of existing or new)
            $duration = max($validated['duration'] ?? 0, $existingRecording->duration ?? 0);
            
            // Save sorted and deduplicated events
            $existingRecording->events_data = $mergedEvents;
            $existingRecording->duration = $duration;
            $existingRecording->save();
            
            // VALIDATION: Check if data was truncated during save
            $savedData = $existingRecording->fresh()->events_data;
            $savedCount = is_array($savedData) ? count($savedData) : 0;
            $expectedCount = count($mergedEvents);
            
            if ($savedCount !== $expectedCount) {
                \Log::error('SESSION RECORDING DATA TRUNCATED!', [
                    'session_id' => $incomingSessionId,
                    'expected_events' => $expectedCount,
                    'saved_events' => $savedCount,
                    'data_size_bytes' => strlen(json_encode($mergedEvents)),
                ]);
            }
            
            // Log data size for debugging
            $jsonSize = strlen(json_encode($mergedEvents));
            \Log::info('Session recording updated', [
                'session_id' => $incomingSessionId,
                'event_count' => $expectedCount,
                'data_size_kb' => round($jsonSize / 1024, 2),
            ]);
            
            return response()->json([
                'success' => true,
                'session_id' => $incomingSessionId,
                'id' => $existingRecording->id,
                'action' => 'updated',
                'event_count' => count($mergedEvents),
                'data_size_kb' => round($jsonSize / 1024, 2),
            ], 200);
            
        } else {
            // === CREATE NEW SESSION ===
            $sessionId = $incomingSessionId ?? Str::uuid()->toString();
            
            // Sort new events by timestamp (in case they're out of order)
            usort($newEvents, function ($a, $b) {
                $timestampA = $a['timestamp'] ?? 0;
                $timestampB = $b['timestamp'] ?? 0;
                return $timestampA <=> $timestampB;
            });
            
            $ip = $request->ip();
            $location = $this->getLocationFromIp($ip);
            
            $recording = SessionRecording::create([
                'session_id' => $sessionId,
                'landing_page_id' => $validated['landing_page_id'],
                'visitor_ip' => $ip,
                'location' => $location,
                'duration' => $validated['duration'] ?? 0,
                'events_data' => $newEvents,
            ]);
            
            // VALIDATION: Check if data was truncated during save
            $savedData = $recording->fresh()->events_data;
            $savedCount = is_array($savedData) ? count($savedData) : 0;
            $expectedCount = count($newEvents);
            
            if ($savedCount !== $expectedCount) {
                \Log::error('SESSION RECORDING DATA TRUNCATED!', [
                    'session_id' => $sessionId,
                    'expected_events' => $expectedCount,
                    'saved_events' => $savedCount,
                    'data_size_bytes' => strlen(json_encode($newEvents)),
                ]);
            }
            
            // Log data size for debugging
            $jsonSize = strlen(json_encode($newEvents));
            \Log::info('Session recording created', [
                'session_id' => $sessionId,
                'event_count' => $expectedCount,
                'data_size_kb' => round($jsonSize / 1024, 2),
            ]);
            
            return response()->json([
                'success' => true,
                'session_id' => $sessionId,
                'id' => $recording->id,
                'action' => 'created',
                'event_count' => count($newEvents),
                'data_size_kb' => round($jsonSize / 1024, 2),
            ], 201);
        }
    }
    
    /**
     * Append events to an existing session recording.
     * Uses string manipulation for performance with large LONGTEXT fields.
     */
    public function append(Request $request, string $sessionId)
    {
        $validated = $request->validate([
            'events' => 'required|array',
            'duration' => 'nullable|integer',
        ]);
        
        $recording = SessionRecording::where('session_id', $sessionId)->firstOrFail();
        
        // Get raw events_data from database (bypasses Eloquent cast)
        $existingJson = $recording->getRawOriginal('events_data');
        $newEventsJson = json_encode($validated['events']);
        
        // Handle merging using string manipulation (Method A - Performance Optimized)
        if (empty($existingJson) || $existingJson === '[]' || $existingJson === 'null') {
            // No existing data, use new events directly
            $mergedJson = $newEventsJson;
        } else {
            // Both have data - merge them
            // Remove trailing ] from existing: "[...]" -> "[..."
            $existingTrimmed = rtrim($existingJson);
            if (substr($existingTrimmed, -1) === ']') {
                $existingTrimmed = substr($existingTrimmed, 0, -1);
            }
            
            // Remove leading [ from new: "[...]" -> "...]"
            $newTrimmed = ltrim($newEventsJson);
            if (substr($newTrimmed, 0, 1) === '[') {
                $newTrimmed = substr($newTrimmed, 1);
            }
            
            // Concatenate with comma and close array
            // Result: "[existing...,new...]"
            $mergedJson = $existingTrimmed . ',' . $newTrimmed;
        }
        
        // Update duration if provided
        $duration = $validated['duration'] ?? $recording->duration;
        
        // Use raw update to bypass Eloquent JSON casting issues
        \DB::table('session_recordings')
            ->where('id', $recording->id)
            ->update([
                'events_data' => $mergedJson,
                'duration' => $duration,
                'updated_at' => now(),
            ]);
        
        // Get approximate event count for response
        $eventCount = substr_count($mergedJson, '"type":');
        
        return response()->json([
            'success' => true,
            'event_count' => $eventCount,
        ]);
    }
    
    /**
     * Get approximate location from IP address.
     * In production, consider using a proper GeoIP service.
     */
    private function getLocationFromIp(string $ip): ?string
    {
        // Skip for local/private IPs
        if (in_array($ip, ['127.0.0.1', '::1']) || 
            preg_match('/^(10\.|172\.(1[6-9]|2[0-9]|3[01])\.|192\.168\.)/', $ip)) {
            return 'Local';
        }
        
        try {
            // Using ip-api.com free service (consider using a paid service in production)
            $response = @file_get_contents("http://ip-api.com/json/{$ip}?fields=city,country");
            if ($response) {
                $data = json_decode($response, true);
                if (isset($data['city']) && isset($data['country'])) {
                    return "{$data['city']}, {$data['country']}";
                }
            }
        } catch (\Exception $e) {
            // Silently fail
        }
        
        return null;
    }
}
