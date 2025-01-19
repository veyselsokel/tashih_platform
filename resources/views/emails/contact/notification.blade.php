@component('mail::message')
# Yeni İletişim Formu Mesajı

**Gönderen:** {{ $contact->name }}  
**E-posta:** {{ $contact->email }}

**Mesaj:**  
{{ $contact->message }}

Tarih: {{ $contact->created_at->format('d.m.Y H:i') }}

@endcomponent