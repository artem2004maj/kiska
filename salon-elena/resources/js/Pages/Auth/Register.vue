<script setup>
import { ref } from 'vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const showPassword = ref(false);
const showPasswordConfirm = ref(false);

const form = useForm({
    name: '',
    login: '',
    email: '',
    phone: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <Head title="Регистрация" />
    
    <div class="min-h-screen bg-gradient-to-br from-purple-50 via-white to-pink-50 flex items-center justify-center p-4">
        <div class="max-w-md w-full">
            <!-- Логотип и название -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-gradient-to-r from-purple-500 to-pink-500 rounded-2xl flex items-center justify-center mx-auto shadow-lg mb-4">
                    <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z" />
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-gray-800">Регистрация</h1>
                <p class="text-gray-500 mt-1">Создайте новый аккаунт</p>
            </div>
            
            <!-- Форма регистрации -->
            <div class="bg-white rounded-2xl shadow-xl p-8">
                <form @submit.prevent="submit" class="space-y-5">
                    <!-- Имя -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Имя <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="form.name"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            :class="{ 'border-red-500': form.errors.name }"
                            placeholder="Введите ваше имя"
                            required
                        />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-500">{{ form.errors.name }}</p>
                    </div>
                    
                    <!-- Логин -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Логин <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="text" 
                            v-model="form.login"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            :class="{ 'border-red-500': form.errors.login }"
                            placeholder="Придумайте логин"
                            required
                        />
                        <p v-if="form.errors.login" class="mt-1 text-sm text-red-500">{{ form.errors.login }}</p>
                    </div>
                    
                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input 
                            type="email" 
                            v-model="form.email"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            :class="{ 'border-red-500': form.errors.email }"
                            placeholder="example@mail.ru"
                            required
                        />
                        <p v-if="form.errors.email" class="mt-1 text-sm text-red-500">{{ form.errors.email }}</p>
                    </div>
                    
                    <!-- Телефон -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Телефон
                        </label>
                        <input 
                            type="tel" 
                            v-model="form.phone"
                            class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                            :class="{ 'border-red-500': form.errors.phone }"
                            placeholder="+7 999 123-45-67"
                        />
                        <p v-if="form.errors.phone" class="mt-1 text-sm text-red-500">{{ form.errors.phone }}</p>
                        <p class="mt-1 text-xs text-gray-400">Формат: +7XXXXXXXXXX или 8XXXXXXXXXX</p>
                    </div>
                    
                    <!-- Пароль -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Пароль <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                v-model="form.password"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.password }"
                                placeholder="Придумайте пароль"
                                required
                            />
                            <button 
                                type="button"
                                @click="showPassword = !showPassword"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg v-if="!showPassword" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password" class="mt-1 text-sm text-red-500">{{ form.errors.password }}</p>
                    </div>
                    
                    <!-- Подтверждение пароля -->
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2">
                            Подтверждение пароля <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <input 
                                :type="showPasswordConfirm ? 'text' : 'password'"
                                v-model="form.password_confirmation"
                                class="w-full px-4 py-3 border border-gray-300 rounded-xl focus:ring-2 focus:ring-purple-500 focus:border-transparent transition"
                                :class="{ 'border-red-500': form.errors.password_confirmation }"
                                placeholder="Повторите пароль"
                                required
                            />
                            <button 
                                type="button"
                                @click="showPasswordConfirm = !showPasswordConfirm"
                                class="absolute inset-y-0 right-0 pr-3 flex items-center"
                            >
                                <svg v-if="!showPasswordConfirm" class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                <svg v-else class="w-5 h-5 text-gray-400 hover:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                </svg>
                            </button>
                        </div>
                        <p v-if="form.errors.password_confirmation" class="mt-1 text-sm text-red-500">{{ form.errors.password_confirmation }}</p>
                    </div>
                    
                    <!-- Кнопка регистрации -->
                    <button 
                        type="submit" 
                        :disabled="form.processing"
                        class="w-full bg-gradient-to-r from-purple-600 to-pink-600 text-white py-3 rounded-xl font-medium hover:from-purple-700 hover:to-pink-700 transition shadow-lg disabled:opacity-50 disabled:cursor-not-allowed mt-2"
                    >
                        <span v-if="!form.processing">Зарегистрироваться</span>
                        <span v-else>Регистрация...</span>
                    </button>
                    
                    <!-- Ссылка на вход -->
                    <div class="text-center mt-4">
                        <Link href="/login" class="text-sm text-purple-600 hover:text-purple-700 transition">
                            Уже есть аккаунт? Войти
                        </Link>
                    </div>
                </form>
            </div>
            
            <!-- Ссылка на главную -->
            <div class="text-center mt-6">
                <Link href="/" class="text-sm text-gray-500 hover:text-gray-700 transition">
                    ← Вернуться на главную
                </Link>
            </div>
        </div>
    </div>
</template>