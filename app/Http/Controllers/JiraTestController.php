<?php

namespace App\Http\Controllers;

use App\Services\JiraService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class JiraTestController extends Controller
{
    public function testTransition(Request $request, JiraService $jiraService)
    {
        $request->validate([
            'ticket_key' => 'required|string',
            'is_draft' => 'required|boolean'
        ]);

        $user = Auth::user();
        $ticketKey = $request->get('ticket_key');
        $isDraft = $request->get('is_draft');

        // First, let's test if we can access the ticket at all
        $ticketInfo = $jiraService->getTicketInformation($user, $ticketKey);
        
        if (empty($ticketInfo)) {
            // Let's also test if we can get tickets in progress to see if Jira is working at all
            $ticketsInProgress = $jiraService->getJiraTicketsInProgress($user);
            
            \Log::info('Jira connectivity test', [
                'ticket_key' => $ticketKey,
                'tickets_in_progress_count' => $ticketsInProgress->count(),
                'sample_tickets' => $ticketsInProgress->take(3)->pluck('key')->toArray()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => "Cannot access ticket {$ticketKey}. Found {$ticketsInProgress->count()} tickets in progress. Sample tickets: " . $ticketsInProgress->take(3)->pluck('key')->join(', ')
            ]);
        }

        // Log ticket info for debugging
        \Log::info('Ticket accessed successfully', [
            'ticket_key' => $ticketKey,
            'current_status' => $ticketInfo['fields']['status']['name'] ?? 'Unknown',
            'ticket_title' => $ticketInfo['fields']['summary'] ?? 'No title'
        ]);

        // If we can access the ticket, try the transition
        $result = $jiraService->transitionTicketStatus($user, $ticketKey, $isDraft);

        return response()->json($result);
    }
}