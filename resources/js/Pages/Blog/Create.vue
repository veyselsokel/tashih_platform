<script setup>
import { ref } from 'vue';
import { useForm } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';

const maxWidth = 1920; // maksimum genişlik
const maxHeight = 1080; // maksimum yükseklik
const quality = 0.7; // sıkıştırma kalitesi (0-1 arası)
const maxFileSize = 2 * 1024 * 1024; // 2MB

const compressImage = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;

            img.onload = () => {
                // Boyut oranını hesapla
                let width = img.width;
                let height = img.height;

                if (width > maxWidth) {
                    height *= maxWidth / width;
                    width = maxWidth;
                }

                if (height > maxHeight) {
                    width *= maxHeight / height;
                    height = maxHeight;
                }

                // Canvas oluştur
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                // Resmi canvas'a çiz
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Sıkıştırılmış base64'ü al
                canvas.toBlob((blob) => {
                    if (blob) {
                        // Eğer hala çok büyükse tekrar sıkıştır
                        if (blob.size > maxFileSize) {
                            const newQuality = (maxFileSize / blob.size) * quality;
                            canvas.toBlob(
                                (finalBlob) => {
                                    resolve(finalBlob);
                                },
                                'image/jpeg',
                                Math.min(newQuality, quality) // En düşük kaliteyi kullan
                            );
                        } else {
                            resolve(blob);
                        }
                    } else {
                        reject(new Error('Resim sıkıştırılamadı'));
                    }
                }, 'image/jpeg', quality);
            };

            img.onerror = (error) => {
                reject(error);
            };
        };

        reader.onerror = (error) => {
            reject(error);
        };
    });
};

const form = useForm({
    title: '',
    content: '',
    formatting: {
        font: 'Arial',
        fontSize: '16px',
        textAlign: 'left',
        color: '#000000',
        lineHeight: '1.5'
    },
    featured_image: null,
    meta_description: '',
    tags: [],
    is_published: false
});

const imagePreview = ref(null);

const handleImageUpload = async (e) => {
    const file = e.target.files[0];

    if (file) {
        try {
            const compressedBlob = await compressImage(file);
            // Blob'u File nesnesine çevir
            const compressedFile = new File([compressedBlob], file.name, {
                type: 'image/jpeg',
                lastModified: new Date().getTime()
            });

            // Form'a compressed dosyayı ata
            form.featured_image = compressedFile;

            // Önizleme için URL oluştur
            imagePreview.value = URL.createObjectURL(compressedBlob);
        } catch (error) {
            console.error('Resim sıkıştırma hatası:', error);
            // Kullanıcıya hata mesajı göster
        }
    }
};

const fontOptions = [
    { value: 'Arial', label: 'Arial' },
    { value: 'Times New Roman', label: 'Times New Roman' },
    { value: 'Helvetica', label: 'Helvetica' },
    { value: 'Georgia', label: 'Georgia' }
];

const fontSizeOptions = [
    { value: '12px', label: '12px' },
    { value: '14px', label: '14px' },
    { value: '16px', label: '16px' },
    { value: '18px', label: '18px' },
    { value: '20px', label: '20px' }
];

const submit = () => {
    form.post(route('blog.store'), {
        preserveScroll: true
    });
};

// İçeriği paragraflar halinde formatlayan fonksiyon
const formatContent = (content) => {
    if (!content) return '';
    return content.split('\n\n')
        .filter(paragraph => paragraph.trim() !== '')
        .map(paragraph => `<p>${paragraph.replace(/\n/g, '<br>')}</p>`)
        .join('');
};
</script>

<template>
    <AuthenticatedLayout>
        <template #header>
            <h2 class="text-xl font-semibold leading-tight text-gray-800">
                Yeni Blog Yazısı
            </h2>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                <div class="overflow-hidden bg-white shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <form @submit.prevent="submit">
                            <!-- Title -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Başlık
                                </label>
                                <input v-model="form.title" type="text"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    required />
                            </div>

                            <!-- Formatting Tools -->
                            <div class="mb-6 flex flex-wrap gap-4 rounded-lg bg-gray-50 p-4">
                                <!-- Font Family -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Font</label>
                                    <select v-model="form.formatting.font"
                                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option v-for="font in fontOptions" :key="font.value" :value="font.value">
                                            {{ font.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Font Size -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Boyut</label>
                                    <select v-model="form.formatting.fontSize"
                                        class="mt-1 rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500">
                                        <option v-for="size in fontSizeOptions" :key="size.value" :value="size.value">
                                            {{ size.label }}
                                        </option>
                                    </select>
                                </div>

                                <!-- Text Align -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Hizalama</label>
                                    <div class="mt-1 flex space-x-2">
                                        <button type="button" @click="form.formatting.textAlign = 'left'" :class="[
                                            'p-2 rounded-md',
                                            form.formatting.textAlign === 'left'
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-gray-200'
                                        ]">
                                            <span class="sr-only">Sola Hizala</span>
                                            <i class="fas fa-align-left"></i>
                                        </button>
                                        <button type="button" @click="form.formatting.textAlign = 'center'" :class="[
                                            'p-2 rounded-md',
                                            form.formatting.textAlign === 'center'
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-gray-200'
                                        ]">
                                            <span class="sr-only">Ortala</span>
                                            <i class="fas fa-align-center"></i>
                                        </button>
                                        <button type="button" @click="form.formatting.textAlign = 'right'" :class="[
                                            'p-2 rounded-md',
                                            form.formatting.textAlign === 'right'
                                                ? 'bg-orange-500 text-white'
                                                : 'bg-gray-200'
                                        ]">
                                            <span class="sr-only">Sağa Hizala</span>
                                            <i class="fas fa-align-right"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Text Color -->
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Renk</label>
                                    <input type="color" v-model="form.formatting.color"
                                        class="mt-1 h-9 w-16 rounded-md border-gray-300" />
                                </div>
                            </div>

                            <!-- Content Editor -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    İçerik
                                </label>
                                <textarea v-model="form.content" rows="10"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500 whitespace-pre-wrap"
                                    :style="{
                                        fontFamily: form.formatting.font,
                                        fontSize: form.formatting.fontSize,
                                        textAlign: form.formatting.textAlign,
                                        color: form.formatting.color,
                                        lineHeight: form.formatting.lineHeight
                                    }" required></textarea>
                            </div>

                            <!-- Featured Image -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Öne Çıkan Görsel
                                </label>
                                <input type="file" @change="handleImageUpload" accept="image/*"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                                <img v-if="imagePreview" :src="imagePreview" alt="Preview"
                                    class="mt-4 h-32 w-32 rounded-lg object-cover" />
                            </div>

                            <!-- Meta Description -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Meta Açıklama
                                </label>
                                <textarea v-model="form.meta_description" rows="2"
                                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500"
                                    placeholder="SEO için meta açıklama"></textarea>
                            </div>

                            <!-- Tags -->
                            <div class="mb-6">
                                <label class="mb-2 block text-sm font-medium text-gray-700">
                                    Etiketler
                                </label>
                                <div class="flex flex-wrap gap-2">
                                    <div v-for="(tag, index) in form.tags" :key="index"
                                        class="flex items-center rounded-full bg-orange-100 px-3 py-1">
                                        <span class="text-sm text-orange-800">{{ tag }}</span>
                                        <button type="button" @click="form.tags.splice(index, 1)"
                                            class="ml-2 text-orange-600 hover:text-orange-800">
                                            ×
                                        </button>
                                    </div>
                                    <input type="text" @keydown.enter.prevent="
                                        $event.target.value && form.tags.push($event.target.value);
                                    $event.target.value = ''
                                        " placeholder="Enter ile etiket ekleyin"
                                        class="rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                                </div>
                            </div>

                            <!-- Publishing Options -->
                            <div class="mb-6">
                                <div class="flex items-center">
                                    <input type="checkbox" v-model="form.is_published" id="is_published"
                                        class="h-4 w-4 rounded border-gray-300 text-orange-500 focus:ring-orange-500" />
                                    <label for="is_published" class="ml-2 block text-sm text-gray-700">
                                        Yayınla
                                    </label>
                                </div>
                                <p class="mt-1 text-sm text-gray-500">
                                    İşaretlemezseniz taslak olarak kaydedilecektir.
                                </p>
                            </div>

                            <!-- Preview -->
                            <div class="mb-6">
                                <h3 class="mb-4 text-lg font-medium text-gray-900">Önizleme</h3>
                                <div class="rounded-lg border border-gray-200 p-4">
                                    <h1 class="mb-4 text-2xl font-bold" :style="{
                                        fontFamily: form.formatting.font,
                                        color: form.formatting.color
                                    }">
                                        {{ form.title || 'Başlık' }}
                                    </h1>
                                    <div class="prose max-w-none" :style="{
                                        fontFamily: form.formatting.font,
                                        fontSize: form.formatting.fontSize,
                                        textAlign: form.formatting.textAlign,
                                        color: form.formatting.color,
                                        lineHeight: form.formatting.lineHeight
                                    }">
                                        <div v-html="formatContent(form.content || 'İçerik')"></div>
                                    </div>
                                </div>
                            </div>

                            <!-- Submit Button -->
                            <div class="flex items-center justify-end gap-4">
                                <button type="button"
                                    class="rounded-md bg-white px-4 py-2 text-sm font-medium text-gray-700 shadow-sm hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2"
                                    @click="$inertia.visit(route('dashboard'))">
                                    İptal
                                </button>
                                <button type="submit" :disabled="form.processing"
                                    class="inline-flex items-center rounded-md border border-transparent bg-orange-500 px-4 py-2 text-sm font-medium text-white shadow-sm hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2">
                                    <span v-if="form.processing">Kaydediliyor...</span>
                                    <span v-else>Kaydet</span>
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>

<style>
.prose {
    max-width: 65ch;
    margin: 0 auto;
}

.prose p {
    margin-bottom: 1.5em;
    white-space: pre-wrap;
}

textarea {
    white-space: pre-wrap;
}
</style>