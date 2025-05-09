<template>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 font-sans bg-slate-100 min-h-screen">
        <div
            class="flex flex-col sm:flex-row items-center justify-between bg-gradient-to-r from-sky-500 to-indigo-600 text-white p-4 mb-6 rounded-xl shadow-lg">
            <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-0">{{ puzzleTitle }}</h1>
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-center">
                <button @click="revealPuzzle"
                    class="px-4 py-2 bg-yellow-400 text-yellow-900 rounded-lg font-semibold hover:bg-yellow-300 transition-all shadow hover:shadow-md text-sm sm:text-base">
                    Hepsini Göster
                </button>
                <button @click="clearPuzzle"
                    class="px-4 py-2 bg-slate-200 text-slate-700 rounded-lg font-semibold hover:bg-slate-300 transition-all shadow hover:shadow-md text-sm sm:text-base">
                    Temizle
                </button>
                <button @click="checkPuzzle"
                    class="px-4 py-2 bg-sky-500 text-white rounded-lg font-semibold hover:bg-sky-600 transition-all shadow hover:shadow-md text-sm sm:text-base">
                    Kontrol Et
                </button>
            </div>
        </div>

        <div v-if="statusMessage" :class="['p-3 mb-6 rounded-lg text-center font-medium shadow',
            statusType === 'success' ? 'bg-green-100 text-green-700 border border-green-300' :
                statusType === 'error' ? 'bg-red-100 text-red-700 border border-red-300' :
                    'bg-blue-100 text-blue-700 border border-blue-300']" role="alert">
            {{ statusMessage }}
        </div>

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
            <div
                class="border-2 border-slate-300 inline-block mx-auto lg:mx-0 overflow-auto shadow-xl rounded-lg bg-slate-200">
                <div v-if="gridData.length" class="inline-block p-1.5">
                    <div v-for="(row, rowIndex) in gridData" :key="'row-' + rowIndex" class="flex">
                        <div v-for="(cell, colIndex) in row" :key="'cell-' + rowIndex + '-' + colIndex"
                            class="flex items-center justify-center border border-slate-400/80 relative"
                            :style="{ width: cellSize + 'px', height: cellSize + 'px' }" :class="[
                                !cell.isActive ? 'bg-slate-700 pointer-events-none' : 'bg-white',
                                cell.isError ? '!bg-red-200 !text-red-800' : '',
                                isCurrentCell(rowIndex, colIndex) ? '!bg-sky-200' : '',
                                isPartOfCurrentWord(rowIndex, colIndex) && !isCurrentCell(rowIndex, colIndex) ? '!bg-sky-50' : ''
                            ]" @click="handleCellClick(rowIndex, colIndex)">
                            <div v-if="cell.number"
                                class="absolute top-0.5 left-0.5 text-[10px] sm:text-[11px] font-bold text-slate-500 select-none">
                                {{ cell.number }}
                            </div>
                            <input v-if="cell.isActive" type="text" v-model="cell.value" maxlength="1"
                                :readonly="!cell.isActive" :aria-label="`Satır ${rowIndex + 1}, Sütun ${colIndex + 1}`"
                                class="w-full h-full text-center text-xl uppercase bg-transparent focus:outline-none font-medium appearance-none caret-sky-600"
                                @input="handleInput($event, rowIndex, colIndex)"
                                @keydown="handleKeyDown($event, rowIndex, colIndex)"
                                @focus="handleFocus(rowIndex, colIndex)"
                                @click.stop="handleCellClick(rowIndex, colIndex)"
                                :ref="el => { if (el) cellRefs[`${rowIndex}-${colIndex}`] = el }" autocomplete="off"
                                autocorrect="off" autocapitalize="characters" spellcheck="false" />
                        </div>
                    </div>
                </div>
                <div v-else class="text-center p-10 text-slate-500">
                    Bulmaca yükleniyor veya veri yok...
                </div>
            </div>

            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <div class="bg-slate-50 p-4 rounded-xl shadow-lg">
                    <h3 class="text-xl font-bold mb-3 text-slate-800 border-b border-slate-300 pb-2">Soldan Sağa</h3>
                    <div class="space-y-1.5 max-h-[30rem] overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!acrossClues.length && !isLoading" class="text-slate-500 italic py-2">İpucu
                            bulunamadı.</div>
                        <div v-if="isLoading" class="text-slate-500 italic py-2">İpuçları yükleniyor...</div>
                        <div v-for="clue in acrossClues"
                            :key="'across-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2.5 rounded-md cursor-pointer transition-all duration-150 hover:bg-sky-100"
                            :class="{ 'bg-sky-200 !text-sky-800 font-semibold shadow-sm': isActiveClue(clue) }"
                            @click="selectClueStart(clue)">
                            <span class="font-bold mr-1.5">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-slate-500 text-xs ml-1.5">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>

                <div class="bg-slate-50 p-4 rounded-xl shadow-lg">
                    <h3 class="text-xl font-bold mb-3 text-slate-800 border-b border-slate-300 pb-2">Yukarıdan Aşağıya
                    </h3>
                    <div class="space-y-1.5 max-h-[30rem] overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!downClues.length && !isLoading" class="text-slate-500 italic py-2">İpucu bulunamadı.
                        </div>
                        <div v-if="isLoading" class="text-slate-500 italic py-2">İpuçları yükleniyor...</div>
                        <div v-for="clue in downClues" :key="'down-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2.5 rounded-md cursor-pointer transition-all duration-150 hover:bg-sky-100"
                            :class="{ 'bg-sky-200 !text-sky-800 font-semibold shadow-sm': isActiveClue(clue) }"
                            @click="selectClueStart(clue)">
                            <span class="font-bold mr-1.5">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-slate-500 text-xs ml-1.5">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { useCrossword } from '@/composables/useCrossword'; // Assuming useCrossword.js is in the same directory

// Define props for the component
const props = defineProps({
    puzzleData: {
        type: Object,
        required: true,
        default: () => ({
            width: 10,
            height: 10,
            clues: [],
            title: 'Bulmaca Yükleniyor...'
        })
    }
});

// Utilize the composable, passing the props
const {
    gridData,
    // currentCell, // Not directly used in template, but part of composable's returned state
    cellRefs,
    // currentDirection, // Not directly used in template
    // currentClue, // Not directly used in template
    statusMessage,
    statusType,
    cellSize,
    acrossClues,
    downClues,
    puzzleTitle,
    // initializeGrid, // Called internally by composable
    // focusFirstClue, // Called internally by composable
    // updateCurrentClue, // Called internally by composable
    handleCellClick,
    // selectCell, // Called internally by composable
    // focusCell, // Called internally by composable
    handleFocus,
    handleInput,
    handleKeyDown,
    // moveToNextCell, // Called internally by composable
    // moveToPreviousCell, // Called internally by composable
    // moveToNextClueStart, // Called internally by composable
    isActiveClue,
    selectClueStart,
    checkPuzzle,
    revealPuzzle,
    clearPuzzle,
    // clearStatus, // Called internally by composable
    isCurrentCell,
    isPartOfCurrentWord,
    isLoading // Added for loading state feedback
} = useCrossword(props);

// Note: onMounted and watch for puzzleData are handled within the composable.
</script>

<style scoped>
/* Custom focus style for input cells */
input:focus {
    background-color: theme('colors.sky.100');
    box-shadow: inset 0 0 0 2px theme('colors.sky.500');
    outline: none;
}

/* Custom scrollbar for clue lists */
.max-h-\[30rem\]::-webkit-scrollbar {
    /* Adjusted max-h for consistency */
    width: 8px;
}

.max-h-\[30rem\]::-webkit-scrollbar-track {
    background: theme('colors.slate.200');
    border-radius: 4px;
}

.max-h-\[30rem\]::-webkit-scrollbar-thumb {
    background: theme('colors.slate.400');
    border-radius: 4px;
}

.max-h-\[30rem\]::-webkit-scrollbar-thumb:hover {
    background: theme('colors.slate.500');
}

/* Firefox scrollbar */
.max-h-\[30rem\] {
    scrollbar-width: thin;
    scrollbar-color: theme('colors.slate.400') theme('colors.slate.200');
}

/* Ensure grid cells don't shrink if container is too small (though overflow handles it) */
.flex>div[class*="w-"],
.flex>div[style*="width"] {
    flex-shrink: 0;
}

/* Prevent selection of cell numbers (already in class) */
.select-none {
    user-select: none;
}

/* Caret color (already in class) */
.caret-sky-600 {
    caret-color: theme('colors.sky.600');
}
</style>
