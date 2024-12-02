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
            // Validate the request
            $validated = $request->validate([
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'message' => ['required', 'string', 'min:10', 'max:' . config('contact.max_message_length', 1000)],
                // 'g-recaptcha-response' => ['required', 'recaptcha'], // If you want to add reCAPTCHA
            ], [
                'name.required' => 'Lütfen adınızı giriniz.',
                'name.max' => 'Ad alanı en fazla 255 karakter olabilir.',
                'email.required' => 'Lütfen e-posta adresinizi giriniz.',
                'email.email' => 'Geçerli bir e-posta adresi giriniz.',
                'email.max' => 'E-posta adresi en fazla 255 karakter olabilir.',
                'message.required' => 'Lütfen mesajınızı giriniz.',
                'message.min' => 'Mesajınız en az 10 karakter olmalıdır.',
                'message.max' => 'Mesajınız en fazla :max karakter olabilir.',
            ]);

            // Create the contact message
            $contact = Contact::create($validated);

            // Log the successful submission
            Log::info('New contact form submission', [
                'contact_id' => $contact->id,
                'email' => $contact->email,
            ]);

            // Optional: Send notification email to admin
            // $this->sendAdminNotification($contact);

            // Return successful response
            return redirect()->back()->with([
                'success' => 'Mesajınız başarıyla gönderildi!',
                'contact_id' => $contact->id,
            ]);

        } catch (ValidationException $e) {
            throw $e;
        } catch (\Exception $e) {
            // Log the error
            Log::error('Contact form submission failed', [
                'error' => $e->getMessage(),
                'data' => $request->except(['password', 'token']),
            ]);

            return redirect()->back()
                ->with('error', 'Mesajınız gönderilemedi. Lütfen daha sonra tekrar deneyiniz.')
                ->withInput();
        }
    }

    /**
     * Send notification email to admin
     * 
     * @param Contact $contact
     * @return void
     */
    private function sendAdminNotification(Contact $contact)
    {
        try {
            Mail::to(config('mail.admin_address'))->queue(new \App\Mail\NewContactNotification($contact));
        } catch (\Exception $e) {
            Log::error('Failed to send admin notification email for contact form submission', [
                'contact_id' => $contact->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}