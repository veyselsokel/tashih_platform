<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;

class StoreBlogPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // BlogPostPolicy'deki 'create' yetkisine göre kontrol edilebilir
        // return $this->user()->can('create', \App\Models\BlogPost::class);
        return true; // Şimdilik true bırakıldı, policy entegrasyonu yapılmalı
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:blog_posts,title',
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:50', // Her bir etiket için
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Max 2MB
            'formatting' => 'nullable|json',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048', // Her bir galeri görseli için
            'gallery_captions' => 'nullable|array',
            'gallery_captions.*' => 'nullable|string|max:255',
            'gallery_alts' => 'nullable|array',
            'gallery_alts.*' => 'nullable|string|max:255',
            'category_ids' => 'nullable|array', // Kategori ID'leri
            'category_ids.*' => 'nullable|exists:categories,id', // Her bir kategori ID'si categories tablosunda var olmalı
            'scheduled_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now', // Geçmiş bir tarih olamaz
            'status' => 'required|in:draft,published', // Durum: taslak veya yayınlanmış
        ];
    }

    /**
     * Validasyondan sonraki veriyi hazırlar.
     */
    protected function prepareForValidation()
    {
        if ($this->title && !$this->slug) {
            $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }
        // Gelen 'formatting' string ise JSON'a çevir (veya null bırak)
        if ($this->formatting && is_string($this->formatting)) {
            $decodedFormatting = json_decode($this->formatting, true);
            $this->merge([
                'formatting' => $decodedFormatting === null && json_last_error() !== JSON_ERROR_NONE ? null : $decodedFormatting,
            ]);
        } elseif (!$this->formatting) {
             $this->merge(['formatting' => json_decode('{"font":"Inter","fontSize":"16px","lineHeight":"1.5","textAlign":"left","color":"#333333"}', true)]);
        }

        // Gelen 'tags' string ise array'e çevir
        if ($this->tags && is_string($this->tags)) {
            $this->merge([
                'tags' => array_map('trim', explode(',', $this->tags)),
            ]);
        }
    }

    /**
     * Özelleştirilmiş validasyon mesajları.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Başlık alanı zorunludur.',
            'title.unique' => 'Bu başlık daha önce kullanılmış.',
            'content.required' => 'İçerik alanı zorunludur.',
            'meta_description.max' => 'Meta açıklama en fazla 160 karakter olabilir.',
            'featured_image.image' => 'Öne çıkan görsel bir resim dosyası olmalıdır.',
            'featured_image.mimes' => 'Öne çıkan görsel sadece jpeg, png, jpg, gif, webp formatlarında olabilir.',
            'featured_image.max' => 'Öne çıkan görsel en fazla 2MB olabilir.',
            'category_ids.*.exists' => 'Seçilen kategori geçersiz.',
            'scheduled_at.date_format' => 'Zamanlama tarihi Y-m-d H:i:s formatında olmalıdır.',
            'scheduled_at.after_or_equal' => 'Zamanlama tarihi geçmiş bir tarih olamaz.',
            'status.required' => 'Yayın durumu zorunludur.',
            'status.in' => 'Geçersiz yayın durumu seçildi.',
        ];
    }
}
