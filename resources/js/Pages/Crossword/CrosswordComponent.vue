<!-- CrosswordComponent.vue -->
<template>
    <div class="max-w-7xl mx-auto p-5">
        <!-- Header -->
        <div class="flex items-center justify-between bg-red-500 text-white p-4 mb-6 rounded-lg">
            <h1 class="text-2xl font-bold">Günlük Kare Bulmaca</h1>
            <div class="flex items-center gap-4">
                <button @click="checkPuzzle"
                    class="px-4 py-2 bg-white text-red-500 rounded font-medium hover:bg-red-50 transition-colors">
                    Kontrol Et
                </button>
            </div>
        </div>

        <div class="flex flex-col lg:flex-row gap-8">
            <!-- Grid -->
            <div class="border-2 border-gray-900 inline-block">
                <div v-for="(row, rowIndex) in gridData" :key="'row-' + rowIndex" class="flex">
                    <div v-for="(cell, colIndex) in row" :key="'cell-' + rowIndex + '-' + colIndex"
                        class="w-10 h-10 border border-gray-300 relative" :class="[
                            !cell.isActive ? 'bg-gray-900' : '',
                            isCurrentCell(rowIndex, colIndex) ? 'bg-blue-100' : '',
                            isPartOfCurrentWord(rowIndex, colIndex) ? 'bg-blue-50' : ''
                        ]" @click="selectCell(rowIndex, colIndex)">
                        <div v-if="cell.number" class="absolute top-0.5 left-0.5 text-xs">
                            {{ cell.number }}
                        </div>
                        <input v-if="cell.isActive" type="text" v-model="cell.value" maxlength="1"
                            class="w-full h-full text-center text-xl uppercase bg-transparent focus:outline-none"
                            @keydown="handleKeyDown($event, rowIndex, colIndex)"
                            :ref="el => { if (el) cellRefs[`${rowIndex}-${colIndex}`] = el }"
                            @focus="currentCell = { row: rowIndex, col: colIndex }" />
                    </div>
                </div>
            </div>

            <!-- Clues -->
            <div class="flex-1 grid md:grid-cols-2 gap-6">
                <!-- Across Clues -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Soldan Sağa</h3>
                    <div class="space-y-2">
                        <div v-for="clue in acrossClues" :key="clue.number"
                            class="p-2 rounded cursor-pointer transition-colors"
                            :class="{ 'bg-blue-100': isActiveClue(clue, 'across') }"
                            @click="selectClueStart(clue, 'across')">
                            <span class="font-bold">{{ clue.number }}.</span>
                            <span class="ml-1">{{ clue.text }}</span>
                        </div>
                    </div>
                </div>

                <!-- Down Clues -->
                <div class="bg-gray-50 p-4 rounded-lg">
                    <h3 class="text-xl font-bold mb-4 text-gray-800">Yukarıdan Aşağıya</h3>
                    <div class="space-y-2">
                        <div v-for="clue in downClues" :key="clue.number"
                            class="p-2 rounded cursor-pointer transition-colors"
                            :class="{ 'bg-blue-100': isActiveClue(clue, 'down') }"
                            @click="selectClueStart(clue, 'down')">
                            <span class="font-bold">{{ clue.number }}.</span>
                            <span class="ml-1">{{ clue.text }}</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';

const props = defineProps({
    puzzleData: {
        type: Object,
        required: true
    }
});

const gridData = ref([]);
const currentCell = ref(null);
const cellRefs = ref({});
const currentDirection = ref('across');

// Sabit ipuçları
const acrossClues = ref([
    { number: 1, text: "4 Nisan 1953'te batan denizaltımız (Otorite)", row: 0, col: 0, length: 7, answer: 'DUMLUPI' },
    { number: 3, text: "Öğretim ve eğitim sistemi (Vilayet)", row: 2, col: 0, length: 6, answer: 'MAARIF' }
]);

const downClues = ref([
    { number: 1, text: "Tayin etme", row: 0, col: 0, length: 2, answer: 'DU' },
    { number: 2, text: "Kısa çizgi (İnce perde veya örtü)", row: 0, col: 2, length: 3, answer: 'TUL' }
]);

// Mevcut hücre kontrolü
const isCurrentCell = (row, col) => {
    return currentCell.value &&
        currentCell.value.row === row &&
        currentCell.value.col === col;
};

// Mevcut kelime parçası kontrolü
const isPartOfCurrentWord = (row, col) => {
    if (!currentCell.value) return false;

    if (currentDirection.value === 'across') {
        const currentClue = acrossClues.value.find(clue =>
            clue.row === currentCell.value.row &&
            col >= clue.col &&
            col < clue.col + clue.length
        );
        return currentClue && row === currentCell.value.row;
    } else {
        const currentClue = downClues.value.find(clue =>
            clue.col === currentCell.value.col &&
            row >= clue.row &&
            row < clue.row + clue.length
        );
        return currentClue && col === currentCell.value.col;
    }
};

const initializeGrid = () => {
    const grid = [];
    for (let i = 0; i < 10; i++) {
        const row = [];
        for (let j = 0; j < 10; j++) {
            row.push({
                value: '',
                isActive: false,
                number: null
            });
        }
        grid.push(row);
    }

    // Aktif hücreleri ve numaraları işaretle
    acrossClues.value.forEach(clue => {
        grid[clue.row][clue.col].number = clue.number;
        for (let i = 0; i < clue.length; i++) {
            grid[clue.row][clue.col + i].isActive = true;
        }
    });

    downClues.value.forEach(clue => {
        if (!grid[clue.row][clue.col].number) {
            grid[clue.row][clue.col].number = clue.number;
        }
        for (let i = 0; i < clue.length; i++) {
            grid[clue.row + i][clue.col].isActive = true;
        }
    });

    gridData.value = grid;
};

// Hücre seçimi
const selectCell = (row, col) => {
    if (gridData.value[row][col].isActive) {
        currentCell.value = { row, col };
        cellRefs.value[`${row}-${col}`]?.focus();
    }
};

const handleKeyDown = (event, row, col) => {
    const key = event.key;

    if (/^[a-zA-ZğüşıöçĞÜŞİÖÇ]$/.test(key)) {
        gridData.value[row][col].value = key.toUpperCase();
        moveToNextCell(row, col);
    } else if (key === 'ArrowRight' && col < gridData.value[row].length - 1) {
        selectCell(row, col + 1);
    } else if (key === 'ArrowLeft' && col > 0) {
        selectCell(row, col - 1);
    } else if (key === 'ArrowUp' && row > 0) {
        selectCell(row - 1, col);
    } else if (key === 'ArrowDown' && row < gridData.value.length - 1) {
        selectCell(row + 1, col);
    } else if (key === 'Enter') {
        currentDirection.value = currentDirection.value === 'across' ? 'down' : 'across';
    } else if (key === 'Backspace' || key === 'Delete') {
        if (gridData.value[row][col].value) {
            gridData.value[row][col].value = '';
        } else if (key === 'Backspace') {
            moveToPreviousCell(row, col);
        }
    }
};

const moveToNextCell = (row, col) => {
    if (currentDirection.value === 'across' && col < gridData.value[row].length - 1) {
        selectCell(row, col + 1);
    } else if (currentDirection.value === 'down' && row < gridData.value.length - 1) {
        selectCell(row + 1, col);
    }
};

const moveToPreviousCell = (row, col) => {
    if (currentDirection.value === 'across' && col > 0) {
        selectCell(row, col - 1);
    } else if (currentDirection.value === 'down' && row > 0) {
        selectCell(row - 1, col);
    }
};

const isActiveClue = (clue, direction) => {
    if (!currentCell.value) return false;

    if (direction === 'across') {
        return currentCell.value.row === clue.row &&
            currentCell.value.col >= clue.col &&
            currentCell.value.col < clue.col + clue.length;
    } else {
        return currentCell.value.col === clue.col &&
            currentCell.value.row >= clue.row &&
            currentCell.value.row < clue.row + clue.length;
    }
};

const selectClueStart = (clue, direction) => {
    currentDirection.value = direction;
    selectCell(clue.row, clue.col);
};

const checkPuzzle = () => {
    let allCorrect = true;
    let foundError = false;

    [...acrossClues.value, ...downClues.value].forEach(clue => {
        const word = getWordFromGrid(clue);
        if (word && word !== clue.answer) {
            allCorrect = false;
            foundError = true;
        }
    });

    if (allCorrect) {
        alert('Tebrikler! Bulmaca doğru çözüldü!');
    } else if (foundError) {
        alert('Bazı kelimeler yanlış. Kontrol edip tekrar deneyin.');
    } else {
        alert('Bulmaca henüz tamamlanmadı.');
    }
};

const getWordFromGrid = (clue) => {
    let word = '';
    if (clue.row !== undefined && clue.col !== undefined) {
        for (let i = 0; i < clue.length; i++) {
            const cell = currentDirection.value === 'across'
                ? gridData.value[clue.row][clue.col + i]
                : gridData.value[clue.row + i][clue.col];

            if (!cell || !cell.value) return null;
            word += cell.value;
        }
    }
    return word;
};

onMounted(() => {
    initializeGrid();
});
</script>