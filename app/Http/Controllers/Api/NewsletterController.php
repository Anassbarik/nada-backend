<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\NewsletterSignupNotification;
use App\Models\NewsletterSubscriber;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class NewsletterController extends Controller
{
    /**
     * Subscribe to newsletter (public API endpoint).
     * Route: POST /api/newsletter/subscribe
     */
    public function subscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
            'name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));
        $name = $request->input('name');

        // Check if already subscribed
        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if ($subscriber) {
            if ($subscriber->status === 'subscribed') {
                return response()->json([
                    'success' => false,
                    'message' => 'This email is already subscribed to our newsletter.',
                ], 409);
            }

            // Re-subscribe if previously unsubscribed
            $subscriber->subscribe();
            $subscriber->update(['source' => 'api']);

            // Send notification email to booking@seminairexpo.com
            try {
                Mail::to('booking@seminairexpo.com')->send(
                    new NewsletterSignupNotification($subscriber)
                );
            } catch (\Exception $e) {
                // Log error but don't fail the subscription
                \Log::warning('Newsletter signup notification failed', [
                    'email' => $subscriber->email,
                    'error' => $e->getMessage(),
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Successfully re-subscribed to newsletter.',
            ]);
        }

        // Create new subscriber
        $subscriber = NewsletterSubscriber::create([
            'email' => $email,
            'name' => $name,
            'status' => 'subscribed',
            'source' => 'api',
            'subscribed_at' => now(),
        ]);

        // Send notification email to booking@seminairexpo.com
        try {
            Mail::to('booking@seminairexpo.com')->send(
                new NewsletterSignupNotification($subscriber)
            );
        } catch (\Exception $e) {
            // Log error but don't fail the subscription
            \Log::warning('Newsletter signup notification failed', [
                'email' => $subscriber->email,
                'error' => $e->getMessage(),
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Successfully subscribed to newsletter.',
        ], 201);
    }

    /**
     * Unsubscribe from newsletter (public API endpoint).
     * Route: POST /api/newsletter/unsubscribe
     */
    public function unsubscribe(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation failed.',
                'errors' => $validator->errors(),
            ], 422);
        }

        $email = strtolower(trim($request->input('email')));

        $subscriber = NewsletterSubscriber::where('email', $email)->first();

        if (!$subscriber) {
            return response()->json([
                'success' => false,
                'message' => 'Email not found in our newsletter list.',
            ], 404);
        }

        if ($subscriber->status === 'unsubscribed') {
            return response()->json([
                'success' => false,
                'message' => 'This email is already unsubscribed.',
            ], 409);
        }

        $subscriber->unsubscribe();

        return response()->json([
            'success' => true,
            'message' => 'Successfully unsubscribed from newsletter.',
        ]);
    }

    /**
     * Unsubscribe from newsletter via GET (for email links).
     * Route: GET /api/newsletter/unsubscribe?email=...
     */
    public function unsubscribeGet(Request $request)
    {
        $email = $request->query('email');

        if (!$email) {
            return response()->view('emails.unsubscribe-failed', [
                'message' => 'Email parameter is required.',
            ], 400);
        }

        $subscriber = NewsletterSubscriber::where('email', strtolower(trim($email)))->first();

        if (!$subscriber) {
            return response()->view('emails.unsubscribe-failed', [
                'message' => 'Email not found in our newsletter list.',
            ], 404);
        }

        if ($subscriber->status === 'unsubscribed') {
            return response()->view('emails.unsubscribe-success', [
                'message' => 'This email is already unsubscribed.',
                'alreadyUnsubscribed' => true,
            ]);
        }

        $subscriber->unsubscribe();

        return response()->view('emails.unsubscribe-success', [
            'message' => 'Successfully unsubscribed from newsletter.',
            'alreadyUnsubscribed' => false,
        ]);
    }
}

