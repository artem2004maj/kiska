<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue'; // ← Добавлен computed

const props = defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
    laravelVersion: {
        type: String,
        required: true,
    },
    phpVersion: {
        type: String,
        required: true,
    },
    doctors: {
        type: Array,
        default: () => []
    },
    services: {
        type: Array,
        default: () => []
    },
    testimonials: {
        type: Array,
        default: () => []
    }
});

// Для слайдера отзывов
const currentTestimonialIndex = ref(0);

const nextTestimonial = () => {
    currentTestimonialIndex.value = (currentTestimonialIndex.value + 1) % props.testimonials.length;
};

const prevTestimonial = () => {
    currentTestimonialIndex.value = (currentTestimonialIndex.value - 1 + props.testimonials.length) % props.testimonials.length;
};

// Автоматическая смена отзывов каждые 5 секунд
onMounted(() => {
    const interval = setInterval(() => {
        if (props.testimonials.length > 1) {
            nextTestimonial();
        }
    }, 5000);
    
    return () => clearInterval(interval);
});

// Группировка услуг по категориям
const servicesByCategory = computed(() => {
    const grouped = {};
    props.services.forEach(service => {
        if (!grouped[service.service_category]) {
            grouped[service.service_category] = [];
        }
        grouped[service.service_category].push(service);
    });
    return grouped;
});
</script>

<template>
    <Head title="Салон красоты 'Елена'" />
    
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
        <!-- Навигация -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Логотип -->
                    <div class="flex items-center">
                        <svg class="h-10 w-auto text-[#14b8a6]" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                            <path d="M61.8548 14.6253..." fill="currentColor"/>
                        </svg>
                        <span class="ml-2 text-xl font-semibold text-gray-800">Елена</span>
                    </div>
                    
                    <!-- Меню для десктопа -->
                    <div class="hidden md:flex space-x-8">
                        <a href="#about" class="text-gray-600 hover:text-[#14b8a6] transition">О салоне</a>
                        <a href="#doctors" class="text-gray-600 hover:text-[#14b8a6] transition">Врачи</a>
                        <a href="#prices" class="text-gray-600 hover:text-[#14b8a6] transition">Цены</a>
                        <a href="#testimonials" class="text-gray-600 hover:text-[#14b8a6] transition">Отзывы</a>
                    </div>
                    
                    <!-- Кнопки авторизации -->
                    <div class="flex items-center space-x-4">
                        <Link
                            v-if="canLogin"
                            :href="route('login')"
                            class="px-4 py-2 text-sm font-medium text-[#14b8a6] border border-[#14b8a6] rounded-md hover:bg-[#14b8a6] hover:text-white transition"
                        >
                            Войти
                        </Link>
                        
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="px-4 py-2 text-sm font-medium bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition"
                        >
                            Регистрация
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero секция -->
        <section class="relative bg-gradient-to-r from-[#14b8a6] to-[#0d9488] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-24">
                <div class="text-center">
                    <h1 class="text-4xl md:text-5xl font-bold mb-6">Добро пожаловать в салон красоты "Елена"</h1>
                    <p class="text-xl mb-8 opacity-90">Профессиональный уход и косметологические услуги</p>
                    <Link
                        :href="route('register')"
                        class="inline-block px-8 py-3 bg-white text-[#14b8a6] font-semibold rounded-md hover:bg-gray-100 transition text-lg"
                    >
                        Записаться на прием
                    </Link>
                </div>
            </div>
            <!-- Декоративная волна -->
            <div class="absolute bottom-0 left-0 right-0">
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 120" class="w-full h-auto">
                    <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
                </svg>
            </div>
        </section>

        <!-- О салоне -->
        <section id="about" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">О салоне</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-12 items-center">
                    <div>
                        <img 
                            src="https://images.unsplash.com/photo-1560066984-138dadb4c035?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80" 
                            alt="Интерьер салона"
                            class="rounded-lg shadow-xl"
                        />
                    </div>
                    <div class="space-y-6">
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Салон красоты "Елена" - это пространство, где профессионализм встречается с комфортом. 
                            Мы предлагаем широкий спектр косметологических услуг с использованием современных технологий 
                            и качественных материалов.
                        </p>
                        <p class="text-lg text-gray-600 leading-relaxed">
                            Наша команда состоит из опытных специалистов, которые постоянно повышают квалификацию 
                            и следят за последними тенденциями в индустрии красоты.
                        </p>
                        <div class="grid grid-cols-3 gap-4 pt-4">
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#14b8a6]">10+</div>
                                <div class="text-sm text-gray-500">лет опыта</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#14b8a6]">5000+</div>
                                <div class="text-sm text-gray-500">довольных клиентов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-3xl font-bold text-[#14b8a6]">20+</div>
                                <div class="text-sm text-gray-500">услуг</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Наши врачи -->
        <section id="doctors" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Наши специалисты</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                    <p class="mt-4 text-lg text-gray-600">Профессионалы своего дела</p>
                </div>
                
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                    <div v-for="doctor in doctors" :key="doctor.employee_id" 
                         class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="h-64 bg-gradient-to-br from-[#14b8a6]/20 to-[#0d9488]/20 flex items-center justify-center">
                            <svg class="w-24 h-24 text-[#14b8a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="p-6">
                            <h3 class="text-xl font-semibold text-gray-900 mb-1">{{ doctor.employee_name }}</h3>
                            <p class="text-[#14b8a6] font-medium mb-3">{{ doctor.role }}</p>
                            <div class="flex items-center text-gray-600 text-sm">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Пн-Пт 10:00 - 20:00</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-sm mt-2">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Сб 11:00 - 18:00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="doctors.length === 0" class="text-center py-12 text-gray-500">
                    Информация о врачах скоро появится
                </div>
            </div>
        </section>

        <!-- Прейскурант -->
        <section id="prices" class="py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Прейскурант</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                    <p class="mt-4 text-lg text-gray-600">Доступные цены на все услуги</p>
                </div>
                
                <div v-if="Object.keys(servicesByCategory).length > 0" class="space-y-8">
                    <div v-for="(services, category) in servicesByCategory" :key="category" 
                         class="bg-gray-50 rounded-xl p-6">
                        <h3 class="text-2xl font-semibold text-gray-800 mb-4">{{ category }}</h3>
                        <div class="space-y-3">
                            <div v-for="service in services" :key="service.service_id" 
                                 class="flex justify-between items-center py-2 border-b border-gray-200 last:border-0">
                                <span class="text-gray-700">{{ service.service_name }}</span>
                                <span class="text-[#14b8a6] font-semibold">{{ service.default_price }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-12 text-gray-500">
                    Прейскурант скоро будет доступен
                </div>
                
                <div class="text-center mt-8">
                    <Link
                        :href="route('register')"
                        class="inline-block px-6 py-3 bg-[#14b8a6] text-white font-semibold rounded-md hover:bg-[#14b8a6]/90 transition"
                    >
                        Записаться на прием
                    </Link>
                </div>
            </div>
        </section>

        <!-- Отзывы -->
        <section id="testimonials" class="py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-bold text-gray-900 mb-4">Отзывы наших клиентов</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                </div>
                
                <div v-if="testimonials.length > 0" class="relative">
                    <!-- Слайдер отзывов -->
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-xl shadow-lg p-8">
                            <div class="flex items-center gap-1 mb-4">
                                <svg v-for="star in 5" :key="star" 
                                     class="w-5 h-5" 
                                     :class="star <= testimonials[currentTestimonialIndex].score ? 'text-yellow-400' : 'text-gray-300'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <p class="text-gray-700 text-lg italic mb-6">"{{ testimonials[currentTestimonialIndex].comment }}"</p>
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-semibold text-gray-900">{{ testimonials[currentTestimonialIndex].client?.client_name || 'Клиент' }}</p>
                                    <p class="text-sm text-gray-500">{{ testimonials[currentTestimonialIndex].service_name || 'Услуга' }}</p>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="prevTestimonial" 
                                            class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button @click="nextTestimonial" 
                                            class="p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                                        <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Индикаторы -->
                    <div class="flex justify-center gap-2 mt-6">
                        <button v-for="(_, index) in testimonials" :key="index"
                                @click="currentTestimonialIndex = index"
                                class="w-2 h-2 rounded-full transition"
                                :class="index === currentTestimonialIndex ? 'bg-[#14b8a6] w-4' : 'bg-gray-300'">
                        </button>
                    </div>
                </div>
                
                <div v-else class="text-center py-12 text-gray-500">
                    Отзывы скоро появятся
                </div>
            </div>
        </section>

        <!-- Призыв к действию -->
        <section class="py-16 bg-gradient-to-r from-[#14b8a6] to-[#0d9488] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-3xl font-bold mb-4">Готовы преобразиться?</h2>
                <p class="text-xl mb-8 opacity-90">Запишитесь на прием прямо сейчас</p>
                <Link
                    :href="route('register')"
                    class="inline-block px-8 py-3 bg-white text-[#14b8a6] font-semibold rounded-md hover:bg-gray-100 transition text-lg"
                >
                    Записаться
                </Link>
            </div>
        </section>

        <!-- Подвал -->
        <footer class="bg-gray-900 text-white py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid md:grid-cols-4 gap-8">
                    <div>
                        <div class="flex items-center mb-4">
                            <svg class="h-8 w-auto text-[#14b8a6]" viewBox="0 0 62 65" fill="none" xmlns="http://www.w3.org/2000/svg">
                                <path d="M61.8548 14.6253..." fill="currentColor"/>
                            </svg>
                            <span class="ml-2 text-lg font-semibold">Елена</span>
                        </div>
                        <p class="text-gray-400 text-sm">Салон красоты премиум-класса</p>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">Навигация</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li><a href="#about" class="hover:text-white transition">О салоне</a></li>
                            <li><a href="#doctors" class="hover:text-white transition">Врачи</a></li>
                            <li><a href="#prices" class="hover:text-white transition">Цены</a></li>
                            <li><a href="#testimonials" class="hover:text-white transition">Отзывы</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">Контакты</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>ул. Пушкина, д. 10</li>
                            <li>+7 (999) 123-45-67</li>
                            <li>info@elena.ru</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="font-semibold mb-4">Режим работы</h4>
                        <ul class="space-y-2 text-gray-400">
                            <li>Пн-Пт: 10:00 - 20:00</li>
                            <li>Сб: 11:00 - 18:00</li>
                            <li>Вс: выходной</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gray-800 mt-8 pt-8 text-center text-gray-400 text-sm">
                    © 2026 Салон красоты "Елена". Все права защищены.
                </div>
            </div>
        </footer>
    </div>
</template>