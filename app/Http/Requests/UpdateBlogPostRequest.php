<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class UpdateBlogPostRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // BlogPostPolicy'deki 'update' yetkisine göre kontrol edilebilir
        // $blogPost = $this->route('blog_post'); // Rota model binding ile gelen post
        // return $this->user()->can('update', $blogPost);
        return true; // Şimdilik true bırakıldı, policy entegrasyonu yapılmalı
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $blogPostId = $this->route('blog_post') ? $this->route('blog_post')->id : null;

        return [
            'title' => [
                'required',
                'string',
                'max:255',
                Rule::unique('blog_posts', 'title')->ignore($blogPostId),
            ],
            'content' => 'required|string',
            'meta_description' => 'nullable|string|max:160',
            'tags' => 'nullable|array',
            'tags.*' => 'nullable|string|max:50',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'formatting' => 'nullable|json',
            'gallery_images' => 'nullable|array',
            'gallery_images.*' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'gallery_captions' => 'nullable|array',
            'gallery_captions.*' => 'nullable|string|max:255',
            'gallery_alts' => 'nullable|array',
            'gallery_alts.*' => 'nullable|string|max:255',
            'remove_gallery_images' => 'nullable|array', // Silinecek galeri görsellerinin ID'leri
            'remove_gallery_images.*' => 'nullable|integer|exists:blog_post_galleries,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'nullable|exists:categories,id',
            'scheduled_at' => 'nullable|date_format:Y-m-d H:i:s|after_or_equal:now',
            'status' => 'required|in:draft,published',
        ];
    }

    /**
     * Validasyondan sonraki veriyi hazırlar.
     */
    protected function prepareForValidation()
    {
        if ($this->title && $this->route('blog_post') && $this->title !== $this->route('blog_post')->title) {
             $this->merge([
                'slug' => Str::slug($this->title),
            ]);
        }

        if ($this->formatting && is_string($this->formatting)) {
            $decodedFormatting = json_decode($this->formatting, true);
            $this->merge([
                'formatting' => $decodedFormatting === null && json_last_error() !== JSON_ERROR_NONE ? $this->route('blog_post')->formatting : $decodedFormatting,
            ]);
        } elseif (!$this->has('formatting') && $this->route('blog_post')) { // Eğer formatting hiç gelmediyse ve post varsa eskisini koru
             $this->merge(['formatting' => $this->route('blog_post')->formatting]);
        } elseif (!$this->formatting) { // Eğer formatting boş geldiyse varsayılanı ata
            $this->merge(['formatting' => json_decode('{"font":"Inter","fontSize":"16px","lineHeight":"1.5","textAlign":"left","color":"#333333"}', true)]);
        }


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
            // ... diğer mesajlar StoreBlogPostRequest ile benzer olabilir
        ];
    }
}
