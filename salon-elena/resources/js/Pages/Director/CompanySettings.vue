<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'УПРАВЛЕНИЕ ПРЕДПРИЯТИЕМ'"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <form @submit.prevent="submit">
                <!-- Вкладки -->
                <div class="mb-6 border-b border-gray-200 dark:border-zinc-700">
                    <div class="flex flex-wrap gap-2">
                        <button type="button" 
                            @click="activeTab = 'main'"
                            :class="activeTab === 'main' 
                                ? 'border-[#8b5cf6] text-[#8b5cf6]' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                            🏢 Основные
                        </button>
                        <button type="button"
                            @click="activeTab = 'requisites'"
                            :class="activeTab === 'requisites' 
                                ? 'border-[#8b5cf6] text-[#8b5cf6]' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                            📜 Реквизиты
                        </button>
                        <button type="button"
                            @click="activeTab = 'bank'"
                            :class="activeTab === 'bank' 
                                ? 'border-[#8b5cf6] text-[#8b5cf6]' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                            🏦 Банковские реквизиты
                        </button>
                        <button type="button"
                            @click="activeTab = 'contacts'"
                            :class="activeTab === 'contacts' 
                                ? 'border-[#8b5cf6] text-[#8b5cf6]' 
                                : 'border-transparent text-gray-500 hover:text-gray-700 dark:hover:text-gray-300'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                            📞 Контакты
                        </button>
                    </div>
                </div>
                
                <!-- Основная информация -->
                <div v-if="activeTab === 'main'" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Название компании <span class="text-red-500">*</span>
                        </label>
                        <input type="text" v-model="form.company_name" required
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="ООО 'Косметологическая клиника'">
                        <p class="text-xs text-gray-500 mt-1">Отображается в шапке сайта и в документах</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Короткое название
                        </label>
                        <input type="text" v-model="form.short_name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="Клиника">
                        <p class="text-xs text-gray-500 mt-1">Используется в мобильной версии и кратких упоминаниях</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">
                            Логотип
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="w-24 h-24 rounded-lg border-2 border-dashed border-gray-300 dark:border-zinc-700 overflow-hidden bg-gray-50 dark:bg-zinc-800 flex items-center justify-center">
                                <img v-if="logoPreview" :src="logoPreview" class="w-full h-full object-cover" />
                                <svg v-else class="w-10 h-10 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                </svg>
                            </div>
                            <div>
                                <input type="file" ref="logoInput" @change="handleLogoUpload" accept="image/*" class="hidden" />
                                <button type="button" @click="$refs.logoInput.click()" 
                                        class="px-4 py-2 text-sm bg-gray-100 dark:bg-zinc-800 rounded-lg hover:bg-gray-200 dark:hover:bg-zinc-700 transition">
                                    Выбрать файл
                                </button>
                                <button v-if="form.logo_url" type="button" @click="removeLogo" 
                                        class="px-4 py-2 text-sm text-red-600 hover:bg-red-50 dark:hover:bg-red-900/20 rounded-lg transition ml-2">
                                    Удалить
                                </button>
                            </div>
                        </div>
                        <p class="text-xs text-gray-500 mt-1">Рекомендуемый размер: 200x200 px. Форматы: PNG, JPG, SVG</p>
                    </div>
                </div>
                
                <!-- Юридические реквизиты -->
                <div v-if="activeTab === 'requisites'" class="space-y-5">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ИНН</label>
                            <input type="text" v-model="form.inn"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="1234567890">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">КПП</label>
                            <input type="text" v-model="form.kpp"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="123456789">
                        </div>
                    </div>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ОГРН</label>
                            <input type="text" v-model="form.ogrn"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="1234567890123">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ОКПО</label>
                            <input type="text" v-model="form.okpo"
                                   class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="12345678">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Юридический адрес</label>
                        <textarea v-model="form.legal_address" rows="2"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="г. Москва, ул. Примерная, д. 1, офис 1"></textarea>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Фактический адрес</label>
                        <textarea v-model="form.actual_address" rows="2"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="Если не указан, используется юридический адрес"></textarea>
                        <p class="text-xs text-gray-500 mt-1">Если поле не заполнено, будет использоваться юридический адрес</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Генеральный директор</label>
                        <input type="text" v-model="form.director_name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="Иванов Иван Иванович">
                        <p class="text-xs text-gray-500 mt-1">ФИО директора для подписи документов</p>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Главный бухгалтер</label>
                        <input type="text" v-model="form.accountant_name"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="Петрова Анна Сергеевна">
                        <p class="text-xs text-gray-500 mt-1">ФИО бухгалтера для подписи документов</p>
                    </div>
                </div>
                
                <!-- Банковские реквизиты -->
                <div v-if="activeTab === 'bank'" class="space-y-5">
                    <div class="space-y-4">
                        <div v-for="(bank, index) in form.bank_details" :key="index" 
                             class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 relative">
                            <button type="button" @click="removeBank(index)" 
                                    class="absolute top-3 right-3 text-red-500 hover:text-red-700 transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                            
                            <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Банк {{ index + 1 }}</h4>
                            
                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Название банка</label>
                                    <input type="text" v-model="bank.bank_name" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] text-sm"
                                           placeholder="ПАО СБЕРБАНК">
                                </div>
                                
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">БИК</label>
                                        <input type="text" v-model="bank.bik" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] text-sm"
                                               placeholder="044525225">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Корр. счет</label>
                                        <input type="text" v-model="bank.correspondent_account" 
                                               class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] text-sm"
                                               placeholder="30101810400000000225">
                                    </div>
                                </div>
                                
                                <div>
                                    <label class="block text-xs font-medium text-gray-600 dark:text-gray-400 mb-1">Расчетный счет</label>
                                    <input type="text" v-model="bank.payment_account" 
                                           class="w-full px-3 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] text-sm"
                                           placeholder="40702810900000012345">
                                </div>
                            </div>
                        </div>
                        
                        <button type="button" @click="addBank" 
                                class="w-full px-4 py-3 border-2 border-dashed border-gray-300 dark:border-zinc-700 rounded-lg text-gray-500 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                            + Добавить банковский счет
                        </button>
                        <p class="text-xs text-gray-500 text-center">Можно добавить несколько банковских счетов</p>
                    </div>
                </div>
                
                <!-- Контакты -->
                <div v-if="activeTab === 'contacts'" class="space-y-5">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Телефон</label>
                        <input type="tel" v-model="form.phone"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="+7 (999) 123-45-67">
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Email</label>
                        <input type="email" v-model="form.email"
                               class="w-full px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                               placeholder="info@example.ru">
                    </div>
                    
                    <div class="pt-4 border-t border-gray-200 dark:border-zinc-700">
                        <h4 class="font-medium text-gray-700 dark:text-gray-300 mb-3">Социальные сети</h4>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Instagram</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">@</span>
                            <input type="text" v-model="form.instagram"
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="username">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Telegram</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">t.me/</span>
                            <input type="text" v-model="form.telegram"
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="username">
                        </div>
                    </div>
                    
                    <div>
                        <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">ВКонтакте</label>
                        <div class="flex items-center gap-2">
                            <span class="text-gray-500">vk.com/</span>
                            <input type="text" v-model="form.vk"
                                   class="flex-1 px-4 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg focus:ring-2 focus:ring-[#8b5cf6] focus:border-transparent dark:bg-zinc-800 dark:text-white"
                                   placeholder="club123456">
                        </div>
                    </div>
                </div>
                
                <!-- Кнопки сохранения -->
                <div class="mt-6 pt-4 border-t border-gray-200 dark:border-zinc-700 flex justify-end gap-3">
                    <button type="button" @click="resetForm" 
                            class="px-6 py-2 border border-gray-300 dark:border-zinc-700 rounded-lg hover:bg-gray-100 dark:hover:bg-zinc-800 transition">
                        Сбросить
                    </button>
                    <button type="submit" :disabled="processing"
                            class="px-6 py-2 bg-[#8b5cf6] text-white rounded-lg hover:bg-[#7c3aed] transition disabled:opacity-50">
                        {{ processing ? 'Сохранение...' : 'Сохранить настройки' }}
                    </button>
                </div>
                
                <!-- Уведомление об успехе -->
                <div v-if="successMessage" class="mt-4 p-3 bg-green-100 text-green-700 rounded-lg">
                    {{ successMessage }}
                </div>
            </form>
        </div>
    </DirectorLayout>
</template>

<script setup>
import { ref, reactive } from 'vue';
import { useForm, router } from '@inertiajs/vue3';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';

const props = defineProps({
    director: Object,
    settings: Object
});

const activeTab = ref('main');
const processing = ref(false);
const successMessage = ref('');
const logoPreview = ref(props.settings?.logo_url || null);
const logoInput = ref(null);

const form = useForm({
    company_name: props.settings?.company_name || '',
    short_name: props.settings?.short_name || '',
    inn: props.settings?.inn || '',
    kpp: props.settings?.kpp || '',
    ogrn: props.settings?.ogrn || '',
    okpo: props.settings?.okpo || '',
    legal_address: props.settings?.legal_address || '',
    actual_address: props.settings?.actual_address || '',
    phone: props.settings?.phone || '',
    email: props.settings?.email || '',
    website: props.settings?.website || '',
    director_name: props.settings?.director_name || '',
    accountant_name: props.settings?.accountant_name || '',
    bank_details: props.settings?.bank_details || [],
    instagram: props.settings?.instagram || '',
    telegram: props.settings?.telegram || '',
    vk: props.settings?.vk || '',
    logo_url: props.settings?.logo_url || null,
});

const submit = () => {
    processing.value = true;
    successMessage.value = '';
    
    form.post('/director/company-settings/save', {
        preserveScroll: true,
        onSuccess: () => {
            processing.value = false;
            successMessage.value = 'Настройки успешно сохранены!';
            setTimeout(() => {
                successMessage.value = '';
            }, 3000);
        },
        onError: () => {
            processing.value = false;
        },
    });
};

const resetForm = () => {
    if (confirm('Сбросить все изменения?')) {
        form.company_name = props.settings?.company_name || '';
        form.short_name = props.settings?.short_name || '';
        form.inn = props.settings?.inn || '';
        form.kpp = props.settings?.kpp || '';
        form.ogrn = props.settings?.ogrn || '';
        form.okpo = props.settings?.okpo || '';
        form.legal_address = props.settings?.legal_address || '';
        form.actual_address = props.settings?.actual_address || '';
        form.phone = props.settings?.phone || '';
        form.email = props.settings?.email || '';
        form.website = props.settings?.website || '';
        form.director_name = props.settings?.director_name || '';
        form.accountant_name = props.settings?.accountant_name || '';
        form.bank_details = JSON.parse(JSON.stringify(props.settings?.bank_details || []));
        form.instagram = props.settings?.instagram || '';
        form.telegram = props.settings?.telegram || '';
        form.vk = props.settings?.vk || '';
        logoPreview.value = props.settings?.logo_url || null;
    }
};

const handleLogoUpload = (event) => {
    const file = event.target.files[0];
    if (!file) return;
    
    const formData = new FormData();
    formData.append('logo', file);
    
    axios.post('/director/company-settings/logo', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
    }).then(response => {
        logoPreview.value = response.data.logo_url;
        form.logo_url = response.data.logo_url;
        successMessage.value = 'Логотип загружен!';
        setTimeout(() => {
            successMessage.value = '';
        }, 3000);
    }).catch(error => {
        alert('Ошибка при загрузке логотипа');
    });
};

const removeLogo = () => {
    if (!confirm('Удалить логотип?')) return;
    
    axios.delete('/director/company-settings/logo').then(() => {
        logoPreview.value = null;
        form.logo_url = null;
        successMessage.value = 'Логотип удален';
        setTimeout(() => {
            successMessage.value = '';
        }, 3000);
    });
};

const addBank = () => {
    if (!form.bank_details) form.bank_details = [];
    form.bank_details.push({
        bank_name: '',
        bik: '',
        correspondent_account: '',
        payment_account: ''
    });
};

const removeBank = (index) => {
    form.bank_details.splice(index, 1);
};
</script>