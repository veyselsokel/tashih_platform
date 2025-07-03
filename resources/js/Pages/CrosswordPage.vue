<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CrosswordComponent from './Crossword/CrosswordComponent.vue';
import { examplePuzzle } from './Crossword/crosswordData.js';
import Papa from 'papaparse';
import { ref, computed } from 'vue';

const currentPuzzleData = ref(examplePuzzle);
const pageTitle = computed(() => currentPuzzleData.value?.title || 'Kare Bulmaca');
const fileInput = ref(null);
const uploadError = ref('');
const uploadSuccess = ref('');
const isLoading = ref(false);

const puzzleTitleInput = ref('');
const puzzleWidthInput = ref(null);
const puzzleHeightInput = ref(null);
const selectedFile = ref(null);

const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type === 'text/csv' || file.name.toLowerCase().endsWith('.csv')) {
            selectedFile.value = file;
            uploadError.value = '';
            uploadSuccess.value = `Dosya seçildi: ${file.name}`;
        } else {
            selectedFile.value = null;
            uploadError.value = 'Lütfen geçerli bir CSV dosyası seçin.';
            uploadSuccess.value = '';
            if (fileInput.value) fileInput.value.value = '';
        }
    } else {
        selectedFile.value = null;
        uploadSuccess.value = '';
    }
};

const loadPuzzleFromCsv = () => {
    if (!puzzleTitleInput.value || !puzzleWidthInput.value || !puzzleHeightInput.value || !selectedFile.value) {
        uploadError.value = 'Lütfen bulmaca başlığını, boyutlarını (genişlik/yükseklik) girin ve bir CSV dosyası seçin.';
        return;
    }
    isLoading.value = true;
    uploadError.value = '';
    uploadSuccess.value = '';

    Papa.parse(selectedFile.value, {
        header: true,
        skipEmptyLines: true,
        complete: (results) => {
            isLoading.value = false;
            if (results.errors.length) {
                uploadError.value = `CSV ayrıştırma hatası: ${results.errors[0].message}`;
                return;
            }
            const clues = results.data.map(row => ({
                ...row,
                number: parseInt(row.number),
                row: parseInt(row.row),
                col: parseInt(row.col),
                length: parseInt(row.length),
            }));
            currentPuzzleData.value = {
                title: puzzleTitleInput.value.trim(),
                width: parseInt(puzzleWidthInput.value),
                height: parseInt(puzzleHeightInput.value),
                clues: clues
            };
            uploadSuccess.value = `Bulmaca "${currentPuzzleData.value.title}" başarıyla yüklendi!`;
        },
        error: (error) => {
            isLoading.value = false;
            uploadError.value = `Dosya okunurken hata: ${error.message}`;
        }
    });
};
</script>

<template>
    <GuestLayout :title="pageTitle">
        <div class="bg-stone-50 py-28">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-8 p-6 bg-white shadow-lg rounded-xl border border-gray-200">
                    <h2 class="text-2xl font-bold mb-4 text-gray-800">Kendi Bulmacanı Yükle</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="puzzleTitle" class="block text-sm font-medium text-gray-700 mb-1">Bulmaca Başlığı:</label>
                            <input type="text" id="puzzleTitle" v-model="puzzleTitleInput" placeholder="Örn: Haftalık Bulmaca 1" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="puzzleWidth" class="block text-sm font-medium text-gray-700 mb-1">Genişlik (Sütun):</label>
                            <input type="number" id="puzzleWidth" v-model.number="puzzleWidthInput" min="3" max="50" placeholder="Örn: 13" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="puzzleHeight" class="block text-sm font-medium text-gray-700 mb-1">Yükseklik (Satır):</label>
                            <input type="number" id="puzzleHeight" v-model.number="puzzleHeightInput" min="3" max="50" placeholder="Örn: 12" class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-orange-500 focus:border-orange-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-grow">
                            <label for="csvFile" class="block text-sm font-medium text-gray-700 mb-1">CSV Dosyası Seç:</label>
                            <input type="file" id="csvFile" ref="fileInput" @change="handleFileChange" accept=".csv, text/csv" class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-orange-50 file:text-orange-700 hover:file:bg-orange-100 cursor-pointer border border-gray-300 rounded-md">
                        </div>
                        <button @click="loadPuzzleFromCsv" :disabled="isLoading || !selectedFile" class="w-full sm:w-auto mt-4 sm:mt-6 px-6 py-2 bg-orange-500 text-white rounded-full shadow-md hover:bg-orange-600 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-orange-500 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                            <span v-if="isLoading">Yükleniyor...</span>
                            <span v-else>Bulmacayı Yükle</span>
                        </button>
                    </div>
                    <div v-if="uploadError" class="mt-3 text-sm text-red-600 bg-red-50 p-3 rounded border border-red-200" role="alert">{{ uploadError }}</div>
                    <div v-if="uploadSuccess" class="mt-3 text-sm text-green-600 bg-green-50 p-3 rounded border border-green-200" role="status">{{ uploadSuccess }}</div>
                </div>

                <div class="bg-white overflow-hidden shadow-lg sm:rounded-xl border border-gray-200">
                    <CrosswordComponent v-if="currentPuzzleData && currentPuzzleData.clues.length > 0" :puzzleData="currentPuzzleData" />
                    <div v-else class="p-6 text-gray-500 text-center">Görüntülenecek bulmaca verisi bulunamadı.</div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>