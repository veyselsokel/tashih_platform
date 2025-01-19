<?php

namespace App\Http\Controllers;

use App\Models\Contact;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Illuminate\Support\Facades\Mail;

class ContactController extends Controller
{
    /**
     * Display the contact form page.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        return Inertia::render('ContactPage', [
            'maxMessageLength' => config('contact.max_message_length', 1000),
            'recaptchaKey' => config('services.recaptcha.site_key'), // If you want to add reCAPTCHA
        ]);
    }

    /**
     * Store a new contact message.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'message' => ['required', 'string', 'min:10', 'max:' . config('contact.max_message_length', 1000)],
            ], [
                // ... validation messages ...
            ]);
    
            $contact = Contact::create($validated);
    
            // Mail gönderimi
            $this->sendAdminNotification($contact);
    
            return redirect()->back()->with([
                'success' => 'Mesajınız başarıyla gönderildi!',
                'contact_id' => $contact->id,
            ]);
    
        } catch (\Exception $e) {
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'token']),
            ]);
    
            return redirect()->back()
                ->with('error', 'Mesajınız gönderilemedi. Lütfen daha sonra tekrar deneyiniz.')
                ->withInput();
        }
    }
    
    private function sendAdminNotification(Contact $contact)
    {
        try {
            Mail::to(config('mail.admin_address'))
                ->send(new \App\Mail\NewContactNotification($contact));
                
            Log::info('Admin notification email sent successfully', [
                'contact_id' => $contact->id
            ]);
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification email', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
            
            throw $e; // Hatayı yukarı fırlat
        }
    }

    /**
     * Send notification email to admin
     * 
     * @param Contact $contact
     * @return void
     */
}