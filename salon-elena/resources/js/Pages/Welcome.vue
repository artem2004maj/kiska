<!-- resources/js/Pages/Welcome.vue -->
<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    canLogin: Boolean,
    canRegister: Boolean,
    laravelVersion: String,
    phpVersion: String,
    doctors: { type: Array, default: () => [] },
    services: { type: Array, default: () => [] },
    testimonials: { type: Array, default: () => [] }
});

// Мобильное меню (НОВОЕ)
const mobileMenuOpen = ref(false);

const toggleMobileMenu = () => {
    mobileMenuOpen.value = !mobileMenuOpen.value;
    document.body.style.overflow = mobileMenuOpen.value ? 'hidden' : '';
};

// Слайдер отзывов
const currentTestimonialIndex = ref(0);

const nextTestimonial = () => {
    currentTestimonialIndex.value = (currentTestimonialIndex.value + 1) % props.testimonials.length;
};

const prevTestimonial = () => {
    currentTestimonialIndex.value = (currentTestimonialIndex.value - 1 + props.testimonials.length) % props.testimonials.length;
};

onMounted(() => {
    const interval = setInterval(() => {
        if (props.testimonials.length > 1) nextTestimonial();
    }, 5000);
    return () => clearInterval(interval);
});

const servicesByCategory = computed(() => {
    const grouped = {};
    props.services.forEach(service => {
        if (!grouped[service.service_category]) grouped[service.service_category] = [];
        grouped[service.service_category].push(service);
    });
    return grouped;
});
</script>

<template>
    <Head title="Салон красоты 'Елена'" />
    
    <div class="min-h-screen bg-gradient-to-b from-gray-50 to-white">
        <!-- Навигация - адаптирована -->
        <nav class="bg-white shadow-sm sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16 items-center">
                    <!-- Логотип -->
                    <div class="flex items-center">
                        <svg class="h-8 sm:h-10 w-auto text-[#14b8a6]" viewBox="0 0 62 65" fill="none">
                            <path d="M61.8548 14.6253..." fill="currentColor"/>
                        </svg>
                        <span class="ml-2 text-lg sm:text-xl font-semibold text-gray-800">Елена</span>
                    </div>
                    
                    <!-- Десктопное меню (скрыто на мобильных) -->
                    <div class="hidden md:flex space-x-8">
                        <a href="#about" class="text-gray-600 hover:text-[#14b8a6] transition">О салоне</a>
                        <a href="#doctors" class="text-gray-600 hover:text-[#14b8a6] transition">Врачи</a>
                        <a href="#prices" class="text-gray-600 hover:text-[#14b8a6] transition">Цены</a>
                        <a href="#testimonials" class="text-gray-600 hover:text-[#14b8a6] transition">Отзывы</a>
                    </div>
                    
                    <!-- Десктопные кнопки (скрыты на мобильных) -->
                    <div class="hidden md:flex items-center space-x-4">
                        <Link v-if="canLogin" :href="route('login')"
                              class="px-4 py-2 text-sm font-medium text-[#14b8a6] border border-[#14b8a6] rounded-md hover:bg-[#14b8a6] hover:text-white transition">
                            Войти
                        </Link>
                        <Link v-if="canRegister" :href="route('register')"
                              class="px-4 py-2 text-sm font-medium bg-[#14b8a6] text-white rounded-md hover:bg-[#14b8a6]/90 transition">
                            Регистрация
                        </Link>
                    </div>
                    
                    <!-- Мобильные кнопки -->
                    <div class="flex md:hidden items-center space-x-2">
                        <Link v-if="canLogin" :href="route('login')"
                              class="px-3 py-1.5 text-xs font-medium text-[#14b8a6] border border-[#14b8a6] rounded-md">
                            Войти
                        </Link>
                        <button @click="toggleMobileMenu" class="p-2 rounded-lg hover:bg-gray-100">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path v-if="!mobileMenuOpen" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path v-else stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>
            
            <!-- Мобильное меню -->
            <Transition enter-active-class="transition duration-200 ease-out"
                        enter-from-class="transform -translate-y-4 opacity-0"
                        enter-to-class="transform translate-y-0 opacity-100"
                        leave-active-class="transition duration-150 ease-in"
                        leave-from-class="transform translate-y-0 opacity-100"
                        leave-to-class="transform -translate-y-4 opacity-0">
                <div v-if="mobileMenuOpen" class="md:hidden bg-white border-t">
                    <div class="px-4 py-3 space-y-2">
                        <a href="#about" @click="toggleMobileMenu" 
                           class="block px-3 py-2 text-sm text-gray-600 hover:bg-[#14b8a6]/10 rounded-md">О салоне</a>
                        <a href="#doctors" @click="toggleMobileMenu"
                           class="block px-3 py-2 text-sm text-gray-600 hover:bg-[#14b8a6]/10 rounded-md">Врачи</a>
                        <a href="#prices" @click="toggleMobileMenu"
                           class="block px-3 py-2 text-sm text-gray-600 hover:bg-[#14b8a6]/10 rounded-md">Цены</a>
                        <a href="#testimonials" @click="toggleMobileMenu"
                           class="block px-3 py-2 text-sm text-gray-600 hover:bg-[#14b8a6]/10 rounded-md">Отзывы</a>
                        <hr class="my-2">
                        <Link v-if="canRegister" :href="route('register')" @click="toggleMobileMenu"
                              class="block px-3 py-2 text-sm text-[#14b8a6] font-medium hover:bg-[#14b8a6]/10 rounded-md">
                            Регистрация
                        </Link>
                    </div>
                </div>
            </Transition>
        </nav>

        <!-- Hero секция - адаптирована -->
        <section class="relative bg-gradient-to-r from-[#14b8a6] to-[#0d9488] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 sm:py-16 lg:py-24">
                <div class="text-center">
                    <h1 class="text-2xl sm:text-3xl md:text-4xl lg:text-5xl font-bold mb-4 sm:mb-6 px-4">
                        Добро пожаловать в салон красоты "Елена"
                    </h1>
                    <p class="text-sm sm:text-base md:text-lg lg:text-xl mb-6 sm:mb-8 opacity-90 px-4">
                        Профессиональный уход и косметологические услуги
                    </p>
                    <Link :href="route('register')"
                          class="inline-block px-6 sm:px-8 py-2.5 sm:py-3 bg-white text-[#14b8a6] font-semibold rounded-md hover:bg-gray-100 transition text-sm sm:text-base lg:text-lg">
                        Записаться на прием
                    </Link>
                </div>
            </div>
            <!-- Декоративная волна (скрыта на мобильных) -->
            <div class="hidden sm:block absolute bottom-0 left-0 right-0">
                <svg viewBox="0 0 1440 120" class="w-full h-auto">
                    <path fill="#ffffff" fill-opacity="1" d="M0,64L80,69.3C160,75,320,85,480,80C640,75,800,53,960,48C1120,43,1280,53,1360,58.7L1440,64L1440,120L1360,120C1280,120,1120,120,960,120C800,120,640,120,480,120C320,120,160,120,80,120L0,120Z"></path>
                </svg>
            </div>
        </section>

        <!-- О салоне - адаптировано -->
        <section id="about" class="py-12 sm:py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">О салоне</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                </div>
                
                <div class="grid md:grid-cols-2 gap-8 lg:gap-12 items-center">
                    <div class="order-2 md:order-1">
                        <img src="https://i.pinimg.com/736x/1a/18/ba/1a18bad83cfdb3a07e816b061ece0538.jpg" 
                             alt="Интерьер салона"
                             class="rounded-lg shadow-xl w-full">
                    </div>
                    <div class="order-1 md:order-2 space-y-4 sm:space-y-6">
                        <p class="text-sm sm:text-base lg:text-lg text-gray-600 leading-relaxed">
                            Салон красоты "Елена" - это пространство, где профессионализм встречается с комфортом. 
                            Мы предлагаем широкий спектр косметологических услуг с использованием современных технологий 
                            и качественных материалов.
                        </p>
                        <p class="text-sm sm:text-base lg:text-lg text-gray-600 leading-relaxed">
                            Наша команда состоит из опытных специалистов, которые постоянно повышают квалификацию 
                            и следят за последними тенденциями в индустрии красоты.
                        </p>
                        <div class="grid grid-cols-3 gap-2 sm:gap-4 pt-4">
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#14b8a6]">10+</div>
                                <div class="text-xs sm:text-sm text-gray-500">лет опыта</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#14b8a6]">5000+</div>
                                <div class="text-xs sm:text-sm text-gray-500">довольных клиентов</div>
                            </div>
                            <div class="text-center">
                                <div class="text-xl sm:text-2xl lg:text-3xl font-bold text-[#14b8a6]">20+</div>
                                <div class="text-xs sm:text-sm text-gray-500">услуг</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Наши врачи - адаптировано -->
        <section id="doctors" class="py-12 sm:py-16 lg:py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Наши специалисты</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                    <p class="mt-4 text-sm sm:text-base lg:text-lg text-gray-600">Профессионалы своего дела</p>
                </div>
                
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 lg:gap-8">
                    <div v-for="doctor in doctors" :key="doctor.employee_id" 
                         class="bg-white rounded-xl shadow-md overflow-hidden hover:shadow-xl transition">
                        <div class="h-48 sm:h-56 lg:h-64 bg-gradient-to-br from-[#14b8a6]/20 to-[#0d9488]/20 flex items-center justify-center">
                            <svg class="w-16 sm:w-20 lg:w-24 h-16 sm:h-20 lg:h-24 text-[#14b8a6]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <div class="p-4 sm:p-5 lg:p-6">
                            <h3 class="text-lg sm:text-xl font-semibold text-gray-900 mb-1">{{ doctor.employee_name }}</h3>
                            <p class="text-sm text-[#14b8a6] font-medium mb-2 sm:mb-3">{{ doctor.role }}</p>
                            <div class="flex items-center text-gray-600 text-xs sm:text-sm">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                                </svg>
                                <span>Пн-Пт 10:00 - 20:00</span>
                            </div>
                            <div class="flex items-center text-gray-600 text-xs sm:text-sm mt-1 sm:mt-2">
                                <svg class="w-3 h-3 sm:w-4 sm:h-4 mr-1 sm:mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                                <span>Сб 11:00 - 18:00</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="doctors.length === 0" class="text-center py-8 sm:py-12 text-gray-500">
                    Информация о врачах скоро появится
                </div>
            </div>
        </section>

        <!-- Прейскурант - адаптирован -->
        <section id="prices" class="py-12 sm:py-16 lg:py-20 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Прейскурант</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                    <p class="mt-4 text-sm sm:text-base lg:text-lg text-gray-600">Доступные цены на все услуги</p>
                </div>
                
                <div v-if="Object.keys(servicesByCategory).length > 0" class="space-y-6 sm:space-y-8">
                    <div v-for="(services, category) in servicesByCategory" :key="category" 
                         class="bg-gray-50 rounded-xl p-4 sm:p-6">
                        <h3 class="text-xl sm:text-2xl font-semibold text-gray-800 mb-3 sm:mb-4">{{ category }}</h3>
                        <div class="space-y-2 sm:space-y-3">
                            <div v-for="service in services" :key="service.service_id" 
                                 class="flex flex-col sm:flex-row sm:justify-between sm:items-center py-2 sm:py-3 border-b border-gray-200 last:border-0 gap-2">
                                <span class="text-sm sm:text-base text-gray-700">{{ service.service_name }}</span>
                                <span class="text-sm sm:text-base lg:text-lg font-semibold text-[#14b8a6]">{{ service.default_price }} ₽</span>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 sm:py-12 text-gray-500">
                    Прейскурант скоро будет доступен
                </div>
                
                <div class="text-center mt-6 sm:mt-8">
                    <Link :href="route('register')"
                          class="inline-block px-6 sm:px-8 py-2.5 sm:py-3 bg-[#14b8a6] text-white font-semibold rounded-md hover:bg-[#14b8a6]/90 transition text-sm sm:text-base">
                        Записаться на прием
                    </Link>
                </div>
            </div>
        </section>

        <!-- Отзывы - адаптированы -->
        <section id="testimonials" class="py-12 sm:py-16 lg:py-20 bg-gray-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center mb-8 sm:mb-12">
                    <h2 class="text-2xl sm:text-3xl font-bold text-gray-900 mb-4">Отзывы наших клиентов</h2>
                    <div class="w-20 h-1 bg-[#14b8a6] mx-auto"></div>
                </div>
                
                <div v-if="testimonials.length > 0" class="relative">
                    <div class="max-w-3xl mx-auto">
                        <div class="bg-white rounded-xl shadow-lg p-4 sm:p-6 lg:p-8">
                            <div class="flex items-center gap-1 mb-3 sm:mb-4">
                                <svg v-for="star in 5" :key="star" 
                                     class="w-4 h-4 sm:w-5 sm:h-5" 
                                     :class="star <= testimonials[currentTestimonialIndex].score ? 'text-yellow-400' : 'text-gray-300'"
                                     fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z" />
                                </svg>
                            </div>
                            <p class="text-sm sm:text-base lg:text-lg text-gray-700 italic mb-4 sm:mb-6 px-2 sm:px-0">
                                "{{ testimonials[currentTestimonialIndex].comment }}"
                            </p>
                            <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
                                <div>
                                    <p class="font-semibold text-gray-900 text-sm sm:text-base">{{ testimonials[currentTestimonialIndex].client?.client_name || 'Клиент' }}</p>
                                    <p class="text-xs sm:text-sm text-gray-500">{{ testimonials[currentTestimonialIndex].service_name || 'Услуга' }}</p>
                                </div>
                                <div class="flex gap-2 self-end sm:self-auto">
                                    <button @click="prevTestimonial" 
                                            class="p-1.5 sm:p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                                        </svg>
                                    </button>
                                    <button @click="nextTestimonial" 
                                            class="p-1.5 sm:p-2 rounded-full bg-gray-100 hover:bg-gray-200 transition">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Индикаторы -->
                    <div class="flex justify-center gap-2 mt-4 sm:mt-6">
                        <button v-for="(_, index) in testimonials" :key="index"
                                @click="currentTestimonialIndex = index"
                                class="h-1.5 sm:h-2 rounded-full transition-all duration-300"
                                :class="index === currentTestimonialIndex ? 'w-4 sm:w-6 bg-[#14b8a6]' : 'w-1.5 sm:w-2 bg-gray-300'">
                        </button>
                    </div>
                </div>
                
                <div v-else class="text-center py-8 sm:py-12 text-gray-500">
                    Отзывы скоро появятся
                </div>
            </div>
        </section>

        <!-- Призыв к действию - адаптирован -->
        <section class="py-12 sm:py-16 bg-gradient-to-r from-[#14b8a6] to-[#0d9488] text-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <h2 class="text-xl sm:text-2xl lg:text-3xl font-bold mb-3 sm:mb-4">Готовы преобразиться?</h2>
                <p class="text-sm sm:text-base lg:text-xl mb-6 sm:mb-8 opacity-90">Запишитесь на прием прямо сейчас</p>
                <Link :href="route('register')"
                      class="inline-block px-6 sm:px-8 py-2.5 sm:py-3 bg-white text-[#14b8a6] font-semibold rounded-md hover:bg-gray-100 transition text-sm sm:text-base lg:text-lg">
                    Записаться
                </Link>
            </div>
        </section>

        <!-- Подвал - адаптирован -->
        <footer class="bg-gray-900 text-white py-8 sm:py-12">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 sm:gap-8">
                    <div>
                        <div class="flex items-center mb-4">
                            <svg class="h-6 sm:h-8 w-auto text-[#14b8a6]" viewBox="0 0 62 65" fill="none">
                                <path d="M61.8548 14.6253..." fill="currentColor"/>
                            </svg>
                            <span class="ml-2 text-base sm:text-lg font-semibold">Елена</span>
                        </div>
                        <p class="text-xs sm:text-sm text-gray-400">Салон красоты премиум-класса</p>
                    </div>
                    
                    <div>
                        <h4 class="text-sm sm:text-base font-semibold mb-3 sm:mb-4">Навигация</h4>
                        <ul class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-gray-400">
                            <li><a href="#about" class="hover:text-white transition">О салоне</a></li>
                            <li><a href="#doctors" class="hover:text-white transition">Врачи</a></li>
                            <li><a href="#prices" class="hover:text-white transition">Цены</a></li>
                            <li><a href="#testimonials" class="hover:text-white transition">Отзывы</a></li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-sm sm:text-base font-semibold mb-3 sm:mb-4">Контакты</h4>
                        <ul class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-gray-400">
                            <li>ул. Пушкина, д. 10</li>
                            <li>+7 (999) 123-45-67</li>
                            <li>info@elena.ru</li>
                        </ul>
                    </div>
                    
                    <div>
                        <h4 class="text-sm sm:text-base font-semibold mb-3 sm:mb-4">Режим работы</h4>
                        <ul class="space-y-1.5 sm:space-y-2 text-xs sm:text-sm text-gray-400">
                            <li>Пн-Пт: 10:00 - 20:00</li>
                            <li>Сб: 11:00 - 18:00</li>
                            <li>Вс: выходной</li>
                        </ul>
                    </div>
                </div>
                
                <div class="border-t border-gray-800 mt-6 sm:mt-8 pt-6 sm:pt-8 text-center text-xs sm:text-sm text-gray-400">
                    © 2026 Салон красоты "Елена". Все права защищены.
                </div>
            </div>
        </footer>
    </div>
</template>

<style scoped>
/* Анимации для мобильного меню */
.v-enter-active,
.v-leave-active {
    transition: all 0.3s ease;
}

.v-enter-from,
.v-leave-to {
    opacity: 0;
    transform: translateY(-10px);
}
</style>