<template>
    <section class="bg-stone-50 py-24">
        <div class="container mx-auto px-4 max-w-5xl">
            <!-- Main Title -->
            <h2 class="text-3xl font-extrabold text-center mb-16 text-gray-800">
                Profesyonel Tashih Süreci ve İlkeleri
            </h2>

            <!-- Accordion Sections -->
            <div class="space-y-4">
                <div v-for="(section, index) in sections" :key="index" class="border border-gray-200 rounded-lg bg-white shadow-sm">
                    <button
                        class="w-full px-6 py-5 text-left transition-colors duration-200 flex justify-between items-center rounded-lg"
                        @click="toggleSection(index)">
                        <h3 class="text-xl font-semibold text-gray-800">{{ section.title }}</h3>
                        <svg class="w-6 h-6 transform transition-transform duration-500 text-orange-500"
                            :class="{ 'rotate-180': section.isOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <transition enter-active-class="transition-all duration-500 ease-in-out"
                        leave-active-class="transition-all duration-300 ease-in-out"
                        enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-[1000px] opacity-100"
                        leave-from-class="max-h-[1000px] opacity-100" leave-to-class="max-h-0 opacity-0">
                        <div v-show="section.isOpen" class="overflow-hidden">
                            <div class="px-6 pb-6 pt-2 bg-white rounded-b-lg">
                                <div class="prose max-w-none text-gray-600">
                                    <!-- Introduction text if exists -->
                                    <p v-if="section.intro" class="mb-6 leading-relaxed text-lg">
                                        {{ section.intro }}
                                    </p>

                                    <!-- Numbered steps if they exist -->
                                    <div v-if="section.steps" class="space-y-6">
                                        <div v-for="(step, stepIndex) in section.steps" :key="stepIndex"
                                            class="flex gap-4 p-4 bg-stone-50 rounded-lg">
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="flex items-center justify-center w-8 h-8 bg-orange-500 text-white rounded-full font-semibold">
                                                    {{ stepIndex + 1 }}
                                                </span>
                                            </div>
                                            <div class="flex-grow">
                                                <p class="text-gray-700 leading-relaxed">{{ step }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bullet points if they exist -->
                                    <ul v-if="section.bullets" class="space-y-4 list-inside mt-4">
                                        <li v-for="(bullet, bulletIndex) in section.bullets" :key="bulletIndex" class="flex items-start">
                                            <span class="text-orange-500 mr-2 mt-1">&#10003;</span>
                                            <span class="text-gray-700 leading-relaxed">{{ bullet }}</span>
                                        </li>
                                    </ul>

                                    <!-- Additional content if exists -->
                                    <blockquote v-if="section.quote" class="mt-6 p-4 bg-orange-50 border-l-4 border-orange-400 text-orange-800 italic">
                                        <p class="leading-relaxed">{{ section.quote }}</p>
                                    </blockquote>
                                </div>
                            </div>
                        </div>
                    </transition>
                </div>
            </div>
        </div>
    </section>
</template>

<script setup>
import { ref } from 'vue'

const sections = ref([
    {
        title: 'Profesyonel Tashih Süreci: Adım Adım Kalite',
        isOpen: true,
        intro: 'Etkili bir tashih, belirli bir disiplin ve metodoloji gerektirir. Sadece hataları bulmak değil, metnin ruhunu koruyarak onu en iyi versiyonuna ulaştırmak hedeflenir. İşte profesyonel tashih sürecinin temel adımları:',
        steps: [
            'İçeriği Anlama ve Bütünlüğü Koruma: Tashihin ilk ve en kritik şartı, metnin içeriğine tam olarak hâkim olmaktır. Metnin ana fikrinden kopmadan, anlam bütünlüğünü koruyarak düzeltmeler yapılır.',
            'Yazım ve Noktalama Hatalarını Giderme: Metnin kalitesini en çok düşüren unsurlar olan yazım (imla) ve noktalama hataları titizlikle tespit edilir ve düzeltilir.',
            'Dil Bilgisi Kontrolü: Cümle düşüklükleri, yanlış fiil çekimleri, eklerin hatalı kullanımı gibi dil bilgisi sorunları giderilerek metnin dilbilgisel doğruluğu sağlanır.',
            'Üslup ve Akıcılık Optimizasyonu: Metnin akıcılığını bozan tekrarlar, anlatımı zorlaştıran ifadeler ve üslup hataları düzenlenir. Bu aşamada yazarın özgün üslubuna müdahale etmekten kaçınılmaz.',
            'Bilgi ve Tutarlılık Kontrolü: Metin içindeki bilgilerin (tarih, isim, terim vb.) tutarlılığı ve doğruluğu kontrol edilir.'
        ]
    },
    {
        title: 'Tashih Yapılırken Nelere Dikkat Edilmelidir?',
        isOpen: false,
        bullets: [
            'Sistematik Yaklaşım: Aceleye getirilmeden, her kelime ve cümle dikkatlice incelenmelidir.',
            'Yazarın Üslubuna Saygı: Amaç, yazarın kimliğini silmek değil, metnini güçlendirmektir. Gerekli durumlarda yazardan teyit alınarak ilerlenir.',
            "Güncel TDK Kuralları: Dil yaşayan bir varlıktır. Türk Dil Kurumu'nun (TDK) güncel yazım ve imla kuralları takip edilerek en doğru kullanım sağlanır. Örneğin, \"unvan\" kelimesinin \"ünvan\" olarak değişmesi gibi güncellemeler yakından izlenir."
        ],
        quote: 'Tashih, yazının terziliğidir. Harfleri yerine oturtur, cümleleri yeniden diker. Çünkü bir metin, ancak hatalarından arındığında kendine yakışanı giyer.'
    }
]);

const toggleSection = (index) => {
    sections.value[index].isOpen = !sections.value[index].isOpen
}
</script>