<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\AnnouncementRequest;
use App\Models\Announcement;
use App\Models\AnnouncementRecipient;
use App\Models\Unit;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class AnnouncementController extends Controller
{
    /**
     * Display a listing of announcements (admin only).
     */
    public function index(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can list all announcements
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $includeDeleted = $request->boolean('include_deleted', false);
        $status = $request->get('status'); // 'active', 'expired', or null for all
        
        $query = Announcement::forTenant($user->tenant_id)
            ->with(['creator', 'recipients.unit', 'recipients.resident']);
            
        if ($includeDeleted) {
            $query->withTrashed();
        }
        
        // Filter by status
        if ($status === 'active') {
            $query->active();
        } elseif ($status === 'expired') {
            $query->expired();
        }
        
        $announcements = $query->orderBy('created_at', 'desc')->get();
        
        return response()->json([
            'data' => $announcements,
            'meta' => [
                'total' => $announcements->count(),
                'include_deleted' => $includeDeleted,
                'status' => $status,
            ],
        ]);
    }

    /**
     * Get announcements for the current user (all authenticated users).
     */
    public function forUser(Request $request): JsonResponse
    {
        $user = $request->user();
        
        // Get all active announcements for the tenant
        $announcements = Announcement::forTenant($user->tenant_id)
            ->active()
            ->with(['creator', 'recipients.unit', 'recipients.resident'])
            ->orderBy('created_at', 'desc')
            ->get();
        
        // Filter announcements where user is a recipient
        $userAnnouncements = $announcements->filter(function ($announcement) use ($user) {
            foreach ($announcement->recipients as $recipient) {
                // Check if user matches any recipient criteria
                if ($recipient->recipient_type === 'all_members' && $user->isResident()) {
                    return true;
                }
                
                if ($recipient->recipient_type === 'all_admins' && $user->isAdmin()) {
                    return true;
                }
                
                if ($recipient->recipient_type === 'resident' && $recipient->recipient_id == $user->id) {
                    return true;
                }
                
                if ($recipient->recipient_type === 'unit') {
                    // Check if user owns this unit
                    $userOwnedUnitIds = $user->ownedUnits()->pluck('units.id')->toArray();
                    if (in_array($recipient->recipient_id, $userOwnedUnitIds)) {
                        return true;
                    }
                }
            }
            return false;
        })->values();
        
        return response()->json([
            'data' => $userAnnouncements,
            'meta' => [
                'total' => $userAnnouncements->count(),
            ],
        ]);
    }

    /**
     * Store a newly created announcement (admin only).
     */
    public function store(AnnouncementRequest $request): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can create announcements
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $validated = $request->validated();
        
        // Create the announcement
        $announcement = Announcement::create([
            'tenant_id' => $user->tenant_id,
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'removal_date' => $validated['removal_date'] ?? null,
            'created_by' => $user->id,
        ]);
        
        // Create recipients
        foreach ($validated['recipients'] as $recipientData) {
            AnnouncementRecipient::create([
                'announcement_id' => $announcement->id,
                'recipient_type' => $recipientData['recipient_type'],
                'recipient_id' => $recipientData['recipient_id'] ?? null,
            ]);
        }
        
        // Load relationships
        $announcement->load(['creator', 'recipients.unit', 'recipients.resident']);
        
        // Send webhook to n8n for notifications
        $this->sendN8nWebhook($announcement, $user);
        
        return response()->json([
            'data' => $announcement,
            'message' => 'Announcement created successfully',
        ], 201);
    }

    /**
     * Display the specified announcement (admin only).
     */
    public function show(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can view individual announcements
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $announcement = Announcement::forTenant($user->tenant_id)
            ->with(['creator', 'recipients.unit', 'recipients.resident'])
            ->withTrashed()
            ->findOrFail($id);
        
        return response()->json([
            'data' => $announcement,
        ]);
    }

    /**
     * Update the specified announcement (admin only, creator only).
     */
    public function update(AnnouncementRequest $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can update announcements
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $announcement = Announcement::forTenant($user->tenant_id)
            ->findOrFail($id);
        
        // Only the creator can update
        if ($announcement->created_by !== $user->id) {
            return response()->json(['message' => 'You can only edit announcements you created'], 403);
        }
        
        $validated = $request->validated();
        
        // Update the announcement
        $announcement->update([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'removal_date' => $validated['removal_date'] ?? null,
        ]);
        
        // Delete existing recipients
        $announcement->recipients()->delete();
        
        // Create new recipients
        foreach ($validated['recipients'] as $recipientData) {
            AnnouncementRecipient::create([
                'announcement_id' => $announcement->id,
                'recipient_type' => $recipientData['recipient_type'],
                'recipient_id' => $recipientData['recipient_id'] ?? null,
            ]);
        }
        
        // Load relationships
        $announcement->load(['creator', 'recipients.unit', 'recipients.resident']);
        
        return response()->json([
            'data' => $announcement,
            'message' => 'Announcement updated successfully',
        ]);
    }

    /**
     * Remove the specified announcement (admin only, soft delete).
     */
    public function destroy(Request $request, string $id): JsonResponse
    {
        $user = $request->user();
        
        // Only admins can delete announcements
        if (!$user->isAdmin()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }
        
        $announcement = Announcement::forTenant($user->tenant_id)
            ->findOrFail($id);
        
        $announcement->delete();
        
        return response()->json([
            'message' => 'Announcement deleted successfully',
        ]);
    }

    /**
     * Send webhook to n8n for notifications.
     */
    private function sendN8nWebhook(Announcement $announcement, User $user): void
    {
        $webhookUrl = config('services.n8n.webhook_url');
        
        if (!$webhookUrl) {
            Log::warning('N8N webhook URL not configured. Skipping notification.');
            return;
        }
        
        try {
            // Collect recipient emails
            $recipientEmails = $this->collectRecipientEmails($announcement, $user->tenant_id);
            
            // Remove creator email from BCC if present (they're in To field)
            $bccEmails = array_filter($recipientEmails, function($email) use ($user) {
                return $email !== $user->email;
            });
            
            // Prepare payload with type field for n8n switch node
            $payload = [
                'type' => 'announcement', // Required for n8n switch node routing
                'tenant_name' => $user->tenant->name ?? 'HOA', // Top-level for easy access
                'to' => $user->email, // Creator email in To (required by some email systems)
                'bcc' => array_values($bccEmails), // All recipients in BCC only (excluding creator)
                'frontend_url' => env('FRONTEND_URL', 'http://localhost:3000'),//frontend url for the announcement
                'announcement' => [
                    'id' => $announcement->id,
                    'subject' => $announcement->subject,
                    'message' => $announcement->message,
                    'removal_date' => $announcement->removal_date?->toDateString(),
                    'created_at' => $announcement->created_at->toIso8601String(),
                ],
                'tenant' => [
                    'id' => $user->tenant_id,
                    'name' => $user->tenant->name ?? null,
                ],
                'recipients' => $recipientEmails, // Keep for backward compatibility
                'recipient_types' => $announcement->recipients->map(function ($r) {
                    return [
                        'type' => $r->recipient_type,
                        'id' => $r->recipient_id,
                    ];
                })->toArray(),
            ];
            
            Http::timeout(10)->post($webhookUrl, $payload);
            
            Log::info('N8N webhook sent successfully for announcement', [
                'announcement_id' => $announcement->id,
                'recipient_count' => count($recipientEmails),
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send N8N webhook', [
                'announcement_id' => $announcement->id,
                'error' => $e->getMessage(),
            ]);
            // Don't throw - webhook failure shouldn't block announcement creation
        }
    }

    /**
     * Collect recipient emails based on recipient types.
     */
    private function collectRecipientEmails(Announcement $announcement, int $tenantId): array
    {
        $emails = [];
        
        foreach ($announcement->recipients as $recipient) {
            if ($recipient->recipient_type === 'all_members') {
                // Get all resident emails
                $residentEmails = User::forTenant($tenantId)
                    ->byRole('resident')
                    ->where('is_active', true)
                    ->pluck('email')
                    ->toArray();
                $emails = array_merge($emails, $residentEmails);
            } elseif ($recipient->recipient_type === 'all_admins') {
                // Get all admin emails
                $adminEmails = User::forTenant($tenantId)
                    ->byRole('admin')
                    ->where('is_active', true)
                    ->pluck('email')
                    ->toArray();
                $emails = array_merge($emails, $adminEmails);
            } elseif ($recipient->recipient_type === 'resident' && $recipient->recipient_id) {
                // Get specific resident email
                $resident = User::forTenant($tenantId)
                    ->find($recipient->recipient_id);
                if ($resident && $resident->email) {
                    $emails[] = $resident->email;
                }
            } elseif ($recipient->recipient_type === 'unit' && $recipient->recipient_id) {
                // Get emails of all owners of this unit
                $unit = Unit::forTenant($tenantId)->find($recipient->recipient_id);
                if ($unit) {
                    $unitOwnerEmails = $unit->owners()
                        ->where('is_active', true)
                        ->pluck('email')
                        ->toArray();
                    $emails = array_merge($emails, $unitOwnerEmails);
                }
            }
        }
        
        // Remove duplicates and return
        return array_unique($emails);
    }
}
