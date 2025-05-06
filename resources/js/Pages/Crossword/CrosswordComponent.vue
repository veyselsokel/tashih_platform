<template>
    <div class="max-w-7xl mx-auto p-5 font-sans">
        <div
            class="flex flex-col sm:flex-row items-center justify-between bg-red-500 text-white p-4 mb-6 rounded-lg shadow">
            <h1 class="text-xl sm:text-2xl font-bold mb-2 sm:mb-0">{{ puzzleTitle }}</h1>
            <div class="flex items-center gap-2 sm:gap-4 flex-wrap justify-center">
                <button @click="revealPuzzle"
                    class="px-3 sm:px-4 py-2 bg-yellow-300 text-yellow-800 rounded font-medium hover:bg-yellow-200 transition-colors text-xs sm:text-base">
                    Hepsini Göster
                </button>
                <button @click="clearPuzzle"
                    class="px-3 sm:px-4 py-2 bg-gray-200 text-gray-700 rounded font-medium hover:bg-gray-300 transition-colors text-xs sm:text-base">
                    Temizle
                </button>
                <button @click="checkPuzzle"
                    class="px-3 sm:px-4 py-2 bg-white text-red-500 rounded font-medium hover:bg-red-50 transition-colors text-xs sm:text-base">
                    Kontrol Et
                </button>
            </div>
        </div>

        <div v-if="statusMessage"
            :class="['p-3 mb-4 rounded text-center font-medium', statusType === 'success' ? 'bg-green-100 text-green-700' : statusType === 'error' ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700']"
            role="alert">
            {{ statusMessage }}
        </div>

        <div class="flex flex-col lg:flex-row gap-6 lg:gap-8">
            <div class="border-2 border-gray-900 inline-block mx-auto lg:mx-0 overflow-auto shadow-md">
                <div v-if="gridData.length" class="inline-block bg-gray-100 p-1">
                    <div v-for="(row, rowIndex) in gridData" :key="'row-' + rowIndex" class="flex">
                        <div v-for="(cell, colIndex) in row" :key="'cell-' + rowIndex + '-' + colIndex"
                            class="flex items-center justify-center border border-gray-400 relative"
                            :style="{ width: cellSize + 'px', height: cellSize + 'px' }" :class="[
                                !cell.isActive ? 'bg-gray-800' : 'bg-white', // Dark for inactive, white for active
                                cell.isError ? '!bg-red-200 !text-red-800' : '', // Highlight errors with text color change
                                isCurrentCell(rowIndex, colIndex) ? '!bg-blue-200' : '', // Highlight current cell more strongly
                                isPartOfCurrentWord(rowIndex, colIndex) && !isCurrentCell(rowIndex, colIndex) ? '!bg-blue-50' : '' // Highlight rest of the word
                            ]" @click="selectCell(rowIndex, colIndex)">
                            <div v-if="cell.number"
                                class="absolute top-0 left-0.5 text-[8px] sm:text-[10px] font-bold text-gray-500 select-none">
                                {{ cell.number }}
                            </div>
                            <input v-if="cell.isActive" type="text" v-model="cell.value" maxlength="1"
                                :readonly="!cell.isActive" :aria-label="`Satır ${rowIndex + 1}, Sütun ${colIndex + 1}`"
                                class="w-full h-full text-center text-base sm:text-sm uppercase bg-transparent focus:outline-none font-medium appearance-none caret-blue-500"
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

            <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-4 lg:gap-6">
                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h3 class="text-lg sm:text-xl font-bold mb-3 text-gray-800 border-b pb-2">Soldan Sağa</h3>
                    <div class="space-y-1.5 max-h-96 overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!acrossClues.length" class="text-gray-500 italic">İpucu bulunamadı.</div>
                        <div v-for="clue in acrossClues"
                            :key="'across-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2 rounded cursor-pointer transition-colors hover:bg-blue-50"
                            :class="{ 'bg-blue-100 font-semibold': isActiveClue(clue) }" @click="selectClueStart(clue)">
                            <span class="font-bold mr-1">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-gray-500 text-xs ml-1">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>

                <div class="bg-gray-50 p-4 rounded-lg shadow-sm">
                    <h3 class="text-lg sm:text-xl font-bold mb-3 text-gray-800 border-b pb-2">Yukarıdan Aşağıya</h3>
                    <div class="space-y-1.5 max-h-96 overflow-y-auto pr-2 text-sm sm:text-base">
                        <div v-if="!downClues.length" class="text-gray-500 italic">İpucu bulunamadı.</div>
                        <div v-for="clue in downClues" :key="'down-' + clue.number + '-' + clue.row + '-' + clue.col"
                            class="p-2 rounded cursor-pointer transition-colors hover:bg-blue-50"
                            :class="{ 'bg-blue-100 font-semibold': isActiveClue(clue) }" @click="selectClueStart(clue)">
                            <span class="font-bold mr-1">{{ clue.number }}.</span>
                            <span>{{ clue.text }}</span>
                            <span class="text-gray-500 text-xs ml-1">({{ clue.length }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue';

const props = defineProps({
    // Expect the full puzzle data object
    puzzleData: {
        type: Object,
        required: true,
        default: () => ({ width: 10, height: 10, clues: [], title: 'Bulmaca Yükleniyor...' }) // Provide robust default
    }
});

// --- State ---
const gridData = ref([]); // The main grid state
const currentCell = ref(null); // { row, col }
const cellRefs = ref({}); // References to input elements
const currentDirection = ref('across'); // 'across' or 'down'
const currentClue = ref(null); // The currently active clue object
const statusMessage = ref(''); // Feedback message for the user
const statusType = ref(''); // 'success', 'error' or 'info'
const cellSize = ref(36); // Dynamic cell size (adjust as needed for larger grids)

// --- Computed Properties ---

// Filter clues from the prop data, ensure clues array exists
const acrossClues = computed(() => (props.puzzleData?.clues || []).filter(c => c.direction === 'across').sort((a, b) => a.number - b.number || a.row - b.row || a.col - b.col));
const downClues = computed(() => (props.puzzleData?.clues || []).filter(c => c.direction === 'down').sort((a, b) => a.number - b.number || a.col - b.col || a.row - b.row));
const puzzleTitle = computed(() => props.puzzleData?.title || 'Kare Bulmaca');

// --- Methods ---

// Initialize or re-initialize the grid when puzzleData changes
const initializeGrid = () => {
    // console.log("Initializing grid with data:", props.puzzleData); // Debug log
    // Add more robust checks for valid data structure
    if (!props.puzzleData || typeof props.puzzleData.width !== 'number' || props.puzzleData.width <= 0 ||
        typeof props.puzzleData.height !== 'number' || props.puzzleData.height <= 0 || !Array.isArray(props.puzzleData.clues)) {
        console.error("Invalid or incomplete puzzle data provided for initialization:", props.puzzleData);
        gridData.value = []; // Clear grid if data is bad
        statusMessage.value = "Geçersiz bulmaca verisi.";
        statusType.value = 'error';
        return;
    }

    const { width, height, clues } = props.puzzleData;
    const newGrid = Array.from({ length: height }, (_, r) =>
        Array.from({ length: width }, (_, c) => ({
            value: '',      // User input
            isActive: false, // Is it part of any clue?
            number: null,   // Clue number display
            row: r,         // Cell's row
            col: c,         // Cell's col
            isError: false, // Flag for incorrect cells after check
            clueRefs: []    // References to clues passing through this cell [{ number, direction, clueObj }, ...]
        }))
    );

    let gridIsValid = true; // Flag to track if grid generation was successful

    // Populate the grid based on clues
    clues.forEach(clue => {
        // Basic validation for the clue itself
        if (typeof clue.row !== 'number' || typeof clue.col !== 'number' || typeof clue.length !== 'number' || clue.length <= 0 ||
            !clue.solution || typeof clue.solution !== 'string' || clue.solution.length !== clue.length || typeof clue.number !== 'number' ||
            (clue.direction !== 'across' && clue.direction !== 'down')) {
            console.warn(`Skipping invalid clue format:`, clue);
            return; // Skip malformed clues
        }

        let r = clue.row;
        let c = clue.col;

        // Check if starting position is within bounds
        if (r < 0 || r >= height || c < 0 || c >= width) {
            console.warn(`Clue ${clue.number} (${clue.direction}) starting position (${r}, ${c}) is out of bounds (${width}x${height}). Skipping.`);
            gridIsValid = false;
            return; // Skip this clue
        }

        // Add clue number to the starting cell
        if (newGrid[r][c].number === null) {
            newGrid[r][c].number = clue.number;
        } else {
            // Handle multiple clues starting at the same cell (use smaller number)
            if (typeof clue.number === 'number') {
                newGrid[r][c].number = Math.min(newGrid[r][c].number, clue.number);
            }
        }

        // Mark cells as active and add clue references
        let clueOutOfBounds = false;
        for (let i = 0; i < clue.length; i++) {
            const targetRow = clue.direction === 'down' ? r + i : r;
            const targetCol = clue.direction === 'across' ? c + i : c;

            // Check if target cell is within bounds
            if (targetRow < 0 || targetRow >= height || targetCol < 0 || targetCol >= width) {
                console.warn(`Clue ${clue.number} (${clue.direction}) extends out of bounds at (${targetRow}, ${targetCol}). Truncating or skipping.`);
                clueOutOfBounds = true;
                gridIsValid = false;
                break; // Stop processing this clue if it goes out of bounds
            }

            // Mark active and add ref
            newGrid[targetRow][targetCol].isActive = true;
            if (!newGrid[targetRow][targetCol].clueRefs.some(ref => ref.number === clue.number && ref.direction === clue.direction)) {
                newGrid[targetRow][targetCol].clueRefs.push({ number: clue.number, direction: clue.direction, clueObj: clue }); // Store ref to clue object
            }
        }
    });

    if (!gridIsValid) {
        statusMessage.value = "Bulmaca oluşturulurken hatalar oluştu (sınır dışı ipuçları vb.). Lütfen veriyi kontrol edin.";
        statusType.value = 'error';
        // Optionally clear the grid or show partial grid? Clearing might be safer.
        // gridData.value = [];
        // return;
    }

    gridData.value = newGrid;
    currentCell.value = null; // Reset selection
    currentClue.value = null;
    currentDirection.value = 'across';
    clearStatus(); // Clear any previous messages unless an error occurred during init
    focusFirstClue(); // Focus the start of the first clue
};

// Find the first active cell and focus it
const focusFirstClue = async () => {
    await nextTick(); // Wait for DOM updates
    if (!gridData.value.length) return; // Don't try if grid is empty

    const firstAcross = acrossClues.value[0];
    if (firstAcross && gridData.value[firstAcross.row]?.[firstAcross.col]?.isActive) {
        selectClueStart(firstAcross);
    } else {
        const firstDown = downClues.value[0];
        if (firstDown && gridData.value[firstDown.row]?.[firstDown.col]?.isActive) {
            selectClueStart(firstDown);
        } else {
            // Fallback: Find the very first active cell in the grid (top-left bias)
            const firstActiveCell = gridData.value.flat().find(cell => cell.isActive);
            if (firstActiveCell) {
                selectCell(firstActiveCell.row, firstActiveCell.col);
                focusCell(firstActiveCell.row, firstActiveCell.col);
            } else {
                console.warn("No active cells found in the grid.");
                currentCell.value = null;
                currentClue.value = null;
            }
        }
    }
};

// Update the current clue based on the selected cell and direction
const updateCurrentClue = () => {
    if (!currentCell.value) {
        currentClue.value = null;
        return;
    }
    const { row, col } = currentCell.value;
    const cell = gridData.value[row]?.[col];
    if (!cell || !cell.isActive) {
        currentClue.value = null;
        return;
    }

    // Find a clue reference matching the current direction in this cell
    const matchingClueRef = cell.clueRefs.find(ref => ref.direction === currentDirection.value);

    if (matchingClueRef && matchingClueRef.clueObj) {
        currentClue.value = matchingClueRef.clueObj;
    } else {
        // If no clue for the current direction, try the other direction
        const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
        const fallbackClueRef = cell.clueRefs.find(ref => ref.direction === otherDirection);
        if (fallbackClueRef && fallbackClueRef.clueObj) {
            currentDirection.value = otherDirection; // Switch direction
            currentClue.value = fallbackClueRef.clueObj;
        } else {
            // If still no clue found (e.g., isolated active cell?), clear current clue
            currentClue.value = null;
            // console.warn(`No valid clue found for cell (${row}, ${col})`); // Less noisy
        }
    }
};

// --- Cell Interaction ---

// Handle clicking directly on a cell
const handleCellClick = (row, col) => {
    const cell = gridData.value[row]?.[col];
    if (!cell || !cell.isActive) return;

    // If clicking the same cell, toggle direction if possible
    if (isCurrentCell(row, col)) {
        const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
        const hasOtherClue = cell.clueRefs.some(ref => ref.direction === otherDirection);

        if (hasOtherClue) {
            currentDirection.value = otherDirection;
            updateCurrentClue(); // Update based on new direction
        }
    } else {
        // Clicking a new cell, select it (default to across if multiple clues)
        const hasAcross = cell.clueRefs.some(ref => ref.direction === 'across');
        const hasDown = cell.clueRefs.some(ref => ref.direction === 'down');

        if (hasAcross && hasDown) {
            // If both directions exist, keep current direction if it's valid here, else default to across
            const currentDirValid = cell.clueRefs.some(ref => ref.direction === currentDirection.value);
            if (!currentDirValid) {
                currentDirection.value = 'across';
            }
        } else if (hasDown) {
            currentDirection.value = 'down';
        } else {
            currentDirection.value = 'across'; // Default or only option
        }

        selectCell(row, col); // This will also update the clue based on the new direction
    }
    // Ensure focus goes to the input
    focusCell(row, col);
};


// Select a cell and update related state
const selectCell = (row, col) => {
    const cell = gridData.value[row]?.[col];
    if (cell && cell.isActive) {
        // Clear error state on selection ONLY if it's an error state
        if (cell.isError) cell.isError = false;
        // Clear general error message ONLY if it's an error message
        if (statusType.value === 'error') clearStatus();

        currentCell.value = { row, col };
        updateCurrentClue(); // Determine the active clue based on new cell/direction
    }
};

// Focus the input element of a cell
const focusCell = async (row, col) => {
    await nextTick(); // Ensure the ref is available
    const refKey = `${row}-${col}`;
    const inputElement = cellRefs.value[refKey];
    if (inputElement) {
        inputElement.focus();
        // Select text in cell on focus for easier overwrite
        // Use setTimeout to ensure focus is set before select
        setTimeout(() => inputElement.select(), 0);
    }
};


// Handle focus event on input
const handleFocus = (row, col) => {
    // When a cell gains focus (e.g., by keyboard navigation or click),
    // ensure it's marked as the current cell and the correct clue is highlighted.
    selectCell(row, col);
};


// --- Keyboard Navigation & Input ---

// Handle text input into a cell
const handleInput = (event, row, col) => {
    const cell = gridData.value[row]?.[col];
    if (!cell || !cell.isActive) return;

    const inputElement = event.target;
    const value = inputElement.value.toUpperCase();

    // Turkish character support
    const allowedChars = /^[A-ZÇĞİÖŞÜ]$/;

    if (value.length > 0 && allowedChars.test(value.slice(-1))) {
        const charToInsert = value.slice(-1);
        // Check if the value actually changed (prevents infinite loops with some inputs)
        if (cell.value !== charToInsert) {
            cell.value = charToInsert;
            cell.isError = false; // Clear error on valid input
            moveToNextCell(row, col);
        } else {
            // If the value is the same, maybe move next anyway if user types same letter again?
            moveToNextCell(row, col);
        }
    } else if (value === '') {
        // Allow clearing the cell
        if (cell.value !== '') {
            cell.value = '';
            cell.isError = false;
        }
    } else {
        // If invalid character entered, revert visually
        inputElement.value = cell.value; // Put back the original value
    }
};


// Handle keydown events for navigation and deletion
const handleKeyDown = (event, row, col) => {
    const key = event.key;
    const cell = gridData.value[row]?.[col];
    if (!cell || !cell.isActive) return;

    let handled = false; // Flag to prevent default browser behavior

    // --- Navigation ---
    if (key === 'ArrowRight') {
        currentDirection.value = 'across'; // Set context for movement
        moveToNextCell(row, col);
        handled = true;
    } else if (key === 'ArrowLeft') {
        currentDirection.value = 'across';
        moveToPreviousCell(row, col);
        handled = true;
    } else if (key === 'ArrowUp') {
        currentDirection.value = 'down';
        moveToPreviousCell(row, col);
        handled = true;
    } else if (key === 'ArrowDown') {
        currentDirection.value = 'down';
        moveToNextCell(row, col);
        handled = true;
    }
    // --- Direction Toggle ---
    else if (key === 'Enter' || key === ' ') {
        event.preventDefault(); // Prevent space typing or form submission
        const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
        const hasOtherClue = cell.clueRefs.some(ref => ref.direction === otherDirection);

        if (hasOtherClue) {
            currentDirection.value = otherDirection;
            updateCurrentClue(); // Update highlight
            focusCell(row, col); // Keep focus, select content
        }
        handled = true;
    }
    // --- Deletion ---
    else if (key === 'Backspace') {
        handled = true;
        if (cell.value) {
            cell.value = '';
            cell.isError = false;
        } else {
            moveToPreviousCell(row, col); // Move back if already empty
        }
    } else if (key === 'Delete') {
        handled = true;
        if (cell.value) {
            cell.value = '';
            cell.isError = false;
        }
        // Optional: move forward after delete?
        // moveToNextCell(row, col);
    }
    // --- Tab Navigation (Example - Basic Clue Jump) ---
    else if (key === 'Tab') {
        handled = true;
        event.preventDefault();
        moveToNextClueStart(event.shiftKey);
    }
    // --- Typing ---
    // Let handleInput manage valid character entry and movement
    // Prevent disallowed characters explicitly only if necessary
    else if (key.length === 1 && !/^[a-zA-ZçÇğĞıİöÖşŞüÜ]$/.test(key) && !event.ctrlKey && !event.metaKey && !event.altKey) {
        // console.log("Prevented typing:", key);
        handled = true; // Prevent symbols, numbers etc. if desired
    }


    if (handled) {
        event.preventDefault();
    }
};

// Move to the next logical *active* cell based on current direction
const moveToNextCell = (row, col) => {
    if (!currentClue.value) return; // Don't move if not in a clue

    const dir = currentDirection.value;
    const clue = currentClue.value;
    let nextRow = row;
    let nextCol = col;

    if (dir === 'across') {
        // Check if we are at the end of the current clue
        if (col < clue.col + clue.length - 1) {
            nextCol++;
        } else {
            // Optional: Jump to next clue start? Or just stop.
            return;
        }
    } else { // down
        if (row < clue.row + clue.length - 1) {
            nextRow++;
        } else {
            // Optional: Jump to next clue start? Or just stop.
            return;
        }
    }

    // Ensure the calculated next cell is actually active (it should be within the clue)
    const nextCell = gridData.value[nextRow]?.[nextCol];
    if (nextCell && nextCell.isActive) {
        selectCell(nextRow, nextCol); // Select first
        focusCell(nextRow, nextCol);  // Then focus
    }
};

// Move to the previous logical *active* cell based on current direction
const moveToPreviousCell = (row, col) => {
    if (!currentClue.value) return; // Don't move if not in a clue

    const dir = currentDirection.value;
    const clue = currentClue.value;
    let prevRow = row;
    let prevCol = col;

    if (dir === 'across') {
        // Check if we are at the start of the current clue
        if (col > clue.col) {
            prevCol--;
        } else {
            return; // Stop at the beginning
        }
    } else { // down
        if (row > clue.row) {
            prevRow--;
        } else {
            return; // Stop at the beginning
        }
    }

    // Ensure the calculated previous cell is actually active
    const prevCell = gridData.value[prevRow]?.[prevCol];
    if (prevCell && prevCell.isActive) {
        selectCell(prevRow, prevCol); // Select first
        focusCell(prevRow, prevCol);  // Then focus
    }
};

// Basic Tab/Shift+Tab navigation between clue starts
const moveToNextClueStart = (isShift = false) => {
    const allClues = [...acrossClues.value, ...downClues.value];
    if (!allClues.length) return;

    let currentClueIndex = -1;
    if (currentClue.value) {
        currentClueIndex = allClues.findIndex(c =>
            c.number === currentClue.value.number &&
            c.direction === currentClue.value.direction &&
            c.row === currentClue.value.row &&
            c.col === currentClue.value.col
        );
    }

    let nextClueIndex;
    if (isShift) {
        nextClueIndex = (currentClueIndex - 1 + allClues.length) % allClues.length;
    } else {
        nextClueIndex = (currentClueIndex + 1) % allClues.length;
    }

    const nextClue = allClues[nextClueIndex];
    if (nextClue) {
        selectClueStart(nextClue);
    }
};


// --- Clue Interaction ---

// Check if a given clue is the currently active one
const isActiveClue = (clue) => {
    return currentClue.value &&
        currentClue.value.number === clue.number &&
        currentClue.value.direction === clue.direction &&
        currentClue.value.row === clue.row &&
        currentClue.value.col === clue.col;
};

// Select the starting cell of a clicked clue
const selectClueStart = (clue) => {
    if (gridData.value[clue.row]?.[clue.col]?.isActive) {
        currentDirection.value = clue.direction;
        selectCell(clue.row, clue.col);
        focusCell(clue.row, clue.col); // Focus the input after selecting
    } else {
        console.warn("Attempted to select start of a clue pointing to an inactive cell:", clue);
    }
};


// --- Puzzle Actions ---

// Check the puzzle for correctness
const checkPuzzle = () => {
    if (!gridData.value.length) {
        statusMessage.value = "Kontrol edilecek bulmaca yok.";
        statusType.value = 'info';
        return;
    }
    clearStatus(); // Clear previous messages and errors first
    let incorrectCluesCount = 0;
    let emptyCellsInClues = false;
    let allFilled = true;
    let allCorrect = true;

    // Reset error state and check for empty cells
    gridData.value.flat().forEach(cell => {
        if (cell.isActive) {
            cell.isError = false;
            if (!cell.value) {
                allFilled = false;
            }
        }
    });

    // Check each clue
    (props.puzzleData?.clues || []).forEach(clue => {
        let wordCorrect = true;
        let wordHasEmpty = false;

        for (let i = 0; i < clue.length; i++) {
            const r = clue.direction === 'down' ? clue.row + i : clue.row;
            const c = clue.direction === 'across' ? clue.col + i : clue.col;
            const cell = gridData.value[r]?.[c];

            if (cell && cell.isActive) {
                if (!cell.value) {
                    wordHasEmpty = true;
                    wordCorrect = false;
                } else if (cell.value !== clue.solution[i]) {
                    wordCorrect = false;
                }
            } else {
                console.error(`Cell (${r}, ${c}) for clue ${clue.number} invalid during check.`);
                wordCorrect = false;
                break;
            }
        }

        if (wordHasEmpty) {
            emptyCellsInClues = true;
            allCorrect = false;
        }

        if (!wordCorrect && !wordHasEmpty) {
            incorrectCluesCount++;
            allCorrect = false;
            // Mark cells of incorrect, filled clue as error
            for (let i = 0; i < clue.length; i++) {
                const r = clue.direction === 'down' ? clue.row + i : clue.row;
                const c = clue.direction === 'across' ? clue.col + i : clue.col;
                if (gridData.value[r]?.[c]) gridData.value[r][c].isError = true;
            }
        }
    });

    // Determine status message
    if (allCorrect && allFilled) {
        statusMessage.value = 'Tebrikler! Bulmacayı tamamen doğru çözdünüz!';
        statusType.value = 'success';
    } else if (incorrectCluesCount > 0) {
        statusMessage.value = `Bazı cevaplar hatalı (${incorrectCluesCount} kelime). Hatalı hücreler işaretlendi.`;
        statusType.value = 'error';
    } else if (!allFilled) {
        statusMessage.value = 'Bulmaca henüz tamamlanmadı. Boş hücreler var.';
        statusType.value = 'info';
    } else {
        statusMessage.value = 'Kontrol tamamlandı. Her şey doğru görünüyor!';
        statusType.value = 'success'; // All filled, no errors found
    }
};


// Reveal all answers
const revealPuzzle = () => {
    if (!gridData.value.length) return;
    clearStatus();
    gridData.value.flat().forEach(cell => { cell.isError = false; }); // Clear errors

    (props.puzzleData?.clues || []).forEach(clue => {
        for (let i = 0; i < clue.length; i++) {
            const r = clue.direction === 'down' ? clue.row + i : clue.row;
            const c = clue.direction === 'across' ? clue.col + i : clue.col;
            if (gridData.value[r]?.[c]?.isActive) {
                gridData.value[r][c].value = clue.solution[i];
            }
        }
    });
    statusMessage.value = 'Tüm cevaplar gösterildi.';
    statusType.value = 'info';
};

// Clear all user input
const clearPuzzle = () => {
    if (!gridData.value.length) return;
    clearStatus();
    gridData.value.flat().forEach(cell => {
        if (cell.isActive) {
            cell.value = '';
            cell.isError = false;
        }
    });
    statusMessage.value = 'Girişler temizlendi.';
    statusType.value = 'info';
    focusFirstClue(); // Focus back on the first clue
};


// Clear status messages and optionally cell errors
const clearStatus = () => {
    statusMessage.value = '';
    // Keep previous statusType to decide if errors should be cleared
    // If the last action was a success, don't clear errors from a previous check
    // If the last action was info or error, clear existing errors
    if (statusType.value !== 'success') {
        gridData.value.flat().forEach(cell => cell.isError = false);
    }
    statusType.value = 'info'; // Default to info after clearing
};

// --- Helpers ---

// Check if a cell is the currently selected one
const isCurrentCell = (row, col) => {
    return currentCell.value && currentCell.value.row === row && currentCell.value.col === col;
};

// Check if a cell is part of the currently selected clue/word
const isPartOfCurrentWord = (row, col) => {
    if (!currentClue.value || !currentCell.value) return false;
    const clue = currentClue.value;
    if (clue.direction === 'across') {
        return row === clue.row && col >= clue.col && col < clue.col + clue.length;
    } else { // down
        return col === clue.col && row >= clue.row && row < clue.row + clue.length;
    }
};


// --- Lifecycle Hooks ---

onMounted(() => {
    // Initialize with default/prop data on mount
    initializeGrid();
});

// Watch for changes in the puzzleData prop and re-initialize
watch(() => props.puzzleData, (newPuzzleData, oldPuzzleData) => {
    // Avoid re-initializing if the data is effectively the same
    if (newPuzzleData && JSON.stringify(newPuzzleData) !== JSON.stringify(oldPuzzleData)) {
        // console.log("Puzzle data prop changed, re-initializing grid..."); // Debug log
        initializeGrid();
    }
}, { deep: true }); // Use deep watch

</script>

<style scoped>
/* Focus style using background and inset shadow */
input:focus {
    background-color: theme('colors.blue.100');
    box-shadow: inset 0 0 0 2px theme('colors.blue.400');
    outline: none;
    /* Remove default outline */
}

/* Style for inactive cells */
.bg-gray-800 {
    /* Make inactive cells non-interactive */
    pointer-events: none;
}

/* Custom scrollbar for clue lists */
.max-h-96::-webkit-scrollbar {
    width: 6px;
}

.max-h-96::-webkit-scrollbar-track {
    background: theme('colors.gray.100');
    border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb {
    background: theme('colors.gray.400');
    border-radius: 3px;
}

.max-h-96::-webkit-scrollbar-thumb:hover {
    background: theme('colors.gray.500');
}

/* Firefox scrollbar */
.max-h-96 {
    scrollbar-width: thin;
    scrollbar-color: theme('colors.gray.400') theme('colors.gray.100');
}


/* Ensure grid cells don't shrink */
.flex>div[class*="w-"],
.flex>div[style*="width"] {
    flex-shrink: 0;
}

/* Prevent selection of cell numbers */
.select-none {
    user-select: none;
}

/* Style caret color */
.caret-blue-500 {
    caret-color: theme('colors.blue.500');
}
</style>
