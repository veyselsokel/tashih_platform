<script setup>
import { ref } from 'vue';
import { compressImage } from '@/utils/imageProcessing';

const props = defineProps({
    featuredImage: {
        type: [File, null],
        default: null
    },
    gallery: {
        type: Array,
        default: () => []
    },
    preview: {
        type: String,
        default: null
    }
});

const emit = defineEmits(['update:featuredImage', 'update:gallery']);

const imageGallery = ref(props.gallery);

const handleFeaturedImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) return;

    try {
        const compressedBlob = await compressImage(file);
        const compressedFile = new File([compressedBlob], file.name, {
            type: 'image/jpeg',
            lastModified: new Date().getTime()
        });

        emit('update:featuredImage', compressedFile);
    } catch (error) {
        console.error('Resim sıkıştırma hatası:', error);
    }
};

const handleGalleryUpload = async (e) => {
    const files = Array.from(e.target.files);

    for (const file of files) {
        try {
            const compressedBlob = await compressImage(file);
            const compressedFile = new File([compressedBlob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            const imageUrl = URL.createObjectURL(compressedFile);

            imageGallery.value.push({
                file: compressedFile,
                preview: imageUrl,
                caption: '',
                altText: ''
            });

            emit('update:gallery', imageGallery.value);
        } catch (error) {
            console.error('Görsel işleme hatası:', error);
        }
    }
};

const removeGalleryImage = (index) => {
    imageGallery.value.splice(index, 1);
    emit('update:gallery', imageGallery.value);
};

const updateImageMeta = (index, field, value) => {
    imageGallery.value[index][field] = value;
    emit('update:gallery', imageGallery.value);
};
</script>

<template>
    <div class="space-y-6">
        <!-- Öne Çıkan Görsel -->
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Öne Çıkan Görsel
            </label>
            <div class="space-y-2">
                <input type="file" @change="handleFeaturedImageUpload" accept="image/*"
                    class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                <img v-if="preview" :src="preview" alt="Öne çıkan görsel önizleme"
                    class="h-32 w-32 rounded-lg object-cover" />
            </div>
        </div>

        <!-- Galeri -->
        <div>
            <label class="mb-2 block text-sm font-medium text-gray-700">
                Galeri Görselleri
            </label>
            <input type="file" @change="handleGalleryUpload" accept="image/*" multiple
                class="w-full rounded-md border-gray-300 shadow-sm focus:border-orange-500 focus:ring-orange-500" />

            <!-- Galeri Önizleme -->
            <div class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                <div v-for="(image, index) in imageGallery" :key="index" class="relative rounded-lg border p-2">
                    <img :src="image.preview" :alt="image.altText" class="h-32 w-full rounded-lg object-cover" />

                    <div class="mt-2 space-y-2">
                        <input v-model="image.caption" type="text" placeholder="Resim başlığı"
                            class="w-full text-sm rounded-md border-gray-300"
                            @input="updateImageMeta(index, 'caption', $event.target.value)" />
                        <input v-model="image.altText" type="text" placeholder="Alt metin"
                            class="w-full text-sm rounded-md border-gray-300"
                            @input="updateImageMeta(index, 'altText', $event.target.value)" />
                    </div>

                    <button type="button" @click="removeGalleryImage(index)"
                        class="absolute -top-2 -right-2 rounded-full bg-red-500 p-1 text-white hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500">
                        <span class="sr-only">Görseli kaldır</span>
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" viewBox="0 0 20 20" fill="currentColor">
                            <path fill-rule="evenodd"
                                d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <!-- Yardımcı Metin -->
        <p class="text-sm text-gray-500">
            * Görseller otomatik olarak optimize edilecektir. Maksimum dosya boyutu: 2MB
        </p>
    </div>
</template>

<style scoped>
.image-uploader {
    transition: all 0.3s ease;
}

.gallery-image {
    transition: transform 0.2s ease;
}

.gallery-image:hover {
    transform: scale(1.02);
}

/* Sürükle-bırak alanı stilleri */
.drop-zone {
    @apply border-2 border-dashed border-gray-300 rounded-lg p-4 text-center transition-colors;
}

.drop-zone.active {
    @apply border-orange-500 bg-orange-50;
}

/* Dosya seçici gizleme */
input[type="file"] {
    @apply cursor-pointer;
}

/* Görsel kaldırma butonu hover efekti */
.remove-button {
    @apply opacity-0 transition-opacity;
}

.gallery-image:hover .remove-button {
    @apply opacity-100;
}
</style>