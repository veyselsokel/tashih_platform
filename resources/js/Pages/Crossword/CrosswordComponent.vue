<template>
    <div class="max-w-7xl mx-auto p-4 sm:p-6 lg:p-8 font-sans bg-stone-100 min-h-screen">
        <div
            class="flex flex-col sm:flex-row items-center justify-between bg-gradient-to-r from-orange-400 to-orange-500 text-white p-4 mb-6 rounded-xl shadow-lg">
            <h1 class="text-2xl sm:text-3xl font-bold mb-3 sm:mb-0">{{ puzzleTitle }}</h1>
            <div class="flex items-center gap-2 sm:gap-3 flex-wrap justify-center">
                <button @click="revealPuzzle"
                    class="px-4 py-2 bg-yellow-300 text-yellow-900 rounded-lg font-semibold hover:bg-yellow-200 transition-all shadow hover:shadow-md text-sm sm:text-base">
                    Hepsini Göster
                </button>
                <button @click="clearPuzzle"
                    class="px-4 py-2 bg-stone-200 text-stone-700 rounded-lg font-semibold hover:bg-stone-300 transition-all shadow hover:shadow-md text-sm sm:text-base">
                    Temizle
                </button>
                <button @click="checkPuzzle"
                    class="px-4 py-2 bg-orange-500 text-white rounded-lg font-semibold hover:bg-orange-600 transition-all shadow hover:shadow-md text-sm sm:text-base border border-orange-600">
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
                class="border-2 border-gray-300 inline-block mx-auto lg:mx-0 overflow-auto shadow-xl rounded-lg bg-gray-200">
                <div v-if="gridData.length" class="inline-block p-1.5">
                    <div v-for="(row, rowIndex) in gridData" :key="'row-' + rowIndex" class="flex">
                        <div v-for="(cell, colIndex) in row" :key="'cell-' + rowIndex + '-' + colIndex"
                            class="flex items-center justify-center border border-gray-400/80 relative"
                            :style="{ width: cellSize + 'px', height: cellSize + 'px' }" :class="[
                                !cell.isActive ? 'bg-gray-800 pointer-events-none' : 'bg-white',
                                cell.isError ? '!bg-red-200 !text-red-800' : '',
                                isCurrentCell(rowIndex, colIndex) ? '!bg-orange-200' : '',
                                isPartOfCurrentWord(rowIndex, colIndex) && !isCurrentCell(rowIndex, colIndex) ? '!bg-orange-50' : ''
                            ]" @click="handleCellClick(rowIndex, colIndex)">
                            <div v-if="cell.number"
                                class="absolute top-0.5 left-0.5 text-[10px] sm:text-[11px] font-bold text-gray-500 select-none">
                                {{ cell.number }}
                            </div>
                            <input v-if="cell.isActive" type="text" v-model="cell.value" maxlength="1"
                                :readonly="!cell.isActive" :aria-label="`Satır ${rowIndex + 1}, Sütun ${colIndex + 1}`"
                                class="w-full h-full text-center text-xl uppercase bg-transparent focus:outline-none font-medium appearance-none caret-orange-600"
                                @input="handleInput($event, rowIndex, colIndex)"
                                @keydown="handleKeyDown($event, rowIndex, colIndex)"
                                @focus="handleFocus(rowIndex, colIndex)"
                                @click.stop="handleCellClick(rowIndex, colIndex)"
                                :ref="el => { if (el) cellRefs[`${rowIndex}-${colIndex}`] = el }" autocomplete="off"
                                autocorrect="off" autocapitalize="characters" spellcheck="false" />
                        </div>
                    </div>
                </div>
                <div v-else class="text-center p-10 text-gray-500">
                    Bulmaca yükleniyor veya veri yok...
                </div>
            </div>

            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-6 lg:gap-8">
                <div class="bg-stone-50 p-4 rounded-xl shadow-lg">
                    <h3 class="text-xl font-bold mb-3 text-gray-800 border-b border-gray-300 pb-2">Soldan Sağa</h3>
                    <div class="space-y-1.5 max-h-[30rem] overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!acrossClues.length && !isLoading" class="text-gray-500 italic py-2">İpucu
                            bulunamadı.</div>
                        <div v-if="isLoading" class="text-gray-500 italic py-2">İpuçları yükleniyor...</div>
                        <div v-for="clue in acrossClues"
                            :key="'across-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2.5 rounded-md cursor-pointer transition-all duration-150 hover:bg-orange-100"
                            :class="{ 'bg-orange-200 !text-orange-800 font-semibold shadow-sm': isActiveClue(clue) }"
                            @click="selectClueStart(clue)">
                            <span class="font-bold mr-1.5">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-gray-500 text-xs ml-1.5">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>

                <div class="bg-stone-50 p-4 rounded-xl shadow-lg">
                    <h3 class="text-xl font-bold mb-3 text-gray-800 border-b border-gray-300 pb-2">Yukarıdan Aşağıya
                    </h3>
                    <div class="space-y-1.5 max-h-[30rem] overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!downClues.length && !isLoading" class="text-gray-500 italic py-2">İpucu bulunamadı.
                        </div>
                        <div v-if="isLoading" class="text-gray-500 italic py-2">İpuçları yükleniyor...</div>
                        <div v-for="clue in downClues" :key="'down-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2.5 rounded-md cursor-pointer transition-all duration-150 hover:bg-orange-100"
                            :class="{ 'bg-orange-200 !text-orange-800 font-semibold shadow-sm': isActiveClue(clue) }"
                            @click="selectClueStart(clue)">
                            <span class="font-bold mr-1.5">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-gray-500 text-xs ml-1.5">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { defineProps } from 'vue';
import { useCrossword } from '@/composables/useCrossword';

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

const {
    gridData,
    cellRefs,
    statusMessage,
    statusType,
    cellSize,
    acrossClues,
    downClues,
    puzzleTitle,
    handleCellClick,
    handleFocus,
    handleInput,
    handleKeyDown,
    isActiveClue,
    selectClueStart,
    checkPuzzle,
    revealPuzzle,
    clearPuzzle,
    isCurrentCell,
    isPartOfCurrentWord,
    isLoading
} = useCrossword(props);

</script>

<style scoped>
input:focus {
    background-color: theme('colors.orange.100');
    box-shadow: inset 0 0 0 2px theme('colors.orange.500');
    outline: none;
}

.max-h-\[30rem\]::-webkit-scrollbar {
    width: 8px;
}

.max-h-\[30rem\]::-webkit-scrollbar-track {
    background: theme('colors.stone.200');
    border-radius: 4px;
}

.max-h-\[30rem\]::-webkit-scrollbar-thumb {
    background: theme('colors.stone.400');
    border-radius: 4px;
}

.max-h-\[30rem\]::-webkit-scrollbar-thumb:hover {
    background: theme('colors.stone.500');
}

.max-h-\[30rem\] {
    scrollbar-width: thin;
    scrollbar-color: theme('colors.stone.400') theme('colors.stone.200');
}

.flex>div[class*="w-"],
.flex>div[style*="width"] {
    flex-shrink: 0;
}

.select-none {
    user-select: none;
}

.caret-orange-600 {
    caret-color: theme('colors.orange.600');
}
</style>