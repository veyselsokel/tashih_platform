<template>
    <section class="bg-white py-12">
        <div class="container mx-auto px-4 max-w-5xl">
            <!-- Main Title -->
            <h1 class="text-4xl font-bold text-center mb-12 text-gray-800">
                TASHİH HAKKINDA...
            </h1>

            <!-- Introduction Card -->
            <div class="bg-white rounded-lg shadow-lg p-6 mb-8">
                <div class="flex flex-col md:flex-row items-center gap-8">
                    <img src="../../../assets/kalem.png" alt="Tashih İllüstrasyonu"
                        class="w-48 h-48 object-cover rounded-lg shadow-md" />
                    <div>
                        <h2 class="text-2xl font-semibold mb-4 text-gray-800">Tashih nedir?</h2>
                        <p class="text-gray-600 text-lg leading-relaxed">
                            Tashih, düzelti anlamına gelmektedir. Tashih, özellikle yazın dünyasının (gazete, dergi vb.)
                            çokça kullandığı bir terimdir. Tashih için "yazım dünyasının kalite kontrol yöntemi" de
                            denilebilir.
                        </p>
                    </div>
                </div>
            </div>

            <!-- Accordion Sections -->
            <div class="space-y-4">
                <div v-for="(section, index) in sections" :key="index" class="border rounded-lg">
                    <button
                        class="w-full px-6 py-4 text-left bg-gray-50 hover:bg-gray-100 transition-colors duration-200 flex justify-between items-center rounded-lg"
                        @click="toggleSection(index)">
                        <h3 class="text-xl font-semibold text-gray-800">{{ section.title }}</h3>
                        <svg class="w-6 h-6 transform transition-transform duration-500"
                            :class="{ 'rotate-180': section.isOpen }" fill="none" stroke="currentColor"
                            viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7" />
                        </svg>
                    </button>
                    <transition enter-active-class="transition-all duration-500 ease-in-out"
                        leave-active-class="transition-all duration-500 ease-in-out"
                        enter-from-class="max-h-0 opacity-0" enter-to-class="max-h-[1000px] opacity-100"
                        leave-from-class="max-h-[1000px] opacity-100" leave-to-class="max-h-0 opacity-0">
                        <div v-show="section.isOpen" class="overflow-hidden">
                            <div class="px-6 py-4 bg-white rounded-b-lg">
                                <div class="prose max-w-none text-gray-600">
                                    <!-- Introduction text if exists -->
                                    <p v-if="section.intro" class="mb-6 leading-relaxed text-lg">
                                        {{ section.intro }}
                                    </p>

                                    <!-- Numbered steps if they exist -->
                                    <div v-if="section.steps" class="space-y-6">
                                        <div v-for="(step, stepIndex) in section.steps" :key="stepIndex"
                                            class="flex gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="flex items-center justify-center w-8 h-8 bg-blue-500 text-white rounded-full font-semibold">
                                                    {{ stepIndex + 1 }}
                                                </span>
                                            </div>
                                            <div class="flex-grow">
                                                <p class="text-gray-700 leading-relaxed">{{ step }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Bullet points if they exist -->
                                    <div v-if="section.bullets" class="space-y-6">
                                        <div v-for="(bullet, bulletIndex) in section.bullets" :key="bulletIndex"
                                            class="flex gap-4 p-4 bg-gray-50 rounded-lg hover:bg-gray-100 transition-colors duration-200">
                                            <div class="flex-shrink-0">
                                                <span
                                                    class="flex items-center justify-center w-3 h-3 bg-blue-500 rounded-full mt-2"></span>
                                            </div>
                                            <div class="flex-grow">
                                                <p class="text-gray-700 leading-relaxed">{{ bullet }}</p>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Additional content if exists -->
                                    <div v-if="section.additionalContent" class="mt-6 p-4 bg-blue-50 rounded-lg">
                                        <p class="text-gray-700 leading-relaxed">{{ section.additionalContent }}</p>
                                    </div>
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
        title: 'Tashih neden önemlidir?',
        isOpen: false,
        intro:
            'Tashih, bir yazının veya belgenin kalitesini, okunaklı olmasını ve anlaşılmasını sağlamak için çok önemlidir. Tashihi yapılmış bir metin veya belge daha anlaşılır, akıcı ve ikna edici olur. Böylece yazıdan veya belgeden beklenen sonucu almamızı kolaylaştırır. Ayrıca tashihi yapılmış bir metin veya belge, okuyucunun dikkatinin dağılmasını önlediği ve çok daha kolay anlaşıldığı için sonuna kadar okunmayı da sağlar.'
    },
    {
        title: 'Tashih nasıl yapılır?',
        isOpen: false,
        intro: 'Tashih için öncelikle belli bir bilgi seviyesine sahip olmak şarttır. Sonrasında en az onun kadar önemli olmak üzere fark etme ve yanlışları yakalayabilme dikkatine de sahip olmayı gerektirir. Tashih, sistematik bir şekilde yapılması gereken bir süreçtir. Tashih yaparken genellikle şu adımlar izlenir:',
        steps: [
            'Metin veya belgeyi okurken anlam bütünlüğünden kopmamak ve bunu sağlamak çok önemlidir. Yazının içeriğine tam hâkim olmak, tashih yapmanın ilk şartıdır. Tam olarak anlaşılmayan bir yazı düzeltilemez; hatta yapılan müdahalelerle tam tersi anlamlara kayan vahim durumlara bile sebep olunabilir.',
            'Yazım hatalarından metni arındırmak, tashihin en önemli aşamasını oluşturur. Yazım hataları, metnin veya belgenin kalitesini düşürür ve okuyucuların dikkatini dağıtır.',
            'Tashihin en önemli aşamalarından biri de metni dil bilgisi hatalarından arındırmaktır. Dil bilgisi hataları, metnin veya belgenin anlaşılmasını zorlaştırır ve okuyucuların güvenini sarsar. Bütün bunlar da okuyucunun yazıdan kopmasına sebep olur.',
            'Metnin veya belgenin akıcılığını ve ikna ediciliğini etkileyen unsurlardan biri de üsluptur. Üslup hataları, metnin veya belgenin anlaşılmasını zorlaştırır ve okuyucuların ilgisini kaybetmesine neden olur.',
            'Tashih yaparken yazım hataları, dil bilgisi hataları ve üslup hatalarının yanı sıra diğer hataların da düzeltmesi gerekir. Bu hatalar arasında, noktalama işaretleri hataları, biçimlendirme hataları ve içerik hataları yer alır. Ayrıca bilgi yanlışları da çok önemlidir.'
        ]
    },
    {
        title: 'Tashih yaparken nelere dikkat edilmeli?',
        isOpen: false,
        bullets: [
            'Dikkatli ve sistematik olunmalıdır. Tashih, çok dikkatli ve sistematik şekilde yapılması gereken bir süreçtir. Acele edilmemeli, her kelime ve cümle dikkatlice kontrol edilmelidir.',
            'Tashih yaparken sadece yazım veya dil bilgisi hatalarına odaklanılmamalı. Dil bilgisi ve yazımın yanı sıra bilgi, üslup, akış ve bütünlük de aynı zamanda metin boyunca takip edilmelidir.',
            'Metnin veya belgenin anlamı korunmalıdır. Tashih yaparken metnin veya belge sahibinin üslubuna müdahaleden her zaman kaçınılmalıdır. Metnin veya belgenin sahibi olmadığımızın bilinciyle hareket ederek, gereken durumlarda iletişime geçilerek teyit sağlanmalıdır. Yani hatalar düzeltilirken metnin veya belgenin anlamı değiştirilmemelidir.'
        ],
        additionalContent: 'Dil, yaşayan ve gelişen canlı bir varlıktır. Türk Dil Kurumu da bazı kelimelerin yazımını zamanla değiştirebilmektedir. Mesela; zücaciye şeklinde yazılan kelime bugün züccaciye veya unvan olarak kullanılan sözcük ünvan şeklinde değiştirilmiştir. Böyle birçok kelime mevcuttur. TDK\'nın güncel kelime bilgisine hâkim olarak metinde veya belgede kelimelerin kullanımında çelişki olmasından kaçınılmalıdır.'
    }
]);

const toggleSection = (index) => {
    sections.value[index].isOpen = !sections.value[index].isOpen
}
</script>