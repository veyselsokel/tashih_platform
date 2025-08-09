<script setup>
import { ref, watch, computed } from 'vue';
import { X, ChevronLeft, ChevronRight } from 'lucide-vue-next';


const props = defineProps({
    images: {
        type: Array,
        required: true,
        default: () => []
    },
    isOpen: {
        type: Boolean,
        default: false
    },
    initialIndex: {
        type: Number,
        default: 0
    }
});

const emit = defineEmits(['close']);
const currentIndex = ref(props.initialIndex);

// props.images değiştiğinde veya boş olduğunda kontrol
watch(() => props.images, (newImages) => {
    if (!newImages || newImages.length === 0) {
        emit('close');
    }
}, { immediate: true });

watch(() => props.isOpen, (newVal) => {
    if (newVal) {
        document.body.style.overflow = 'hidden';
        // currentIndex'i güvenli bir şekilde ayarla
        currentIndex.value = Math.min(props.initialIndex, props.images.length - 1);
    } else {
        document.body.style.overflow = '';
    }
});

const next = () => {
    if (props.images && props.images.length > 0) {
        currentIndex.value = (currentIndex.value + 1) % props.images.length;
    }
};

const prev = () => {
    if (props.images && props.images.length > 0) {
        currentIndex.value = (currentIndex.value - 1 + props.images.length) % props.images.length;
    }
};

const handleKeydown = (e) => {
    if (e.key === 'ArrowRight') next();
    if (e.key === 'ArrowLeft') prev();
    if (e.key === 'Escape') emit('close');
};

// Güvenli bir şekilde mevcut resmi al
const currentImage = computed(() => {
    if (props.images && props.images.length > 0) {
        return props.images[currentIndex.value];
    }
    return null;
});
</script>

<template>
    <div v-if="isOpen && currentImage" @keydown="handleKeydown"
        class="fixed inset-0 z-[9999] overflow-hidden bg-black/90" tabindex="0">
        <!-- Backdrop -->
        <div class="absolute inset-0 h-full w-full" @click="emit('close')"></div>

        <!-- Close Button -->
        <button @click="emit('close')"
            class="absolute top-8 right-8 p-2 text-white hover:text-orange-400 transition-colors z-50">
            <X class="w-8 h-8" />
        </button>

        <!-- Navigation Buttons -->
        <button v-if="images.length > 1" @click="prev"
            class="absolute left-8 top-1/2 -translate-y-1/2 p-2 text-white hover:text-orange-400 transition-colors z-50">
            <ChevronLeft class="w-12 h-12" />
        </button>

        <button v-if="images.length > 1" @click="next"
            class="absolute right-8 top-1/2 -translate-y-1/2 p-2 text-white hover:text-orange-400 transition-colors z-50">
            <ChevronRight class="w-12 h-12" />
        </button>

        <!-- Image Container -->
        <div class="absolute inset-0 flex items-center justify-center p-16">
            <div class="relative w-full h-full flex items-center justify-center">
                <img v-if="currentImage.image_url" :src="currentImage.image_url" :alt="currentImage.alt_text || ''"
                    class="max-w-full max-h-[85vh] object-contain" />

                <!-- Caption -->
                <div v-if="currentImage.caption"
                    class="absolute -bottom-12 left-0 right-0 p-4 bg-black/50 text-white rounded-lg">
                    <p class="text-center text-lg">{{ currentImage.caption }}</p>
                </div>

                <!-- Counter -->
                <div v-if="images.length > 1"
                    class="absolute -top-12 left-0 px-4 py-2 bg-black/50 text-white rounded-lg">
                    {{ currentIndex + 1 }} / {{ images.length }}
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped>
.fade-enter-active,
.fade-leave-active {
    transition: opacity 0.3s ease;
}

.fade-enter-from,
.fade-leave-to {
    opacity: 0;
}

.slide-enter-active,
.slide-leave-active {
    transition: transform 0.3s ease;
}

.slide-enter-from {
    transform: translateX(100%);
}

.slide-leave-to {
    transform: translateX(-100%);
}
</style>
