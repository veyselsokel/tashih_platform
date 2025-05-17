<script setup>
import { ref, onMounted, watch } from 'vue'; // watch eklendi
import { compressImage } from '@/utils/imageProcessing';

// defineProps'un döndürdüğü değer 'props' sabitine atanmalı
const props = defineProps({
    // v-model:featuredImage için
    featuredImage: {
        type: [File, String, null], // String, initial URL için olabilir
        default: null
    },
    // v-model:gallery için
    gallery: {
        type: Array,
        default: () => []
    },
    // Create.vue'den gelen featured_image için önizleme URL'si (File nesnesi seçildiğinde güncellenir)
    // Edit.vue'den gelen initialFeaturedImageUrl için de kullanılabilir.
    preview: { // Bu prop featuredImage için bir önizleme URL'si ise ismi daha açıklayıcı olabilir.
        type: String,
        default: null
    },
    // Edit sayfasından gelen mevcut öne çıkan görselin URL'si
    initialFeaturedImageUrl: {
        type: String,
        default: null
    },
    // Edit sayfasından gelen mevcut galeri görselleri
    initialGallery: {
        type: Array,
        default: () => []
    }
});

const emit = defineEmits(['update:featuredImage', 'update:gallery', 'remove-featured-image']);

const internalFeaturedImagePreview = ref(null);
const imageGallery = ref([]); // Bu, hem yeni yüklenenleri hem de mevcutları tutacak

// Öne çıkan görsel için önizlemeyi ayarla
onMounted(() => {
    if (props.initialFeaturedImageUrl) {
        internalFeaturedImagePreview.value = props.initialFeaturedImageUrl;
    }
    // initialGallery'yi imageGallery'ye kopyala
    // Bu, Create.vue'den gelen `form.gallery` ile senkronize olmalı.
    // `v-model:gallery` kullanıldığı için, `props.gallery` zaten en güncel halini yansıtmalı.
    // Ancak, `initialGallery` prop'u Edit modu için daha mantıklı.
    // Create modunda `props.gallery` boş bir dizi ile başlar ve kullanıcı ekledikçe güncellenir.
    // Edit modunda `props.gallery` (Create.vue'deki `form.gallery`den gelen) `initialGallery` ile doldurulur.

    // imageGallery'yi props.gallery ile senkronize et
    // Bu, ImageUploader'ın kendi içinde tuttuğu ve manipüle ettiği bir kopya olacak.
    // Değişiklikler 'update:gallery' ile dışarıya emit edilecek.
    imageGallery.value = JSON.parse(JSON.stringify(props.gallery)); // Derin kopya
});

// Props.gallery değiştiğinde imageGallery'yi güncelle (dışarıdan gelen değişiklikler için)
watch(() => props.gallery, (newGallery) => {
    imageGallery.value = JSON.parse(JSON.stringify(newGallery));
}, { deep: true });


const handleFeaturedImageUpload = async (e) => {
    const file = e.target.files[0];
    if (!file) {
        emit('update:featuredImage', null);
        internalFeaturedImagePreview.value = null;
        emit('remove-featured-image'); // Eğer dosya seçimi iptal edilirse veya boşsa, kaldırma event'ini tetikle
        return;
    }

    try {
        const compressedBlob = await compressImage(file);
        const compressedFile = new File([compressedBlob], file.name, {
            type: 'image/jpeg', // Sıkıştırma sonrası tip
            lastModified: Date.now()
        });

        emit('update:featuredImage', compressedFile); // File nesnesini emit et
        internalFeaturedImagePreview.value = URL.createObjectURL(compressedFile); // Yeni önizleme
    } catch (error) {
        console.error('Öne çıkan görsel işleme hatası:', error);
        emit('update:featuredImage', null); // Hata durumunda null emit et
        internalFeaturedImagePreview.value = null;
    }
};

const removeFeaturedImage = () => {
    emit('update:featuredImage', null);
    internalFeaturedImagePreview.value = null;
    // Create.vue'deki form.remove_featured_image'ı tetiklemek için event
    emit('remove-featured-image');
    // Dosya inputunu sıfırla ki aynı dosya tekrar seçilebilsin
    const fileInput = document.querySelector('input[type="file"][aria-label="featured-image-input"]');
    if (fileInput) {
        fileInput.value = '';
    }
};


const handleGalleryUpload = async (e) => {
    const files = Array.from(e.target.files);
    if (!files.length) return;

    const newImages = [];
    for (const file of files) {
        try {
            const compressedBlob = await compressImage(file);
            const compressedFile = new File([compressedBlob], file.name, {
                type: 'image/jpeg',
                lastModified: Date.now()
            });

            newImages.push({
                file: compressedFile, // Backend'e gönderilecek dosya
                preview: URL.createObjectURL(compressedFile), // UI'da gösterilecek önizleme
                caption: '',
                alt_text: '',
                // id: null, // Yeni eklendiği için ID'si yok
            });
        } catch (error) {
            console.error(`Galeri görseli ${file.name} işlenirken hata:`, error);
        }
    }

    // Mevcut imageGallery'ye yeni görselleri ekle
    const updatedGallery = [...imageGallery.value, ...newImages];
    imageGallery.value = updatedGallery; // Internal state'i güncelle
    emit('update:gallery', updatedGallery); // Değişikliği parent'a bildir

    // Dosya inputunu sıfırla
    e.target.value = '';
};

const removeGalleryImage = (indexToRemove) => {
    const imageToRemove = imageGallery.value[indexToRemove];

    if (imageToRemove.file && imageToRemove.preview.startsWith('blob:')) {
        URL.revokeObjectURL(imageToRemove.preview); // Blob URL'yi serbest bırak
    }

    // Eğer görselin bir ID'si varsa (yani backend'den gelmişse),
    // onu silmek yerine 'markedForRemoval' olarak işaretleyebiliriz.
    // Ya da direkt listeden çıkarıp, parent component'in (Edit.vue)
    // backend'e 'remove_gallery_images' ID listesini göndermesini sağlayabiliriz.
    // Bu implementasyon direkt listeden çıkarıyor. Parent component'in (Edit.vue)
    // bu değişikliği `form.transform` içinde işlemesi gerekir.
    const updatedGallery = imageGallery.value.filter((_, index) => index !== indexToRemove);
    imageGallery.value = updatedGallery;
    emit('update:gallery', updatedGallery);
};


// Galerideki bir görselin meta verisini güncellemek için
const updateGalleryItemMeta = (index, field, value) => {
    if (imageGallery.value[index]) {
        const updatedGallery = JSON.parse(JSON.stringify(imageGallery.value)); // Derin kopya
        updatedGallery[index][field] = value;
        imageGallery.value = updatedGallery; // Internal state'i güncelle
        emit('update:gallery', updatedGallery); // Değişikliği parent'a bildir
    }
};

</script>

<template>
    <div class="space-y-6 rounded-md border border-gray-200 p-4">
        <h3 class="text-lg font-medium leading-6 text-gray-900">Görseller</h3>
        <div>
            <label for="featured-image-input" class="mb-1 block text-sm font-medium text-gray-700">
                Öne Çıkan Görsel
            </label>
            <div class="mt-1">
                <input id="featured-image-input" aria-label="featured-image-input" type="file"
                    @change="handleFeaturedImageUpload" accept="image/*"
                    class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-orange-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-700 hover:file:bg-orange-100" />
            </div>
            <div v-if="internalFeaturedImagePreview" class="mt-2 relative inline-block">
                <img :src="internalFeaturedImagePreview" alt="Öne çıkan görsel önizleme"
                    class="h-32 w-auto rounded-lg object-cover shadow" />
                <button @click="removeFeaturedImage" type="button" title="Öne çıkan görseli kaldır"
                    class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                        stroke="currentColor" class="h-4 w-4">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
            <p v-else-if="!featuredImage && initialFeaturedImageUrl" class="mt-2 text-sm text-gray-500">
                Mevcut öne çıkan görsel kullanılacak. Değiştirmek için yeni bir görsel seçin.
            </p>
        </div>

        <div class="mt-4">
            <label for="gallery-image-input" class="mb-1 block text-sm font-medium text-gray-700">
                Galeri Görselleri
            </label>
            <div class="mt-1">
                <input id="gallery-image-input" type="file" @change="handleGalleryUpload" accept="image/*" multiple
                    class="block w-full text-sm text-slate-500 file:mr-4 file:rounded-full file:border-0 file:bg-orange-50 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-700 hover:file:bg-orange-100" />
            </div>

            <div v-if="imageGallery.length > 0" class="mt-4 grid grid-cols-2 gap-4 sm:grid-cols-3 md:grid-cols-4">
                <div v-for="(image, index) in imageGallery" :key="image.id || image.preview"
                    class="relative rounded-lg border p-2 shadow-sm">
                    <img :src="image.preview || image.image_url" :alt="image.alt_text || 'Galeri görseli'"
                        class="aspect-square h-auto w-full rounded-md object-cover" />

                    <div class="mt-2 space-y-1">
                        <input :value="image.caption"
                            @input="updateGalleryItemMeta(index, 'caption', $event.target.value)" type="text"
                            placeholder="Resim başlığı (isteğe bağlı)"
                            class="block w-full rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                        <input :value="image.alt_text"
                            @input="updateGalleryItemMeta(index, 'alt_text', $event.target.value)" type="text"
                            placeholder="Alt metin (SEO için önemli)"
                            class="block w-full rounded-md border-gray-300 py-1.5 text-xs shadow-sm focus:border-orange-500 focus:ring-orange-500" />
                    </div>

                    <button @click="removeGalleryImage(index)" type="button" title="Bu görseli galeriden kaldır"
                        class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-white shadow-md hover:bg-red-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:ring-offset-2">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5"
                            stroke="currentColor" class="h-4 w-4">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                    </button>
                </div>
            </div>
            <p v-else class="mt-2 text-sm text-gray-500">Galeri için henüz görsel eklenmedi.</p>
        </div>

        <p class="mt-4 text-xs text-gray-500">
            * Görseller otomatik olarak optimize edilecektir. Önerilen en boy oranı 16:9 veya 4:3.
        </p>
    </div>
</template>
