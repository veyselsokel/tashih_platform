// resources/js/Pages/Crossword/crosswordData.js
export const crosswordData = {
    width: 15,
    height: 15,
    cells: [], // Bu diziyi otomatik olarak oluşturacağız
    acrossClues: [
        {
            id: '1a',
            x: 1,
            y: 1,
            length: 10,
            clue: "4 Nisan 1953'te batan denizaltımız (10)",
            solution: "DUMLUPINAR"
        },
        {
            id: '2a',
            x: 1,
            y: 3,
            length: 7,
            clue: "Öğretim ve eğitim sistemi (7)",
            solution: "VILAYET"
        }
    ],
    downClues: [
        {
            id: '1d',
            x: 1,
            y: 1,
            length: 2,
            clue: "Tayin etme (2)",
            solution: "DO"
        },
        {
            id: '2d',
            x: 3,
            y: 1,
            length: 3,
            clue: "Kısa çizgi (3)",
            solution: "TRE"
        }
    ]
};

// Grid hücrelerini oluştur
for (let y = 0; y < crosswordData.height; y++) {
    const row = [];
    for (let x = 0; x < crosswordData.width; x++) {
        row.push({
            x: x,
            y: y,
            value: '',
            isActive: false,
            number: null
        });
    }
    crosswordData.cells.push(row);
}

// İpuçlarına göre aktif hücreleri işaretle
crosswordData.acrossClues.forEach(clue => {
    for (let i = 0; i < clue.length; i++) {
        crosswordData.cells[clue.y - 1][clue.x - 1 + i].isActive = true;
        if (i === 0) {
            crosswordData.cells[clue.y - 1][clue.x - 1].number = parseInt(clue.id);
        }
    }
});

crosswordData.downClues.forEach(clue => {
    for (let i = 0; i < clue.length; i++) {
        crosswordData.cells[clue.y - 1 + i][clue.x - 1].isActive = true;
        if (i === 0 && !crosswordData.cells[clue.y - 1][clue.x - 1].number) {
            crosswordData.cells[clue.y - 1][clue.x - 1].number = parseInt(clue.id);
        }
    }
});