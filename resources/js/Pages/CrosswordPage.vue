<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import CrosswordComponent from './Crossword/CrosswordComponent.vue';
// Import the default/fallback puzzle data
import { examplePuzzle } from './Crossword/crosswordData.js';
// Import PapaParse for CSV parsing
import Papa from 'papaparse';

import { ref, computed, watch } from 'vue';

const props = defineProps({
    initialPuzzleId: {
        type: String,
        default: null
    }
});

// --- State ---
const currentPuzzleData = ref(examplePuzzle); // Start with the default puzzle
const pageTitle = computed(() => currentPuzzleData.value?.title || 'Kare Bulmaca');
const fileInput = ref(null); // Ref for the file input element
const uploadError = ref(''); // Error message for file upload/parsing
const uploadSuccess = ref(''); // Success message for file upload
const isLoading = ref(false); // Loading indicator during parse

// --- State for Manual Inputs (needed for CSV upload) ---
const puzzleTitleInput = ref('');
const puzzleWidthInput = ref(null);
const puzzleHeightInput = ref(null);
const selectedFile = ref(null); // To store the selected file object

// --- Methods ---

// Handle file selection
const handleFileChange = (event) => {
    const file = event.target.files[0];
    if (file) {
        if (file.type === 'text/csv' || file.name.toLowerCase().endsWith('.csv')) {
            selectedFile.value = file;
            uploadError.value = ''; // Clear previous error
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

// Process the selected CSV file
const loadPuzzleFromCsv = () => {
    // Validate manual inputs
    if (!puzzleTitleInput.value || !puzzleWidthInput.value || !puzzleHeightInput.value || !selectedFile.value) {
        uploadError.value = 'Lütfen bulmaca başlığını, boyutlarını (genişlik/yükseklik) girin ve bir CSV dosyası seçin.';
        uploadSuccess.value = '';
        return;
    }
    if (isNaN(parseInt(puzzleWidthInput.value)) || parseInt(puzzleWidthInput.value) <= 0 ||
        isNaN(parseInt(puzzleHeightInput.value)) || parseInt(puzzleHeightInput.value) <= 0) {
        uploadError.value = 'Genişlik ve Yükseklik geçerli pozitif sayılar olmalıdır.';
        uploadSuccess.value = '';
        return;
    }

    isLoading.value = true;
    uploadError.value = '';
    uploadSuccess.value = '';

    Papa.parse(selectedFile.value, {
        header: true,           // Use first row as header
        skipEmptyLines: true,
        delimiter: ",",         // Explicitly set the delimiter
        encoding: "UTF-8",      // Specify encoding
        complete: (results) => {
            isLoading.value = false; // Parsing complete, stop loading indicator

            // --- DETAILED ERROR LOGGING ---
            if (results.errors.length > 0) {
                console.error("--- PapaParse CSV Parsing Errors ---");
                let errorMessages = [];
                results.errors.forEach(err => {
                    if (err.code === 'UndetectableDelimiter') return; // Ignore this warning

                    console.error(`Type: ${err.type}, Code: ${err.code}, Message: ${err.message}, Row: ${err.row}`);
                    let friendlyMsg = `Satır ${err.row + 1}: ${err.message} (${err.code})`;
                    if (err.code === 'TooManyFields' || err.code === 'TooFewFields') {
                        friendlyMsg += ` - Virgül veya tırnak işaretlerini kontrol edin.`;
                    }
                    errorMessages.push(friendlyMsg);
                });
                console.error("------------------------------------");
                if (errorMessages.length > 0) {
                    uploadError.value = `CSV dosyasında ${errorMessages.length} ayrıştırma hatası bulundu. Lütfen konsolu kontrol edin. İlk hata: ${errorMessages[0]}`;
                    return;
                }
            }
            // --- END DETAILED ERROR LOGGING ---


            if (!results.data || results.data.length === 0) {
                uploadError.value = 'CSV dosyası boş veya veri içermiyor.';
                return;
            }

            // Validate headers
            const requiredHeaders = ['number', 'direction', 'row', 'col', 'length', 'text', 'solution'];
            const actualHeaders = results.meta.fields;
            const missingHeaders = requiredHeaders.filter(h => !actualHeaders || !actualHeaders.includes(h));

            if (missingHeaders.length > 0) {
                uploadError.value = `CSV dosyasında eksik başlık(lar) var: ${missingHeaders.join(', ')}. Lütfen formatı kontrol edin.`;
                return;
            }


            // Transform data into clues array
            const clues = [];
            let validationSuccessful = true;
            let validationErrors = [];

            console.log("--- Starting Row Validation ---"); // Added log start
            results.data.forEach((row, index) => {
                const rowNum = index + 2;
                let rowIsValid = true;

                // Trim whitespace
                for (const key in row) {
                    if (typeof row[key] === 'string') {
                        row[key] = row[key].trim();
                    }
                }

                if (Object.values(row).every(val => val === '')) {
                    console.log(`Row ${rowNum}: Skipping empty row.`);
                    return;
                }

                // --- Log Raw Row Data ---
                // console.log(`Row ${rowNum} Raw Data:`, JSON.stringify(row));

                const number = parseInt(row.number);
                const rowIdx = parseInt(row.row);
                const colIdx = parseInt(row.col);
                const length = parseInt(row.length);
                const direction = row.direction?.toLowerCase();
                const text = row.text;
                const solution = row.solution?.toUpperCase();

                // --- Log Parsed Values ---
                console.log(`Row ${rowNum} Parsed: num=${number}, dir=${direction}, r=${rowIdx}, c=${colIdx}, len=${length}, sol=${solution}, text=${text ? text.substring(0, 15) + '...' : 'EMPTY'}`);


                // Detailed Row Validation
                let currentError = null;
                if (isNaN(number)) currentError = `'number' (${row.number}) geçerli bir sayı değil.`;
                else if (isNaN(rowIdx)) currentError = `'row' (${row.row}) geçerli bir sayı değil.`;
                else if (isNaN(colIdx)) currentError = `'col' (${row.col}) geçerli bir sayı değil.`;
                else if (isNaN(length) || length <= 0) currentError = `'length' (${row.length}) geçerli bir pozitif sayı değil.`;
                else if (direction !== 'across' && direction !== 'down') currentError = `'direction' ('${row.direction}') 'across' veya 'down' olmalı.`;
                else if (!text) currentError = `'text' boş olamaz.`;
                else if (!solution) currentError = `'solution' boş olamaz.`;
                else if (solution.length !== length) currentError = `'solution' uzunluğu (${solution.length}) 'length' (${length}) ile eşleşmiyor.`;
                else if (rowIdx < 0 || rowIdx >= puzzleHeightInput.value) currentError = `'row' (${rowIdx}) belirtilen yükseklik (${puzzleHeightInput.value}) sınırları dışında.`;
                else if (colIdx < 0 || colIdx >= puzzleWidthInput.value) currentError = `'col' (${colIdx}) belirtilen genişlik (${puzzleWidthInput.value}) sınırları dışında.`;


                if (currentError) {
                    const errorMsg = `CSV Satır ${rowNum}: ${currentError}`;
                    console.warn(errorMsg, "Satır verisi:", row); // Log the original row data on error
                    validationErrors.push(errorMsg);
                    validationSuccessful = false;
                    rowIsValid = false;
                }

                if (rowIsValid) {
                    clues.push({
                        number: number,
                        direction: direction,
                        row: rowIdx,
                        col: colIdx,
                        length: length,
                        text: text,
                        solution: solution
                    });
                }
            }); // End forEach row
            console.log("--- Finished Row Validation ---"); // Added log end

            if (!validationSuccessful) {
                uploadError.value = `CSV verisi doğrulanırken ${validationErrors.length} hata bulundu. Lütfen konsolu kontrol edin. İlk hata: ${validationErrors[0]}`;
                return;
            }

            if (clues.length === 0) {
                uploadError.value = 'CSV dosyasından geçerli ipucu verisi okunamadı veya tüm satırlar hatalıydı.';
                return;
            }

            // Create the new puzzle data object
            const newPuzzle = {
                title: puzzleTitleInput.value.trim(),
                width: parseInt(puzzleWidthInput.value),
                height: parseInt(puzzleHeightInput.value),
                clues: clues
            };

            currentPuzzleData.value = newPuzzle;
            uploadSuccess.value = `Bulmaca "${newPuzzle.title}" başarıyla CSV'den yüklendi! (${clues.length} ipucu)`;
            uploadError.value = '';

            puzzleTitleInput.value = '';
            puzzleWidthInput.value = null;
            puzzleHeightInput.value = null;
            selectedFile.value = null;
            if (fileInput.value) fileInput.value.value = '';

        },
        error: (error, file) => {
            isLoading.value = false;
            console.error("PapaParse File Reading Error:", error, "File:", file);
            let errorMsg = `Dosya okunurken hata oluştu: ${error.message}.`;
            if (error.message.includes("Permission denied") || error.message.includes("File not found")) {
                errorMsg += " Dosya izinlerini veya dosyanın hala yerinde olduğunu kontrol edin.";
            } else {
                errorMsg += " Dosyanın bozuk olmadığını ve UTF-8 kodlamasında olduğunu kontrol edin.";
            }
            uploadError.value = errorMsg;
        }
    });
};

watch(currentPuzzleData, () => {
    // uploadError.value = '';
    // uploadSuccess.value = '';
});

</script>

<template>
    <GuestLayout :title="pageTitle">
        <div class="py-8 sm:py-12">
            <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

                <div class="mb-8 p-4 sm:p-6 bg-white shadow-sm sm:rounded-lg border border-gray-200">
                    <h2 class="text-xl font-semibold mb-4 text-gray-800">CSV Dosyasından Bulmaca Yükle</h2>
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label for="puzzleTitle" class="block text-sm font-medium text-gray-700 mb-1">Bulmaca
                                Başlığı:</label>
                            <input type="text" id="puzzleTitle" v-model="puzzleTitleInput"
                                placeholder="Örn: Haftalık Bulmaca 1"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="puzzleWidth" class="block text-sm font-medium text-gray-700 mb-1">Genişlik
                                (Sütun):</label>
                            <input type="number" id="puzzleWidth" v-model.number="puzzleWidthInput" min="3" max="50"
                                placeholder="Örn: 13"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                        <div>
                            <label for="puzzleHeight" class="block text-sm font-medium text-gray-700 mb-1">Yükseklik
                                (Satır):</label>
                            <input type="number" id="puzzleHeight" v-model.number="puzzleHeightInput" min="3" max="50"
                                placeholder="Örn: 12"
                                class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm">
                        </div>
                    </div>
                    <div class="flex flex-col sm:flex-row items-center gap-4">
                        <div class="flex-grow">
                            <label for="csvFile" class="block text-sm font-medium text-gray-700 mb-1">CSV Dosyası
                                Seç:</label>
                            <input type="file" id="csvFile" ref="fileInput" @change="handleFileChange"
                                accept=".csv, text/csv"
                                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 cursor-pointer border border-gray-300 rounded-md">
                        </div>
                        <button @click="loadPuzzleFromCsv" :disabled="isLoading || !selectedFile"
                            class="w-full sm:w-auto mt-4 sm:mt-6 px-6 py-2 bg-indigo-600 text-white rounded-md shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 disabled:cursor-not-allowed font-medium">
                            <span v-if="isLoading">Yükleniyor...</span>
                            <span v-else>Bulmacayı Yükle</span>
                        </button>
                    </div>
                    <div v-if="uploadError"
                        class="mt-3 text-sm text-red-600 bg-red-50 p-3 rounded border border-red-200" role="alert">
                        {{ uploadError }}
                    </div>
                    <div v-if="uploadSuccess"
                        class="mt-3 text-sm text-green-600 bg-green-50 p-3 rounded border border-green-200"
                        role="status">
                        {{ uploadSuccess }}
                    </div>
                    <div class="mt-2 text-xs text-gray-500">
                        Not: CSV dosyası UTF-8 kodlamasında olmalı ve başlık satırı içermelidir:
                        number,direction,row,col,length,text,solution. Virgül içeren metinler çift tırnak (" ") içine
                        alınmalıdır.
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <CrosswordComponent
                        v-if="currentPuzzleData && currentPuzzleData.clues && currentPuzzleData.clues.length > 0"
                        :puzzleData="currentPuzzleData" />
                    <div v-else class="p-6 text-gray-500 text-center">
                        Görüntülenecek bulmaca verisi bulunamadı veya yüklenemedi. Lütfen varsayılanı kontrol edin veya
                        bir CSV yükleyin.
                    </div>
                </div>
            </div>
        </div>
    </GuestLayout>
</template>
