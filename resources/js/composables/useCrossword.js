import { ref, computed, onMounted, watch, nextTick } from 'vue';

export function useCrossword(props) {
    // --- State ---
    const gridData = ref([]);
    const currentCell = ref(null);
    const cellRefs = ref({});
    const currentDirection = ref('across');
    const currentClue = ref(null);
    const statusMessage = ref('');
    const statusType = ref('');
    const cellSize = ref(44); // Increased cell size for better text fit
    const isLoading = ref(true); // Added for loading state

    // --- Computed Properties ---
    const puzzleTitle = computed(() => props.puzzleData?.title || 'Kare Bulmaca');
    const acrossClues = computed(() => (props.puzzleData?.clues || []).filter(c => c.direction === 'across').sort((a, b) => a.number - b.number || a.row - b.row || a.col - b.col));
    const downClues = computed(() => (props.puzzleData?.clues || []).filter(c => c.direction === 'down').sort((a, b) => a.number - b.number || a.col - b.col || a.row - b.row));

    // --- Methods ---
    const initializeGrid = () => {
        isLoading.value = true;
        statusMessage.value = ''; // Clear previous status
        statusType.value = '';

        if (!props.puzzleData || typeof props.puzzleData.width !== 'number' || props.puzzleData.width <= 0 ||
            typeof props.puzzleData.height !== 'number' || props.puzzleData.height <= 0 || !Array.isArray(props.puzzleData.clues)) {
            console.error("Invalid or incomplete puzzle data for initialization:", props.puzzleData);
            gridData.value = [];
            statusMessage.value = "Geçersiz bulmaca verisi. Lütfen bulmaca yapısını kontrol edin.";
            statusType.value = 'error';
            isLoading.value = false;
            return;
        }

        const { width, height, clues } = props.puzzleData;
        const newGrid = Array.from({ length: height }, (_, r) =>
            Array.from({ length: width }, (_, c) => ({
                value: '', isActive: false, number: null, row: r, col: c, isError: false, clueRefs: []
            }))
        );

        let invalidFormatCluesCount = 0;
        let outOfBoundsCluesCount = 0;

        clues.forEach(clue => {
            // Check for basic clue structure and valid properties
            if (typeof clue.row !== 'number' || typeof clue.col !== 'number' ||
                typeof clue.length !== 'number' || clue.length <= 0 ||
                !clue.solution || typeof clue.solution !== 'string' ||
                clue.solution.length !== clue.length || // Critical check
                typeof clue.number !== 'number' ||
                (clue.direction !== 'across' && clue.direction !== 'down')) {
                console.warn(`Skipping invalid clue format (e.g., length/solution mismatch, missing fields):`, clue);
                invalidFormatCluesCount++;
                return; // Skip this malformed clue
            }

            let r = clue.row;
            let c = clue.col;

            // Check if starting position is within bounds
            if (r < 0 || r >= height || c < 0 || c >= width) {
                console.warn(`Clue ${clue.number} (${clue.direction}) starting position (${r},${c}) is out of bounds (${width}x${height}). Skipping.`);
                outOfBoundsCluesCount++;
                return; // Skip this clue
            }

            // Add clue number to the starting cell
            if (newGrid[r][c].number === null) {
                newGrid[r][c].number = clue.number;
            } else {
                newGrid[r][c].number = Math.min(newGrid[r][c].number, clue.number);
            }

            // Mark cells as active and add clue references
            let clueExtendsOutOfBounds = false;
            for (let i = 0; i < clue.length; i++) {
                const targetRow = clue.direction === 'down' ? r + i : r;
                const targetCol = clue.direction === 'across' ? c + i : c;

                if (targetRow < 0 || targetRow >= height || targetCol < 0 || targetCol >= width) {
                    console.warn(`Clue ${clue.number} (${clue.direction}) extends out of bounds at (${targetRow},${targetCol}). Truncating or skipping.`);
                    clueExtendsOutOfBounds = true;
                    break;
                }
                newGrid[targetRow][targetCol].isActive = true;
                if (!newGrid[targetRow][targetCol].clueRefs.some(ref => ref.number === clue.number && ref.direction === clue.direction)) {
                    newGrid[targetRow][targetCol].clueRefs.push({ number: clue.number, direction: clue.direction, clueObj: clue });
                }
            }
            if (clueExtendsOutOfBounds) {
                outOfBoundsCluesCount++;
            }
        });

        gridData.value = newGrid; // Assign grid even if some clues were bad, to show what's possible

        if (invalidFormatCluesCount > 0 || outOfBoundsCluesCount > 0) {
            let errorMessages = [];
            if (invalidFormatCluesCount > 0) errorMessages.push(`${invalidFormatCluesCount} ipucu geçersiz formatta`);
            if (outOfBoundsCluesCount > 0) errorMessages.push(`${outOfBoundsCluesCount} ipucu sınırlar dışında`);
            statusMessage.value = `Bulmaca yüklenirken sorunlar oluştu: ${errorMessages.join(', ')}. Lütfen bulmaca verisini kontrol edin.`;
            statusType.value = 'error';
        } else if (!gridData.value.flat().some(cell => cell.isActive)) {
             statusMessage.value = "Bulmaca verisi yüklendi ancak aktif hücre bulunamadı. İpuçlarını kontrol edin.";
             statusType.value = 'error';
        }


        currentCell.value = null;
        currentClue.value = null;
        currentDirection.value = 'across';
        // clearStatus is not called here to preserve the potential error message from initialization.

        isLoading.value = false;
        focusFirstClue();
    };

    const focusFirstClue = async () => {
        await nextTick();
        if (!gridData.value.length || isLoading.value) return;

        const firstAcross = acrossClues.value[0];
        if (firstAcross && gridData.value[firstAcross.row]?.[firstAcross.col]?.isActive) {
            selectClueStart(firstAcross); return;
        }
        const firstDown = downClues.value[0];
        if (firstDown && gridData.value[firstDown.row]?.[firstDown.col]?.isActive) {
            selectClueStart(firstDown); return;
        }
        const firstActiveCell = gridData.value.flat().find(cell => cell.isActive);
        if (firstActiveCell) {
            selectCell(firstActiveCell.row, firstActiveCell.col);
            const cellData = gridData.value[firstActiveCell.row][firstActiveCell.col];
            currentDirection.value = cellData.clueRefs.some(ref => ref.direction === 'across') ? 'across' : 'down';
            updateCurrentClue();
            focusCell(firstActiveCell.row, firstActiveCell.col);
        } else {
            currentCell.value = null; currentClue.value = null;
        }
    };

    const updateCurrentClue = () => {
        if (!currentCell.value) { currentClue.value = null; return; }
        const { row, col } = currentCell.value;
        const cell = gridData.value[row]?.[col];
        if (!cell || !cell.isActive) { currentClue.value = null; return; }

        let matchingClueRef = cell.clueRefs.find(ref => ref.direction === currentDirection.value);
        if (matchingClueRef?.clueObj) {
            currentClue.value = matchingClueRef.clueObj;
        } else {
            const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
            const fallbackClueRef = cell.clueRefs.find(ref => ref.direction === otherDirection);
            if (fallbackClueRef?.clueObj) {
                currentDirection.value = otherDirection;
                currentClue.value = fallbackClueRef.clueObj;
            } else {
                currentClue.value = null;
            }
        }
    };

    const handleCellClick = (row, col) => {
        const cell = gridData.value[row]?.[col];
        if (!cell || !cell.isActive) return;

        if (isCurrentCell(row, col)) {
            const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
            if (cell.clueRefs.some(ref => ref.direction === otherDirection)) {
                currentDirection.value = otherDirection;
                updateCurrentClue();
            }
        } else {
            const hasAcross = cell.clueRefs.some(ref => ref.direction === 'across');
            const hasDown = cell.clueRefs.some(ref => ref.direction === 'down');
            if (hasAcross && hasDown) {
                if (!cell.clueRefs.some(ref => ref.direction === currentDirection.value)) {
                    currentDirection.value = hasAcross ? 'across' : 'down';
                }
            } else {
                currentDirection.value = hasDown ? 'down' : 'across';
            }
            selectCell(row, col);
        }
        focusCell(row, col);
    };

    const selectCell = (row, col) => {
        const cell = gridData.value[row]?.[col];
        if (cell?.isActive) {
            if (cell.isError) cell.isError = false;
            // Only clear general error message if it's not an initialization error
            if (statusType.value === 'error' && !statusMessage.value.includes("Bulmaca yüklenirken sorunlar oluştu")) {
                 clearStatus(false); // Don't clear cell errors from a check
            }
            currentCell.value = { row, col };
            updateCurrentClue();
        }
    };

    const focusCell = async (row, col) => {
        await nextTick();
        const inputElement = cellRefs.value[`${row}-${col}`];
        if (inputElement) {
            inputElement.focus();
            setTimeout(() => inputElement.select(), 0);
        }
    };

    const handleFocus = (row, col) => {
        if (!isCurrentCell(row, col) || !currentClue.value) {
            selectCell(row, col);
        }
        const inputElement = cellRefs.value[`${row}-${col}`];
        if (inputElement) {
             setTimeout(() => inputElement.select(), 0);
        }
    };

    const handleInput = (event, row, col) => {
        const cell = gridData.value[row]?.[col];
        if (!cell?.isActive) return;

        const inputElement = event.target;
        const value = inputElement.value.toUpperCase();
        const allowedChars = /^[A-ZÇĞİÖŞÜ]$/;

        if (value.length > 0 && allowedChars.test(value.slice(-1))) {
            const charToInsert = value.slice(-1);
            if (cell.value !== charToInsert) {
                cell.value = charToInsert;
                if (cell.isError) cell.isError = false;
                if (statusType.value === 'error' && !statusMessage.value.includes("Bulmaca yüklenirken sorunlar oluştu")) {
                    clearStatus(false);
                }
            }
            moveToNextCell(row, col);
        } else if (value === '') {
            if (cell.value !== '') {
                cell.value = '';
                if (cell.isError) cell.isError = false;
            }
        } else {
            inputElement.value = cell.value;
        }
    };

    const handleKeyDown = (event, row, col) => {
        const key = event.key;
        const cell = gridData.value[row]?.[col];
        if (!cell?.isActive) return;

        let handled = false;
        const moveAndHandle = (dir, moveFunc) => {
            currentDirection.value = dir; moveFunc(row, col); handled = true;
        };

        if (key === 'ArrowRight') moveAndHandle('across', moveToNextCell);
        else if (key === 'ArrowLeft') moveAndHandle('across', moveToPreviousCell);
        else if (key === 'ArrowUp') moveAndHandle('down', moveToPreviousCell);
        else if (key === 'ArrowDown') moveAndHandle('down', moveToNextCell);
        else if (key === 'Enter' || key === ' ') {
            event.preventDefault();
            const otherDirection = currentDirection.value === 'across' ? 'down' : 'across';
            if (cell.clueRefs.some(ref => ref.direction === otherDirection)) {
                currentDirection.value = otherDirection;
                updateCurrentClue();
                focusCell(row, col);
            }
            handled = true;
        } else if (key === 'Backspace') {
            handled = true;
            if (cell.value) {
                cell.value = ''; if (cell.isError) cell.isError = false;
            } else {
                moveToPreviousCell(row, col);
            }
        } else if (key === 'Delete') {
            handled = true;
            if (cell.value) {
                cell.value = ''; if (cell.isError) cell.isError = false;
            }
        } else if (key === 'Tab') {
            handled = true; event.preventDefault(); moveToNextClueStart(event.shiftKey);
        } else if (key.length === 1 && !/^[a-zA-ZçÇğĞıİöÖşŞüÜ]$/.test(key) && !event.ctrlKey && !event.metaKey && !event.altKey) {
            handled = true;
        }

        if (handled) event.preventDefault();
    };

    const moveToNextCell = (row, col) => {
        if (!currentClue.value) return;
        const clue = currentClue.value; // currentClue is the clueObj
        if (!clue) return;

        let nextRow = row, nextCol = col;
        if (currentDirection.value === 'across') {
            if (col < clue.col + clue.length - 1) nextCol++; else return;
        } else {
            if (row < clue.row + clue.length - 1) nextRow++; else return;
        }
        if (gridData.value[nextRow]?.[nextCol]?.isActive) {
            selectCell(nextRow, nextCol);
            focusCell(nextRow, nextCol);
        }
    };

    const moveToPreviousCell = (row, col) => {
        if (!currentClue.value) return;
        const clue = currentClue.value; // currentClue is the clueObj
        if (!clue) return;

        let prevRow = row, prevCol = col;
        if (currentDirection.value === 'across') {
            if (col > clue.col) prevCol--; else return;
        } else {
            if (row > clue.row) prevRow--; else return;
        }
        if (gridData.value[prevRow]?.[prevCol]?.isActive) {
            selectCell(prevRow, prevCol);
            focusCell(prevRow, prevCol);
        }
    };

    const moveToNextClueStart = (isShift = false) => {
        const allClues = [...acrossClues.value, ...downClues.value].sort((a, b) => {
            if (a.number !== b.number) return a.number - b.number;
            return a.direction === 'across' ? -1 : 1;
        });
        if (!allClues.length) return;

        let currentIndex = -1;
        if (currentClue.value) {
            currentIndex = allClues.findIndex(c => c.number === currentClue.value.number && c.direction === currentClue.value.direction && c.row === currentClue.value.row && c.col === currentClue.value.col);
        }

        let nextIndex = isShift ? (currentIndex - 1 + allClues.length) % allClues.length : (currentIndex + 1) % allClues.length;
        const nextClue = allClues[nextIndex];
        if (nextClue) selectClueStart(nextClue);
    };

    const isActiveClue = (clue) => {
        return currentClue.value && currentClue.value.number === clue.number && currentClue.value.direction === clue.direction && currentClue.value.row === clue.row && currentClue.value.col === clue.col;
    };

    const selectClueStart = (clue) => {
        if (gridData.value[clue.row]?.[clue.col]?.isActive) {
            currentDirection.value = clue.direction;
            selectCell(clue.row, clue.col);
            focusCell(clue.row, clue.col);
        }
    };

    const checkPuzzle = () => {
        if (!gridData.value.length) {
            statusMessage.value = "Kontrol edilecek bulmaca yok."; statusType.value = 'info'; return;
        }
        clearStatus(false);
        let incorrectCluesCount = 0;
        let hasEmptyCellsInActiveClues = false;
        let allCellsFilledAndActive = true;
        let allAnswersCorrect = true;

        gridData.value.flat().forEach(cell => {
            if (cell.isActive) {
                cell.isError = false;
                if (!cell.value) allCellsFilledAndActive = false;
            }
        });

        (props.puzzleData?.clues || []).forEach(clue => {
            // Only check clues that were successfully processed (i.e., part of the grid logic)
            // This check might be redundant if malformed clues are already filtered out from being processed for grid active state
            if (typeof clue.row !== 'number' || typeof clue.col !== 'number' || clue.length <=0 || !clue.solution) return;

            let wordCorrect = true;
            let wordHasEmpty = false;
            for (let i = 0; i < clue.length; i++) {
                const r = clue.direction === 'down' ? clue.row + i : clue.row;
                const c = clue.direction === 'across' ? clue.col + i : clue.col;
                const cell = gridData.value[r]?.[c];

                // Ensure cell exists and is active before checking
                if (cell?.isActive) {
                    if (!cell.value) { wordHasEmpty = true; wordCorrect = false; }
                    else if (cell.value.toUpperCase() !== clue.solution[i].toUpperCase()) wordCorrect = false;
                } else {
                    // If a cell that should be part of a clue isn't active, the clue definition might be problematic
                    // or it was filtered out during initialization. Consider this an issue for correctness.
                    wordCorrect = false;
                    // console.warn(`Cell for clue ${clue.number} not active during check: (${r},${c})`);
                    break;
                }
            }
            if (wordHasEmpty) hasEmptyCellsInActiveClues = true;

            if (!wordCorrect && !wordHasEmpty) {
                incorrectCluesCount++;
                allAnswersCorrect = false;
                for (let i = 0; i < clue.length; i++) {
                    const r = clue.direction === 'down' ? clue.row + i : clue.row;
                    const c = clue.direction === 'across' ? clue.col + i : clue.col;
                    if (gridData.value[r]?.[c]?.isActive) gridData.value[r][c].isError = true; // Mark only active cells
                }
            } else if (!wordCorrect && wordHasEmpty) {
                 allAnswersCorrect = false;
            }
        });

        if (allCellsFilledAndActive && allAnswersCorrect && incorrectCluesCount === 0) {
            statusMessage.value = 'Tebrikler! Bulmacayı tamamen doğru çözdünüz!'; statusType.value = 'success';
        } else if (incorrectCluesCount > 0) {
            statusMessage.value = `Bazı cevaplar hatalı (${incorrectCluesCount} kelime). Hatalı hücreler işaretlendi.`; statusType.value = 'error';
        } else if (hasEmptyCellsInActiveClues) {
            statusMessage.value = 'Bulmaca henüz tamamlanmadı. Boş hücreler var.'; statusType.value = 'info';
        } else if (allCellsFilledAndActive && !allAnswersCorrect) {
             statusMessage.value = 'Bazı cevaplar hatalı. Hatalı hücreler işaretlendi.'; statusType.value = 'error';
        } else {
            statusMessage.value = 'Kontrol tamamlandı. Lütfen işaretli hücreleri (varsa) gözden geçirin.'; statusType.value = 'info';
        }
    };

    const revealPuzzle = () => {
        if (!gridData.value.length) return;
        clearStatus(true);
        (props.puzzleData?.clues || []).forEach(clue => {
             // Only reveal clues that were valid enough to be processed
            if (typeof clue.row !== 'number' || typeof clue.col !== 'number' || clue.length <=0 || !clue.solution) return;

            for (let i = 0; i < clue.length; i++) {
                const r = clue.direction === 'down' ? clue.row + i : clue.row;
                const c = clue.direction === 'across' ? clue.col + i : clue.col;
                if (gridData.value[r]?.[c]?.isActive) { // Check if cell is part of the rendered grid
                    gridData.value[r][c].value = clue.solution[i].toUpperCase();
                    gridData.value[r][c].isError = false;
                }
            }
        });
        statusMessage.value = 'Tüm cevaplar gösterildi.'; statusType.value = 'info';
    };

    const clearPuzzle = () => {
        if (!gridData.value.length) return;
        clearStatus(true);
        gridData.value.flat().forEach(cell => {
            if (cell.isActive) {
                cell.value = ''; cell.isError = false;
            }
        });
        statusMessage.value = 'Girişler temizlendi.'; statusType.value = 'info';
        focusFirstClue();
    };

    const clearStatus = (clearCellErrors = true) => {
        statusMessage.value = '';
        // statusType.value = ''; // Don't reset type, let new actions set it.
        if (clearCellErrors) {
            gridData.value.flat().forEach(cell => { if(cell.isError) cell.isError = false; });
        }
    };

    const isCurrentCell = (row, col) => currentCell.value && currentCell.value.row === row && currentCell.value.col === col;

    const isPartOfCurrentWord = (row, col) => {
        if (!currentClue.value || !currentCell.value) return false;
        const clue = currentClue.value;
        if (clue.direction === 'across') {
            return row === clue.row && col >= clue.col && col < clue.col + clue.length;
        } else {
            return col === clue.col && row >= clue.row && row < clue.row + clue.length;
        }
    };

    // --- Lifecycle Hooks ---
    onMounted(() => {
        initializeGrid();
    });

    watch(() => props.puzzleData, (newPuzzleData, oldPuzzleData) => {
        if (newPuzzleData && JSON.stringify(newPuzzleData) !== JSON.stringify(oldPuzzleData)) {
            // console.log("Puzzle data changed, re-initializing grid.");
            initializeGrid();
        }
    }, { deep: true });

    return {
        gridData, currentCell, cellRefs, currentDirection, currentClue, statusMessage, statusType,
        cellSize, acrossClues, downClues, puzzleTitle, initializeGrid, focusFirstClue,
        updateCurrentClue, handleCellClick, selectCell, focusCell, handleFocus, handleInput,
        handleKeyDown, moveToNextCell, moveToPreviousCell, moveToNextClueStart, isActiveClue,
        selectClueStart, checkPuzzle, revealPuzzle, clearPuzzle, clearStatus, isCurrentCell,
        isPartOfCurrentWord, isLoading
    };
}
