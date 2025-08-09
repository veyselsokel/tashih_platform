<template>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 bg-white border-b border-gray-200">
                    <h1 class="text-2xl font-semibold text-gray-800 mb-6">Fiyat Takip Uygulaması</h1>

                    <!-- Ürün Ekleme Formu -->
                    <div class="mb-8 bg-gray-50 p-6 rounded-lg shadow-inner">
                        <h2 class="text-xl font-medium text-gray-700 mb-4">Yeni Ürün Ekle</h2>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="url">
                                Ürün URL
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="url" type="text" v-model="newProduct.url"
                                placeholder="https://www.zara.com/tr/tr/...">
                        </div>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">
                                Mağaza
                            </label>
                            <div class="mt-2">
                                <label class="inline-flex items-center mr-4">
                                    <input type="radio" class="form-radio" v-model="newProduct.store" value="zara">
                                    <span class="ml-2">Zara</span>
                                </label>
                                <label class="inline-flex items-center">
                                    <input type="radio" class="form-radio" v-model="newProduct.store" value="pull&bear">
                                    <span class="ml-2">Pull&Bear</span>
                                </label>
                            </div>
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="target_price">
                                Hedef Fiyat (TL)
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="target_price" type="number" v-model="newProduct.targetPrice" placeholder="499.90">
                        </div>

                        <div class="flex items-center justify-end">
                            <button
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                type="button" @click="addProduct">
                                Ürün Ekle
                            </button>
                        </div>
                    </div>

                    <!-- Takip Edilen Ürünler Listesi -->
                    <div>
                        <h2 class="text-xl font-medium text-gray-700 mb-4">Takip Edilen Ürünler</h2>

                        <div class="overflow-x-auto">
                            <table class="min-w-full bg-white">
                                <thead>
                                    <tr>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Ürün
                                        </th>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Mağaza
                                        </th>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Hedef Fiyat
                                        </th>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Son Fiyat
                                        </th>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Son Kontrol
                                        </th>
                                        <th
                                            class="py-3 px-4 border-b border-gray-200 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            İşlemler
                                        </th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr v-for="(product, index) in products" :key="index" class="hover:bg-gray-50">
                                        <td class="py-4 px-4 border-b border-gray-200">
                                            <div class="flex flex-col">
                                                <a :href="product.url" target="_blank"
                                                    class="text-blue-600 hover:text-blue-900 truncate max-w-xs">
                                                    {{ getProductName(product.url) }}
                                                </a>
                                                <span class="text-xs text-gray-500 truncate max-w-xs">{{ product.url
                                                    }}</span>
                                            </div>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                            {{ product.store }}
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                            {{ product.targetPrice }} TL
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                            <span v-if="product.lastPrice"
                                                :class="{ 'text-green-600': product.lastPrice <= product.targetPrice, 'text-red-600': product.lastPrice > product.targetPrice }">
                                                {{ product.lastPrice }} TL
                                            </span>
                                            <span v-else class="text-gray-500">Henüz kontrol edilmedi</span>
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                            {{ product.lastCheck ? formatDate(product.lastCheck) :
                                                'Henüz kontrol edilmedi' }}
                                        </td>
                                        <td class="py-4 px-4 border-b border-gray-200 text-sm">
                                            <button @click="removeProduct(index)"
                                                class="text-red-600 hover:text-red-900 mr-2">
                                                Sil
                                            </button>
                                        </td>
                                    </tr>
                                    <tr v-if="products.length === 0">
                                        <td colspan="6"
                                            class="py-4 px-4 border-b border-gray-200 text-gray-500 text-center">
                                            Henüz takip edilen ürün bulunmuyor.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Email Ayarları Formu -->
                    <div class="mt-8 bg-gray-50 p-6 rounded-lg shadow-inner">
                        <h2 class="text-xl font-medium text-gray-700 mb-4">Email Ayarları</h2>

                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="email">
                                Email Adresi
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="email" type="email" v-model="emailSettings.email" placeholder="ornek@gmail.com">
                        </div>

                        <div class="mb-6">
                            <label class="block text-gray-700 text-sm font-bold mb-2" for="app_password">
                                Gmail App Password
                            </label>
                            <input
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                                id="app_password" type="password" v-model="emailSettings.appPassword"
                                placeholder="xxxx xxxx xxxx xxxx">
                            <p class="text-xs italic mt-1">Bu uygulama, fiyat bildirimleri için Google hesabınızın App
                                Password'ünü gerektirir.</p>
                        </div>

                        <div class="flex items-center justify-end">
                            <button
                                class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline"
                                type="button" @click="saveEmailSettings">
                                Ayarları Kaydet
                            </button>
                        </div>
                    </div>

                    <!-- Durum ve Loglar -->
                    <div v-if="isMonitoring" class="mt-8 bg-gray-50 p-6 rounded-lg shadow-inner">
                        <h2 class="text-xl font-medium text-gray-700 mb-4">Takip Durumu</h2>
                        <div class="flex items-center mb-4">
                            <div class="w-3 h-3 bg-green-500 rounded-full mr-2 animate-pulse"></div>
                            <span class="text-green-600 font-medium">Fiyat takibi aktif</span>
                        </div>

                        <div v-if="logs.length > 0" class="mt-4">
                            <h3 class="text-md font-medium text-gray-700 mb-2">Son Log Kayıtları</h3>
                            <div class="bg-gray-800 text-gray-100 p-4 rounded font-mono text-sm overflow-auto max-h-60">
                                <div v-for="(log, index) in logs" :key="index" class="mb-1">{{ log }}</div>
                            </div>
                        </div>
                    </div>

                    <!-- Takip Başlat / Durdur -->
                    <div class="mt-8 flex justify-center">
                        <button v-if="!isMonitoring" @click="startMonitoring"
                            class="bg-green-500 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline flex items-center"
                            :disabled="loading">
                            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            Fiyat Takibini Başlat
                        </button>
                        <button v-else @click="stopMonitoring"
                            class="bg-red-500 hover:bg-red-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:shadow-outline flex items-center"
                            :disabled="loading">
                            <svg v-if="loading" class="animate-spin -ml-1 mr-3 h-5 w-5 text-white"
                                xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor"
                                    stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor"
                                    d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z">
                                </path>
                            </svg>
                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-2" fill="none"
                                viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z" />
                            </svg>
                            Fiyat Takibini Durdur
                        </button>
                    </div>

                    <!-- Bildirimler -->
                    <div v-if="notification.show" class="fixed bottom-5 right-5">
                        <div
                            :class="`px-6 py-4 rounded-lg shadow-lg max-w-sm ${notification.type === 'success' ? 'bg-green-100 border-l-4 border-green-500 text-green-700' : notification.type === 'error' ? 'bg-red-100 border-l-4 border-red-500 text-red-700' : 'bg-blue-100 border-l-4 border-blue-500 text-blue-700'}`">
                            <div class="flex items-center">
                                <div v-if="notification.type === 'success'"
                                    class="text-green-500 rounded-full bg-green-100 mr-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M9 12L11 14L15 10M12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12C22 17.5228 17.5228 22 12 22Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div v-else-if="notification.type === 'error'"
                                    class="text-red-500 rounded-full bg-red-100 mr-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M12 8V12M12 16H12.01M22 12C22 17.5228 17.5228 22 12 22C6.47715 22 2 17.5228 2 12C2 6.47715 6.47715 2 12 2C17.5228 2 22 6.47715 22 12Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div v-else class="text-blue-500 rounded-full bg-blue-100 mr-3">
                                    <svg width="24" height="24" viewBox="0 0 24 24" fill="none"
                                        xmlns="http://www.w3.org/2000/svg">
                                        <path
                                            d="M13 16H12V12H11M12 8H12.01M21 12C21 16.9706 16.9706 21 12 21C7.02944 21 3 16.9706 3 12C3 7.02944 7.02944 3 12 3C16.9706 3 21 7.02944 21 12Z"
                                            stroke="currentColor" stroke-width="2" stroke-linecap="round"
                                            stroke-linejoin="round" />
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-bold">{{ notification.title }}</p>
                                    <p>{{ notification.message }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>

<script>
import axios from 'axios';

export default {
    data() {
        return {
            newProduct: {
                url: '',
                store: 'zara',
                targetPrice: null
            },
            products: [],
            emailSettings: {
                email: '',
                appPassword: ''
            },
            isMonitoring: false,
            loading: false,
            statusCheckInterval: null,
            logs: [],
            notification: {
                show: false,
                type: 'info',
                title: '',
                message: '',
                timeout: null
            }
        }
    },
    mounted() {
        // localStorage'dan verileri yükle
        this.loadFromLocalStorage();
        // Takip durumunu kontrol et
        this.checkMonitoringStatus();
    },
    beforeUnmount() {
        if (this.statusCheckInterval) {
            clearInterval(this.statusCheckInterval);
        }
    },
    methods: {
        addProduct() {
            if (!this.newProduct.url || !this.newProduct.targetPrice) {
                this.showNotification('error', 'Eksik Bilgi', 'Lütfen URL ve hedef fiyat giriniz.');
                return;
            }

            this.products.push({
                url: this.newProduct.url,
                store: this.newProduct.store,
                targetPrice: parseFloat(this.newProduct.targetPrice),
                lastPrice: null,
                lastCheck: null
            });

            // Formu sıfırla
            this.newProduct = {
                url: '',
                store: 'zara',
                targetPrice: null
            };

            // localStorage'a kaydet
            this.saveToLocalStorage();
            this.showNotification('success', 'Ürün Eklendi', 'Yeni ürün takip listesine eklendi.');
        },
        removeProduct(index) {
            if (confirm('Bu ürünü takipten çıkarmak istediğinize emin misiniz?')) {
                this.products.splice(index, 1);
                this.saveToLocalStorage();
                this.showNotification('info', 'Ürün Silindi', 'Ürün takip listesinden çıkarıldı.');
            }
        },
        saveEmailSettings() {
            if (!this.emailSettings.email) {
                this.showNotification('error', 'Eksik Bilgi', 'Lütfen bir email adresi giriniz.');
                return;
            }

            if (!this.emailSettings.appPassword) {
                this.showNotification('error', 'Eksik Bilgi', 'Lütfen App Password giriniz.');
                return;
            }

            this.saveToLocalStorage();
            this.showNotification('success', 'Ayarlar Kaydedildi', 'Email ayarları başarıyla kaydedildi.');
        },
        async startMonitoring() {
            if (this.products.length === 0) {
                this.showNotification('error', 'Ürün Yok', 'Takip edilecek ürün bulunmuyor. Lütfen önce ürün ekleyin.');
                return;
            }

            if (!this.emailSettings.email || !this.emailSettings.appPassword) {
                this.showNotification('error', 'Eksik Ayarlar', 'Lütfen önce email ayarlarınızı tamamlayın.');
                return;
            }

            this.loading = true;

            try {
                const response = await axios.post('/fiyat-takip/start', {
                    products: this.products,
                    email: this.emailSettings.email,
                    appPassword: this.emailSettings.appPassword
                });

                if (response.data.status === 'success') {
                    this.isMonitoring = true;
                    this.showNotification('success', 'Takip Başlatıldı', 'Fiyat takibi başarıyla başlatıldı.');
                    this.startStatusChecking();
                } else {
                    this.showNotification('error', 'Hata', response.data.message || 'Fiyat takibi başlatılamadı.');
                }
            } catch (error) {
                console.error('Fiyat takibi başlatma hatası:', error);
                this.showNotification('error', 'Hata', error.response?.data?.message || 'Bir hata oluştu. Lütfen tekrar deneyin.');
            } finally {
                this.loading = false;
            }
        },
        async stopMonitoring() {
            this.loading = true;

            try {
                const response = await axios.post('/fiyat-takip/stop');

                if (response.data.status === 'success') {
                    this.isMonitoring = false;
                    this.showNotification('info', 'Takip Durduruldu', 'Fiyat takibi durduruldu.');
                    if (this.statusCheckInterval) {
                        clearInterval(this.statusCheckInterval);
                        this.statusCheckInterval = null;
                    }
                } else {
                    this.showNotification('error', 'Hata', response.data.message || 'Fiyat takibi durdurulamadı.');
                }
            } catch (error) {
                console.error('Fiyat takibi durdurma hatası:', error);
                this.showNotification('error', 'Hata', error.response?.data?.message || 'Bir hata oluştu. Lütfen tekrar deneyin.');
            } finally {
                this.loading = false;
            }
        },
        async checkMonitoringStatus() {
            try {
                const response = await axios.get('/fiyat-takip/status');
                this.isMonitoring = response.data.isRunning;

                if (response.data.lastLogs) {
                    this.logs = response.data.lastLogs.filter(log => log.trim() !== '');
                }

                if (this.isMonitoring && !this.statusCheckInterval) {
                    this.startStatusChecking();
                }
            } catch (error) {
                console.error('Durum kontrolü hatası:', error);
            }
        },
        startStatusChecking() {
            this.statusCheckInterval = setInterval(async () => {
                await this.checkMonitoringStatus();
            }, 10000); // Her 10 saniyede bir durum kontrolü
        },
        showNotification(type, title, message) {
            // Önceki zamanlayıcıyı temizle
            if (this.notification.timeout) {
                clearTimeout(this.notification.timeout);
            }

            // Bildirimi göster
            this.notification = {
                show: true,
                type: type,
                title: title,
                message: message,
                timeout: setTimeout(() => {
                    this.notification.show = false;
                }, 5000) // 5 saniye sonra gizle
            };
        },
        saveToLocalStorage() {
            localStorage.setItem('fiyatTakipProducts', JSON.stringify(this.products));
            localStorage.setItem('fiyatTakipEmail', JSON.stringify(this.emailSettings));
        },
        loadFromLocalStorage() {
            const products = localStorage.getItem('fiyatTakipProducts');
            const email = localStorage.getItem('fiyatTakipEmail');

            if (products) {
                this.products = JSON.parse(products);
            }

            if (email) {
                this.emailSettings = JSON.parse(email);
            }
        },
        getProductName(url) {
            // URL'den ürün adını çıkarma (basit bir yaklaşım)
            try {
                const urlObj = new URL(url);
                const pathParts = urlObj.pathname.split('/');
                // Son parçayı al ve '-' ile ayrılmış kelimeleri bul
                const lastPart = pathParts[pathParts.length - 1];
                const productName = lastPart.split('-').join(' ').replace('.html', '');
                return productName.charAt(0).toUpperCase() + productName.slice(1);
            } catch (e) {
                return 'Ürün';
            }
        },
        formatDate(dateStr) {
            const date = new Date(dateStr);
            return date.toLocaleString('tr-TR');
        }
    }
}
</script>