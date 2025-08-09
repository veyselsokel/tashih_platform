// utils/imageProcessing.js

// Sabit değerler
const MAX_WIDTH = 1920;
const MAX_HEIGHT = 1080;
const QUALITY = 0.7;
const MAX_FILE_SIZE = 2 * 1024 * 1024; // 2MB

/**
 * Görsel sıkıştırma fonksiyonu
 * @param {File} file - Sıkıştırılacak görsel dosyası
 * @returns {Promise<Blob>} Sıkıştırılmış görsel
 */
export const compressImage = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;

            img.onload = () => {
                let width = img.width;
                let height = img.height;

                // En boy oranını koru
                if (width > MAX_WIDTH) {
                    height *= MAX_WIDTH / width;
                    width = MAX_WIDTH;
                }

                if (height > MAX_HEIGHT) {
                    width *= MAX_HEIGHT / height;
                    height = MAX_HEIGHT;
                }

                // Canvas oluştur
                const canvas = document.createElement('canvas');
                canvas.width = width;
                canvas.height = height;

                // Görseli canvas'a çiz
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);

                // Görseli sıkıştır
                canvas.toBlob((blob) => {
                    if (blob) {
                        if (blob.size > MAX_FILE_SIZE) {
                            // Dosya boyutu hala büyükse kaliteyi düşür
                            const newQuality = (MAX_FILE_SIZE / blob.size) * QUALITY;
                            canvas.toBlob(
                                (finalBlob) => resolve(finalBlob),
                                'image/jpeg',
                                Math.min(newQuality, QUALITY)
                            );
                        } else {
                            resolve(blob);
                        }
                    } else {
                        reject(new Error('Görsel sıkıştırılamadı'));
                    }
                }, 'image/jpeg', QUALITY);
            };

            img.onerror = () => reject(new Error('Görsel yüklenemedi'));
        };

        reader.onerror = () => reject(new Error('Dosya okunamadı'));
    });
};

/**
 * Dosya türünü kontrol et
 * @param {File} file - Kontrol edilecek dosya
 * @returns {boolean} Geçerli görsel dosyası mı
 */
export const isValidImageType = (file) => {
    const validTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
    return validTypes.includes(file.type);
};

/**
 * Dosya boyutunu kontrol et
 * @param {File} file - Kontrol edilecek dosya
 * @returns {boolean} Geçerli boyutta mı
 */
export const isValidFileSize = (file) => {
    return file.size <= MAX_FILE_SIZE;
};

/**
 * Boyut bilgisini formatla
 * @param {number} bytes - Bayt cinsinden boyut
 * @returns {string} Formatlanmış boyut
 */
export const formatFileSize = (bytes) => {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return `${parseFloat((bytes / Math.pow(k, i)).toFixed(2))} ${sizes[i]}`;
};

/**
 * Görsel dosyasının boyutlarını al
 * @param {File} file - Görsel dosyası
 * @returns {Promise<{width: number, height: number}>} Görsel boyutları
 */
export const getImageDimensions = (file) => {
    return new Promise((resolve, reject) => {
        const reader = new FileReader();
        reader.readAsDataURL(file);

        reader.onload = (event) => {
            const img = new Image();
            img.src = event.target.result;

            img.onload = () => {
                resolve({
                    width: img.width,
                    height: img.height
                });
            };

            img.onerror = () => reject(new Error('Görsel boyutları alınamadı'));
        };

        reader.onerror = () => reject(new Error('Dosya okunamadı'));
    });
};