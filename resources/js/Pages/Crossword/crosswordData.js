// resources/js/Pages/Crossword/crosswordData.js
// Bu dosya artık varsayılan veya fallback bulmaca olarak kullanılabilir.
// Ana yükleme mekanizması CSV'den olacak.

export const examplePuzzle = {
    title: "Genel Kültür Bulmacası (Varsayılan)",
    width: 13, // Grid width
    height: 12, // Grid height
    clues: [
      // --- Across Clues ---
      { number: 1, direction: 'across', row: 0, col: 7, length: 6, text: "Bir şeyi veya kimseyi küçük görme, önem vermeme.", solution: "HORLUK" },
      { number: 5, direction: 'across', row: 2, col: 0, length: 6, text: "Türkiye'nin başkenti.", solution: "ANKARA" }, // Length düzeltildi
      { number: 7, direction: 'across', row: 2, col: 8, length: 3, text: "Bir iş için gerekli olan para, mali kaynak.", solution: "FON" }, // Length düzeltildi
      { number: 8, direction: 'across', row: 4, col: 1, length: 4, text: "Yılın on ikinci ayı.", solution: "ARAL" },
      { number: 9, direction: 'across', row: 4, col: 6, length: 7, text: "Bir konuyu aydınlatmak için verilen bilgi.", solution: "IZAHAT" },
      { number: 11, direction: 'across', row: 6, col: 0, length: 5, text: "Yemek pişirilen yer.", solution: "MUTFA" },
      { number: 13, direction: 'across', row: 6, col: 6, length: 5, text: "Bir tür değerli taş.", solution: "ELMAS" },
      { number: 15, direction: 'across', row: 8, col: 0, length: 5, text: "Bir müzik aleti.", solution: "GITAR" }, // Length düzeltildi
      { number: 16, direction: 'across', row: 8, col: 7, length: 6, text: "Sıvıların akmasını sağlayan araç.", solution: "MUSLUK" },
      { number: 17, direction: 'across', row: 10, col: 1, length: 5, text: "Güneş sistemindeki bir gezegen.", solution: "VENUS" },
      { number: 18, direction: 'across', row: 11, col: 6, length: 5, text: "Yazı yazmaya yarayan araç.", solution: "KALEM" },
      // --- Down Clues ---
      { number: 2, direction: 'down', row: 0, col: 9, length: 4, text: "Halk ozanı.", solution: "OZAN" }, // Length düzeltildi
      { number: 3, direction: 'down', row: 0, col: 11, length: 7, text: "Bir şeyi yapma veya kullanma izni.", solution: "RUHSAT" },
      { number: 4, direction: 'down', row: 1, col: 7, length: 6, text: "Bir sıvıyı içmek için kullanılan araç.", solution: "BARDAK" },
      { number: 6, direction: 'down', row: 2, col: 4, length: 5, text: "Bir olayın veya durumun nedeni.", solution: "SEBEP" }, // Length düzeltildi
      { number: 10, direction: 'down', row: 4, col: 11, length: 7, text: "Bir işi yapmak için gösterilen çaba.", solution: "GAYRET" },
      { number: 12, direction: 'down', row: 6, col: 2, length: 3, text: "Bir tür deniz hayvanı.", solution: "FOK" }, // Length düzeltildi
      { number: 14, direction: 'down', row: 6, col: 8, length: 5, text: "Bir ülkenin yönetim biçimi.", solution: "REJIM" }, // Length düzeltildi
    ]
  };

  // --- How to create your own puzzle file ---
  // (Instructions remain the same for manual creation if needed)
