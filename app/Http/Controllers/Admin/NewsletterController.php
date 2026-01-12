<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterEmail;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class NewsletterController extends Controller
{
    /**
     * Ensure only super admins can access newsletter management.
     * Routes are already protected by role:super-admin middleware, but this provides defense-in-depth.
     */
    private function ensureSuperAdmin()
    {
        if (!auth()->user()?->isSuperAdmin()) {
            abort(403, 'You do not have permission to access newsletter management. Only super administrators can manage newsletters.');
        }
    }

    /**
     * Display a listing of newsletter subscribers.
     */
    public function index(Request $request)
    {
        $this->ensureSuperAdmin();
        $query = NewsletterSubscriber::query()->latest('subscribed_at');

        // Filter by status
        if ($request->filled('status')) {
            $status = $request->input('status');
            if (in_array($status, ['subscribed', 'unsubscribed'])) {
                $query->where('status', $status);
            }
        }

        // Search by email or name
        if ($request->filled('q')) {
            $search = $request->input('q');
            $query->where(function ($q) use ($search) {
                $q->where('email', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%");
            });
        }

        $subscribers = $query->paginate(25)->withQueryString();

        return view('admin.newsletter.index', compact('subscribers'));
    }

    /**
     * Show the form for sending a newsletter.
     */
    public function create()
    {
        $this->ensureSuperAdmin();
        $subscriberCount = NewsletterSubscriber::subscribed()->count();
        return view('admin.newsletter.create', compact('subscriberCount'));
    }

    /**
     * Send newsletter to all subscribed users.
     */
    public function send(Request $request)
    {
        $this->ensureSuperAdmin();
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'content' => 'required|string',
            'send_test' => 'nullable|boolean',
            'test_email' => 'required_if:send_test,1|email|max:255',
        ]);

        $subscribers = NewsletterSubscriber::subscribed()->get();

        if ($subscribers->isEmpty()) {
            return back()->with('error', __('No active subscribers found.'));
        }

        // Send test email if requested
        if ($request->boolean('send_test')) {
            try {
                Mail::to($validated['test_email'])->send(
                    new NewsletterEmail($validated['subject'], $validated['content'], $validated['test_email'])
                );

                return back()->with('success', __('Test email sent successfully to :email.', ['email' => $validated['test_email']]));
            } catch (\Exception $e) {
                return back()->with('error', __('Failed to send test email: :error', ['error' => $e->getMessage()]));
            }
        }

        // Send to all subscribers
        $sent = 0;
        $failed = 0;

        foreach ($subscribers as $subscriber) {
            try {
                Mail::to($subscriber->email)->send(
                    new NewsletterEmail($validated['subject'], $validated['content'], $subscriber->email)
                );
                $sent++;
            } catch (\Exception $e) {
                $failed++;
                \Log::warning('Newsletter send failed', [
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $message = __('Newsletter sent successfully. :sent sent, :failed failed.', [
            'sent' => $sent,
            'failed' => $failed,
        ]);

        return redirect()->route('admin.newsletter.index')->with('success', $message);
    }

    /**
     * Remove the specified subscriber.
     */
    public function destroy(NewsletterSubscriber $subscriber)
    {
        $this->ensureSuperAdmin();
        $subscriber->delete();

        return redirect()->route('admin.newsletter.index')
            ->with('success', __('Subscriber deleted successfully.'));
    }

    /**
     * Unsubscribe a subscriber (admin action).
     */
    public function unsubscribe(NewsletterSubscriber $subscriber)
    {
        $this->ensureSuperAdmin();
        $subscriber->unsubscribe();

        return redirect()->route('admin.newsletter.index')
            ->with('success', __('Subscriber unsubscribed successfully.'));
    }

    /**
     * Re-subscribe a subscriber (admin action).
     */
    public function resubscribe(NewsletterSubscriber $subscriber)
    {
        $this->ensureSuperAdmin();
        $subscriber->subscribe();

        return redirect()->route('admin.newsletter.index')
            ->with('success', __('Subscriber re-subscribed successfully.'));
    }
}

