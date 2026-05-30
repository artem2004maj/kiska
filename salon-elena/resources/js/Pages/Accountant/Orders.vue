<template>
    <AccountantLayout
        :accountant="accountant"
        :pageTitle="'УПРАВЛЕНИЕ ЗАКАЗАМИ'"
        :unpaidCount="unpaidCount"
        :criticalCount="criticalCount"
        :todayRevenue="todayRevenue"
        :pendingPayments="pendingPayments"
    >
        <div class="bg-white dark:bg-zinc-900 rounded-lg p-6 shadow">
            <div class="flex justify-between items-center mb-6">
                <h2 class="text-2xl font-semibold text-black dark:text-white">ЗАКАЗЫ ПОСТАВЩИКАМ</h2>
            </div>

            <!-- Фильтрация по статусу -->
            <div class="flex flex-wrap gap-3 mb-6">
                <button 
                    v-for="tab in tabs" 
                    :key="tab.value"
                    @click="selectedStatus = tab.value"
                    class="px-4 py-2 rounded-md transition"
                    :class="selectedStatus === tab.value 
                        ? 'bg-[#3b82f6] text-white' 
                        : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700'"
                >
                    {{ tab.label }}
                    <span v-if="tab.count > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">
                        {{ tab.count }}
                    </span>
                </button>
            </div>

            <!-- Список заказов -->
            <div v-if="loading" class="text-center py-8">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#3b82f6]"></div>
                <p class="mt-2 text-gray-500">Загрузка...</p>
            </div>

            <div v-else-if="filteredOrders.length === 0" class="text-center py-8 text-gray-500">
                Нет заказов в этом статусе
            </div>

            <div v-else class="space-y-4">
                <div 
                    v-for="order in filteredOrders" 
                    :key="order.id"
                    class="border border-gray-200 dark:border-zinc-700 rounded-lg p-4 hover:shadow-md transition"
                >
                    <div class="flex flex-wrap justify-between items-start gap-4">
                        <!-- Основная информация -->
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-black dark:text-white">
                                    Заказ №{{ order.number }}
                                </h3>
                                <span 
                                    class="px-2 py-1 text-xs rounded-full"
                                    :class="{
                                        'bg-yellow-100 text-yellow-800': order.status === 0,
                                        'bg-blue-100 text-blue-800': order.status === 1,
                                        'bg-green-100 text-green-800': order.status === 2,
                                        'bg-red-100 text-red-800': order.status === 3
                                    }"
                                >
                                    {{ order.status_text }}
                                </span>
                            </div>
                            
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-2 text-sm">
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Поставщик:</span>
                                    <span class="font-medium text-black dark:text-white">{{ order.supplier_name }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Сумма заказа:</span>
                                    <span class="font-medium text-[#3b82f6]">{{ formatPrice(order.total_amount) }}</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата создания:</span>
                                    <span>{{ formatDateTime(order.created_at) }}</span>
                                </div>
                                <div v-if="order.status === 1 || order.status === 2" class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата подтверждения:</span>
                                    <span>{{ order.confirmed_at ? formatDateTime(order.confirmed_at) : '—' }}</span>
                                </div>
                                <div v-if="order.status === 2" class="flex items-center gap-2">
                                    <span class="text-gray-500 w-32">Дата получения:</span>
                                    <span>{{ order.received_at ? formatDateTime(order.received_at) : '—' }}</span>
                                </div>
                            </div>
                        </div>

                        <!-- Действия -->
                        <div class="flex gap-2">
                            <button 
                                @click="showOrderDetails(order)"
                                class="px-3 py-1.5 text-sm bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 rounded-md hover:bg-gray-200 dark:hover:bg-zinc-700 transition"
                            >
                                Детали заказа
                            </button>
                            
                            <!-- Кнопка "Документ поставки" для завершенных заказов -->
                            <button 
                                v-if="order.status === 2"
                                @click="showSupplyDocument(order)"
                                class="px-3 py-1.5 text-sm bg-indigo-100 dark:bg-indigo-900/30 text-indigo-700 dark:text-indigo-300 rounded-md hover:bg-indigo-200 dark:hover:bg-indigo-900/50 transition"
                            >
                                Документ поставки
                            </button>
                            
                            <!-- Кнопка "Документ для оплаты" для заказов в пути -->
                            <button 
                                v-if="order.status === 1"
                                @click="showPaymentDocument(order)"
                                class="px-3 py-1.5 text-sm bg-purple-100 dark:bg-purple-900/30 text-purple-700 dark:text-purple-300 rounded-md hover:bg-purple-200 dark:hover:bg-purple-900/50 transition"
                            >
                                Документ для оплаты
                            </button>
                            
                            <!-- Кнопка отмены для неподтвержденных заказов -->
                            <button 
                                v-if="order.status === 0"
                                @click="cancelOrder(order)"
                                class="px-3 py-1.5 text-sm bg-red-500 text-white rounded-md hover:bg-red-600 transition"
                            >
                                Отменить заказ
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно деталей заказа -->
        <Teleport to="body">
            <div v-if="showDetailsModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="closeDetailsModal"></div>
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-3xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 px-6 py-4 border-b flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Детали заказа №{{ selectedOrder?.number }}</h3>
                            <button @click="closeDetailsModal" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>
                        
                        <div v-if="selectedOrder" class="p-6">
                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-500">Поставщик</p>
                                    <p class="font-medium">{{ selectedOrder.supplier_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Статус</p>
                                    <span class="inline-block px-2 py-1 text-xs rounded-full"
                                        :class="{
                                            'bg-yellow-100 text-yellow-800': selectedOrder.status === 0,
                                            'bg-blue-100 text-blue-800': selectedOrder.status === 1,
                                            'bg-green-100 text-green-800': selectedOrder.status === 2,
                                            'bg-red-100 text-red-800': selectedOrder.status === 3
                                        }">
                                        {{ selectedOrder.status_text }}
                                    </span>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Дата создания</p>
                                    <p>{{ formatDateTime(selectedOrder.created_at) }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-gray-500">Итого закупка</p>
                                    <p class="text-lg font-bold text-[#3b82f6]">{{ formatPrice(selectedOrder.total_amount) }}</p>
                                </div>
                                <div v-if="selectedOrder.confirmed_at">
                                    <p class="text-sm text-gray-500">Дата подтверждения</p>
                                    <p>{{ formatDateTime(selectedOrder.confirmed_at) }}</p>
                                </div>
                                <div v-if="selectedOrder.received_at">
                                    <p class="text-sm text-gray-500">Дата получения</p>
                                    <p>{{ formatDateTime(selectedOrder.received_at) }}</p>
                                </div>
                            </div>
                            
                            <h4 class="font-semibold mb-3">Состав заказа</h4>
                            <div class="overflow-x-auto border rounded-lg">
                                <table class="w-full">
                                    <thead class="bg-gray-50 dark:bg-zinc-800">
                                        <tr>
                                            <th class="text-left px-4 py-2">Материал</th>
                                            <th class="text-right px-4 py-2">Количество</th>
                                            <th class="text-right px-4 py-2">Цена</th>
                                            <th class="text-right px-4 py-2">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in selectedOrder.items" :key="idx" class="border-t">
                                            <td class="px-4 py-2">{{ item.material_name }}</td>
                                            <td class="px-4 py-2 text-right">{{ item.quantity }} {{ item.unit }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 dark:bg-zinc-800 font-bold">
                                        <tr class="border-t">
                                            <td colspan="3" class="px-4 py-2 text-right">Итого:</td>
                                            <td class="px-4 py-2 text-right text-[#3b82f6]">{{ formatPrice(selectedOrder.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        
                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 px-6 py-4 border-t flex justify-end">
                            <button @click="closeDetailsModal" 
                                    class="px-4 py-2 border border-gray-300 rounded-md hover:bg-gray-100">
                                Закрыть
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно ДОКУМЕНТА ДЛЯ ОПЛАТЫ (стиль как у директора) -->
        <Teleport to="body">
            <div v-if="showPaymentModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showPaymentModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Договор поставки №{{ selectedPaymentDocument?.number }}</h3>
                            <button @click="showPaymentModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6" id="payment-document-content" v-if="selectedPaymentDocument">
                            <!-- Шапка документа -->
                            <div class="text-center mb-8">
                                <img v-if="$page.props.company?.logo_url" 
                                    :src="$page.props.company.logo_url" 
                                    class="h-16 mx-auto mb-2 object-contain" />
                                <h2 class="text-2xl font-bold">{{ $page.props.company?.name || 'Косметологическая клиника' }}</h2>
                                <p class="text-sm text-gray-500">{{ $page.props.company?.legal_address || $page.props.company?.actual_address || '' }}</p>
                                <p class="text-sm text-gray-500">ИНН: {{ $page.props.company?.inn || '—' }} | ОГРН: {{ $page.props.company?.ogrn || '—' }}</p>
                                <p class="text-sm text-gray-500">Тел.: {{ $page.props.company?.phone || '' }} | Email: {{ $page.props.company?.email || '' }}</p>
                                <p class="text-lg font-semibold mt-4">ДОГОВОР ПОСТАВКИ № {{ selectedPaymentDocument.number }}</p>
                                <p class="text-sm text-gray-500">г. {{ selectedPaymentDocument.city || 'Москва' }} | {{ formatDate(selectedPaymentDocument.created_at) }}</p>
                            </div>

                            <!-- Информация о сторонах -->
                            <div class="grid grid-cols-2 gap-6 mb-8 pb-4 border-b">
                                <div>
                                    <h4 class="font-semibold mb-2">Поставщик:</h4>
                                    <p><strong>{{ selectedPaymentDocument.supplier_name }}</strong></p>
                                    <p class="text-sm mt-1">ИНН: {{ selectedPaymentDocument.supplier_inn || '—' }}</p>
                                    <p class="text-sm">КПП: {{ selectedPaymentDocument.supplier_kpp || '—' }}</p>
                                    <p class="text-sm">ОГРН: {{ selectedPaymentDocument.supplier_ogrn || '—' }}</p>
                                    <p class="text-sm">Адрес: {{ selectedPaymentDocument.supplier_address || '—' }}</p>
                                    <p class="text-sm">Телефон: {{ selectedPaymentDocument.supplier_phone || '—' }}</p>
                                    <p class="text-sm">Email: {{ selectedPaymentDocument.supplier_email || '—' }}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Покупатель:</h4>
                                    <p><strong>{{ $page.props.company?.name || 'ООО "Косметологическая клиника"' }}</strong></p>
                                    <p class="text-sm mt-1">ИНН: {{ $page.props.company?.inn || '—' }}</p>
                                    <p class="text-sm">КПП: {{ $page.props.company?.kpp || '—' }}</p>
                                    <p class="text-sm">ОГРН: {{ $page.props.company?.ogrn || '—' }}</p>
                                    <p class="text-sm">Юр. адрес: {{ $page.props.company?.legal_address || '—' }}</p>
                                    <p class="text-sm">Факт. адрес: {{ $page.props.company?.actual_address || '—' }}</p>
                                    <p class="text-sm">Телефон: {{ $page.props.company?.phone || '—' }}</p>
                                    <p class="text-sm">Email: {{ $page.props.company?.email || '—' }}</p>
                                </div>
                            </div>

                            <!-- Спецификация -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">Спецификация</h4>
                                <div class="overflow-x-auto border rounded-lg">
                                    <table class="w-full">
                                        <thead class="bg-gray-50">
                                            <tr>
                                                <th class="text-left px-4 py-2">№</th>
                                                <th class="text-left px-4 py-2">Наименование</th>
                                                <th class="text-right px-4 py-2">Количество</th>
                                                <th class="text-right px-4 py-2">Ед. изм.</th>
                                                <th class="text-right px-4 py-2">Цена за ед.</th>
                                                <th class="text-right px-4 py-2">Сумма</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr v-for="(item, idx) in selectedPaymentDocument.items" :key="idx" class="border-t">
                                                <td class="px-4 py-2">{{ idx + 1 }}</td>
                                                <td class="px-4 py-2">{{ item.material_name }}</td>
                                                <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                                <td class="px-4 py-2 text-right">{{ item.unit }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                                <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                            </tr>
                                        </tbody>
                                        <tfoot class="bg-gray-50 font-bold">
                                            <tr class="border-t">
                                                <td colspan="5" class="px-4 py-2 text-right">ИТОГО:</td>
                                                <td class="px-4 py-2 text-right text-[#8b5cf6]">{{ formatPrice(selectedPaymentDocument.total_amount) }}</td>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>
                                <p class="text-sm text-gray-500 mt-2">* НДС не облагается (упрощенная система налогообложения)</p>
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t px-6 py-4 flex justify-end gap-3">
                            <button @click="printPaymentDocument" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                🖨️ Печать
                            </button>
                            <button @click="exportPaymentDocumentToExcel" 
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                                📊 Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Модальное окно документа поставки -->
        <Teleport to="body">
            <div v-if="showSupplyModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showSupplyModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Документ поставки №{{ selectedSupplyOrder?.number }}</h3>
                            <button @click="showSupplyModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6" id="receipt-content" v-if="selectedSupplyOrder">
                            <div class="text-center mb-8">
                                <img v-if="$page.props.company?.logo_url" 
                                    :src="$page.props.company.logo_url" 
                                    class="h-16 mx-auto mb-2 object-contain" />
                                <h2 class="text-2xl font-bold">{{ $page.props.company?.name || 'Косметологическая клиника' }}</h2>
                                <p class="text-gray-500">Документ поставки № {{ selectedSupplyOrder.number }}</p>
                                <p class="text-sm text-gray-400">Дата формирования: {{ formatDateTime(selectedSupplyOrder.created_at) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-500">Поставщик:</p>
                                    <p class="font-medium">{{ selectedSupplyOrder.supplier_name }}</p>
                                    <p class="text-sm">ИНН: {{ selectedSupplyOrder.supplier_inn || '—' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Дата поставки:</p>
                                    <p class="font-medium">{{ formatDate(selectedSupplyOrder.received_at) || formatDate(selectedSupplyOrder.confirmed_at) }}</p>
                                </div>
                            </div>

                            <div class="overflow-x-auto border rounded-lg mb-6">
                                <table class="w-full">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th class="text-left px-4 py-2">№</th>
                                            <th class="text-left px-4 py-2">Наименование</th>
                                            <th class="text-right px-4 py-2">Количество</th>
                                            <th class="text-right px-4 py-2">Ед. изм.</th>
                                            <th class="text-right px-4 py-2">Цена</th>
                                            <th class="text-right px-4 py-2">Сумма</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(item, idx) in selectedSupplyOrder.items" :key="idx" class="border-t">
                                            <td class="px-4 py-2">{{ idx + 1 }}</td>
                                            <td class="px-4 py-2">{{ item.material_name }}</td>
                                            <td class="px-4 py-2 text-right">{{ item.quantity }}</td>
                                            <td class="px-4 py-2 text-right">{{ item.unit }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.price) }}</td>
                                            <td class="px-4 py-2 text-right">{{ formatPrice(item.total) }}</td>
                                        </tr>
                                    </tbody>
                                    <tfoot class="bg-gray-50 font-bold">
                                        <tr class="border-t">
                                            <td colspan="5" class="px-4 py-2 text-right">Итого:</td>
                                            <td class="px-4 py-2 text-right text-[#8b5cf6]">{{ formatPrice(selectedSupplyOrder.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="grid grid-cols-2 gap-8 mt-8 pt-4 border-t">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Поставщик</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ selectedSupplyOrder.supplier_name }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Директор</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ $page.props.company?.director_name || accountant?.employee_name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t px-6 py-4 flex justify-end gap-3">
                            <button @click="printSupplyDocument" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                🖨️ Печать
                            </button>
                            <button @click="exportSupplyDocumentToExcel" 
                                    class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition">
                                📊 Excel
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Teleport>

        <!-- Уведомления -->
        <Teleport to="body">
            <div v-if="notification.show" 
                 class="fixed bottom-4 right-4 z-50 animate-slide-up"
                 :class="notification.type === 'success' ? 'bg-green-500' : 'bg-red-500'">
                <div class="px-4 py-3 rounded-lg shadow-lg text-white">
                    {{ notification.message }}
                </div>
            </div>
        </Teleport>
    </AccountantLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import AccountantLayout from '@/Layouts/AccountantLayout.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    accountant: Object,
    unpaidCount: Number,
    criticalCount: Number,
    todayRevenue: Number,
    pendingPayments: Number,
    company: Object
});

const loading = ref(true);
const orders = ref([]);
const selectedStatus = ref('all');
const showDetailsModal = ref(false);
const selectedOrder = ref(null);
const showSupplyModal = ref(false);
const showPaymentModal = ref(false);
const selectedSupplyOrder = ref(null);
const selectedPaymentDocument = ref(null);

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

const tabs = computed(() => {
    const counts = {
        0: orders.value.filter(o => o.status === 0).length,
        1: orders.value.filter(o => o.status === 1).length,
        2: orders.value.filter(o => o.status === 2).length,
        3: orders.value.filter(o => o.status === 3).length,
        all: orders.value.length
    };
    
    return [
        { value: 0, label: 'Не подтверждены', count: counts[0] },
        { value: 1, label: 'В пути', count: counts[1] },
        { value: 2, label: 'Завершены', count: counts[2] },
        { value: 3, label: 'Отменены', count: counts[3] },
        { value: 'all', label: 'Все', count: counts.all }
    ];
});

const filteredOrders = computed(() => {
    if (selectedStatus.value === 'all') return orders.value;
    return orders.value.filter(o => o.status === selectedStatus.value);
});

const showNotification = (type, message) => {
    notification.value = { show: true, type, message };
    setTimeout(() => {
        notification.value.show = false;
    }, 3000);
};

const formatPrice = (price) => {
    if (!price && price !== 0) return '0 ₽';
    return new Intl.NumberFormat('ru-RU').format(price) + ' ₽';
};

const formatDate = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU');
};

const formatDateTime = (date) => {
    if (!date) return '—';
    const d = new Date(date);
    return d.toLocaleDateString('ru-RU') + ' ' + d.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' });
};

const loadOrders = async () => {
    loading.value = true;
    try {
        const response = await axios.get('/api/accountant/orders');
        orders.value = response.data;
    } catch (error) {
        console.error('Error loading orders:', error);
        showNotification('error', 'Ошибка загрузки заказов');
    } finally {
        loading.value = false;
    }
};

const showOrderDetails = (order) => {
    selectedOrder.value = order;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedOrder.value = null;
};

const showSupplyDocument = async (order) => {
    try {
        const response = await axios.get(`/api/accountant/orders/${order.id}/document`);
        selectedSupplyOrder.value = response.data;
        showSupplyModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка при загрузке документа');
    }
};

const showPaymentDocument = async (order) => {
    try {
        const response = await axios.get(`/api/accountant/orders/${order.id}/document`);
        selectedPaymentDocument.value = response.data;
        showPaymentModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка при загрузке документа');
    }
};

const cancelOrder = async (order) => {
    if (!confirm(`Отменить заказ №${order.number}?`)) return;
    
    try {
        await axios.put(`/api/accountant/orders/${order.id}/status`, { status: 3 });
        showNotification('success', `Заказ №${order.number} отменен`);
        await loadOrders();
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при отмене заказа');
    }
};

// Печать документа для оплаты (стиль как у директора)
const printPaymentDocument = () => {
    const data = selectedPaymentDocument.value;
    const comp = props.company || {};
    const currentDate = new Date().toLocaleDateString('ru-RU');
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Договор поставки ${data.number}</title>
            <style>
                * {
                    margin: 0;
                    padding: 0;
                    box-sizing: border-box;
                }
                
                body {
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 12pt;
                    line-height: 1.4;
                    color: #000;
                    background: #fff;
                    padding: 20mm;
                }
                
                .document {
                    max-width: 210mm;
                    margin: 0 auto;
                }
                
                /* Шапка документа */
                .header {
                    text-align: center;
                    margin-bottom: 20px;
                    padding-bottom: 10px;
                    border-bottom: 1px solid #000;
                }
                
                .logo {
                    max-width: 80px;
                    max-height: 80px;
                    margin-bottom: 10px;
                }
                
                .company-name {
                    font-size: 18pt;
                    font-weight: bold;
                    margin-bottom: 5px;
                }
                
                .company-details {
                    font-size: 10pt;
                    color: #555;
                    margin-bottom: 5px;
                }
                
                .document-title {
                    font-size: 16pt;
                    font-weight: bold;
                    margin: 15px 0 5px;
                }
                
                .document-number {
                    font-size: 14pt;
                    margin-bottom: 5px;
                }
                
                .document-date {
                    font-size: 10pt;
                    color: #555;
                }
                
                /* Блоки информации */
                .parties {
                    display: flex;
                    justify-content: space-between;
                    gap: 30px;
                    margin: 20px 0;
                    padding-bottom: 15px;
                    border-bottom: 1px solid #000;
                }
                
                .party {
                    flex: 1;
                }
                
                .party-title {
                    font-weight: bold;
                    font-size: 12pt;
                    margin-bottom: 8px;
                    text-decoration: underline;
                }
                
                .party p {
                    margin-bottom: 3px;
                    font-size: 10pt;
                }
                
                /* Заголовки секций */
                .section-title {
                    font-weight: bold;
                    font-size: 12pt;
                    margin: 15px 0 10px;
                    text-decoration: underline;
                }
                
                /* Таблица */
                .table-container {
                    margin: 15px 0;
                    overflow-x: auto;
                }
                
                table {
                    width: 100%;
                    border-collapse: collapse;
                    font-size: 10pt;
                }
                
                th, td {
                    border: 1px solid #000;
                    padding: 6px 8px;
                    text-align: left;
                    vertical-align: top;
                }
                
                th {
                    background-color: #f5f5f5;
                    font-weight: bold;
                    text-align: center;
                }
                
                .text-right {
                    text-align: right;
                }
                
                .text-center {
                    text-align: center;
                }
                
                .total-row {
                    font-weight: bold;
                    background-color: #f9f9f9;
                }
                
                /* Текст */
                .text-note {
                    font-size: 9pt;
                    color: #666;
                    margin-top: 5px;
                    font-style: italic;
                }
                
                /* Реквизиты */
                .requisites {
                    display: flex;
                    justify-content: space-between;
                    gap: 30px;
                    margin: 20px 0;
                }
                
                .requisite {
                    flex: 1;
                }
                
                .requisite-title {
                    font-weight: bold;
                    margin-bottom: 8px;
                    text-decoration: underline;
                }
                
                .requisite p {
                    margin-bottom: 3px;
                    font-size: 9pt;
                }
                
                /* Подписи */
                .signatures {
                    display: flex;
                    justify-content: space-between;
                    gap: 50px;
                    margin: 30px 0 20px;
                }
                
                .signature {
                    flex: 1;
                    text-align: center;
                }
                
                .signature-line {
                    margin-top: 30px;
                    padding-top: 5px;
                    border-top: 1px solid #000;
                }
                
                .signature-title {
                    font-size: 10pt;
                    margin-bottom: 5px;
                }
                
                .stamp {
                    max-width: 80px;
                    max-height: 80px;
                    margin-top: 10px;
                    opacity: 0.7;
                }
                
                /* Подвал */
                .footer {
                    margin-top: 30px;
                    padding-top: 10px;
                    border-top: 1px solid #ccc;
                    font-size: 8pt;
                    text-align: center;
                    color: #888;
                }
                
                @media print {
                    body {
                        padding: 15mm;
                    }
                    button {
                        display: none;
                    }
                    .no-print {
                        display: none;
                    }
                }
            </style>
        </head>
        <body>
            <div class="document">
                <!-- Шапка -->
                <div class="header">
                    ${comp.logo_url ? `<img src="${comp.logo_url}" class="logo" alt="logo">` : ''}
                    <div class="company-name">${comp.name || 'Косметологическая клиника'}</div>
                    <div class="company-details">${comp.legal_address || comp.actual_address || ''}</div>
                    <div class="company-details">ИНН: ${comp.inn || '—'} | ОГРН: ${comp.ogrn || '—'}</div>
                    <div class="company-details">Тел.: ${comp.phone || ''} | Email: ${comp.email || ''}</div>
                    <div class="document-title">ДОГОВОР ПОСТАВКИ № ${data.number}</div>
                    <div class="document-date">г. ${data.city || 'Москва'} | ${formatDate(data.created_at)}</div>
                </div>
                
                <!-- Стороны договора -->
                <div class="parties">
                    <div class="party">
                        <div class="party-title">Поставщик:</div>
                        <p><strong>${data.supplier_name}</strong></p>
                        <p>ИНН: ${data.supplier_inn || '—'}</p>
                        <p>КПП: ${data.supplier_kpp || '—'}</p>
                        <p>ОГРН: ${data.supplier_ogrn || '—'}</p>
                        <p>Адрес: ${data.supplier_address || '—'}</p>
                        <p>Телефон: ${data.supplier_phone || '—'}</p>
                        <p>Email: ${data.supplier_email || '—'}</p>
                    </div>
                    <div class="party">
                        <div class="party-title">Покупатель:</div>
                        <p><strong>${comp.name || 'ООО "Косметологическая клиника"'}</strong></p>
                        <p>ИНН: ${comp.inn || '—'}</p>
                        <p>КПП: ${comp.kpp || '—'}</p>
                        <p>ОГРН: ${comp.ogrn || '—'}</p>
                        <p>Юр. адрес: ${comp.legal_address || '—'}</p>
                        <p>Факт. адрес: ${comp.actual_address || '—'}</p>
                        <p>Телефон: ${comp.phone || '—'}</p>
                        <p>Email: ${comp.email || '—'}</p>
                        <p>В лице: Директора <strong>${comp.director_name || props.accountant?.employee_name}</strong></p>
                    </div>
                </div>
                
                <!-- Предмет договора -->
                <div class="section-title">1. ПРЕДМЕТ ДОГОВОРА</div>
                <p>Поставщик обязуется передать в собственность Покупателя, а Покупатель обязуется принять и оплатить материалы для косметологических процедур в ассортименте, количестве и по ценам, указанным в Спецификации (Приложение №1), которая является неотъемлемой частью настоящего Договора.</p>
                
                <!-- Спецификация -->
                <div class="section-title">Приложение №1. Спецификация</div>
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th style="width: 5%">№</th>
                                <th style="width: 40%">Наименование</th>
                                <th style="width: 10%">Кол-во</th>
                                <th style="width: 10%">Ед. изм.</th>
                                <th style="width: 15%">Цена за ед.</th>
                                <th style="width: 20%">Сумма</th>
                            </tr>
                        </thead>
                        <tbody>
                            ${data.items.map((item, idx) => `
                                <tr>
                                    <td class="text-center">${idx + 1}</td>
                                    <td>${item.material_name}</td>
                                    <td class="text-right">${item.quantity}</td>
                                    <td class="text-center">${item.unit}</td>
                                    <td class="text-right">${formatPrice(item.price)}</td>
                                    <td class="text-right">${formatPrice(item.total)}</td>
                                </tr>
                            `).join('')}
                            <tr class="total-row">
                                <td colspan="5" class="text-right"><strong>ИТОГО:</strong></td>
                                <td class="text-right"><strong>${formatPrice(data.total_amount)}</strong></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <div class="text-note">* НДС не облагается (упрощенная система налогообложения)</div>
                
                <!-- Условия оплаты -->
                <div class="section-title">2. УСЛОВИЯ ОПЛАТЫ</div>
                <p>Покупатель производит 100% предоплату в течение 5 (пяти) рабочих дней с момента подписания настоящего договора. Оплата производится безналичным перечислением на расчетный счет Поставщика.</p>
                
                <!-- Сроки поставки -->
                <div class="section-title">3. СРОКИ ПОСТАВКИ</div>
                <p>Поставка товара осуществляется в течение 14 (четырнадцати) рабочих дней с момента поступления оплаты на расчетный счет Поставщика.</p>
                
                <!-- Реквизиты -->
                <div class="requisites">
                    <div class="requisite">
                        <div class="requisite-title">Реквизиты Поставщика</div>
                        <p>Наименование: ${data.supplier_name}</p>
                        <p>ИНН: ${data.supplier_inn || '—'}</p>
                        <p>КПП: ${data.supplier_kpp || '—'}</p>
                        <p>Банк: ${data.bank_name || '—'}</p>
                        <p>БИК: ${data.bic || '—'}</p>
                        <p>Корр. счет: ${data.correspondent_account || '—'}</p>
                        <p>Р/с: ${data.payment_account || '—'}</p>
                    </div>
                    <div class="requisite">
                        <div class="requisite-title">Реквизиты Покупателя</div>
                        <p>Наименование: ${comp.name || '—'}</p>
                        <p>ИНН: ${comp.inn || '—'}</p>
                        <p>КПП: ${comp.kpp || '—'}</p>
                        <p>ОГРН: ${comp.ogrn || '—'}</p>
                        ${comp.bank_details && comp.bank_details.length > 0 ? `
                            ${comp.bank_details.map((bank, idx) => `
                                <p><strong>Банк ${idx + 1}:</strong> ${bank.bank_name || '—'}</p>
                                <p>БИК: ${bank.bik || '—'}</p>
                                <p>Корр. счет: ${bank.correspondent_account || '—'}</p>
                                <p>Р/с: ${bank.payment_account || '—'}</p>
                            `).join('')}
                        ` : '<p>Банковские реквизиты не указаны</p>'}
                    </div>
                </div>
                
                <!-- Подписи -->
                <div class="signatures">
                    <div class="signature">
                        <div class="signature-title">Поставщик</div>
                        <div class="signature-line"></div>
                        <p>${data.supplier_name}</p>
                        <p class="text-note">(подпись, печать)</p>
                    </div>
                    <div class="signature">
                        <div class="signature-title">Покупатель</div>
                        <div class="signature-line"></div>
                        <p>${comp.director_name || props.accountant?.employee_name}</p>
                        <p class="text-note">Директор ${comp.name || 'ООО "Косметологическая клиника"'}</p>
                        ${comp.stamp_url ? `<img src="${comp.stamp_url}" class="stamp" alt="stamp">` : ''}
                    </div>
                </div>
                
                <!-- Подвал -->
                <div class="footer">
                    Документ сформирован автоматически ${currentDate}
                </div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

const exportPaymentDocumentToExcel = () => {
    if (!selectedPaymentDocument.value) return;
    const data = selectedPaymentDocument.value;
    const excelData = [
        ['ДОГОВОР ПОСТАВКИ'],
        [`№ ${data.number}`],
        [`Дата: ${formatDate(data.created_at)}`],
        [],
        ['Поставщик:', data.supplier_name],
        ['Покупатель:', $page.props.company?.name || 'Клиника'],
        [],
        ['СПЕЦИФИКАЦИЯ'],
        ['№', 'Наименование', 'Кол-во', 'Ед. изм.', 'Цена', 'Сумма']
    ];
    data.items.forEach((item, idx) => {
        excelData.push([idx + 1, item.material_name, item.quantity, item.unit, item.price, item.total]);
    });
    excelData.push([], ['ИТОГО:', '', '', '', '', data.total_amount]);
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    XLSX.utils.book_append_sheet(XLSX.utils.book_new(), ws, `Договор_${data.number}`);
    XLSX.writeFile(wb, `Договор_поставки_${data.number}.xlsx`);
};

// Печать документа поставки (стиль как у директора)
const printSupplyDocument = () => {
    const data = selectedSupplyOrder.value;
    const comp = props.company || {};
    const currentDate = new Date().toLocaleDateString('ru-RU');
    
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <title>Документ поставки ${data.number}</title>
            <style>
                * { margin: 0; padding: 0; box-sizing: border-box; }
                body {
                    font-family: 'Times New Roman', Times, serif;
                    font-size: 12pt;
                    padding: 20mm;
                }
                .header { text-align: center; margin-bottom: 20px; border-bottom: 1px solid #000; padding-bottom: 10px; }
                .company-name { font-size: 18pt; font-weight: bold; }
                .company-details { font-size: 10pt; color: #555; }
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
                th { background: #f5f5f5; text-align: center; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .signatures { display: flex; justify-content: space-between; margin-top: 30px; }
                .signature { text-align: center; flex: 1; }
                .signature-line { margin-top: 30px; padding-top: 5px; border-top: 1px solid #000; }
                .footer { margin-top: 30px; padding-top: 10px; border-top: 1px solid #ccc; font-size: 8pt; text-align: center; color: #888; }
                @media print { body { padding: 15mm; } button { display: none; } }
            </style>
        </head>
        <body>
            <div class="header">
                ${comp.logo_url ? `<img src="${comp.logo_url}" class="logo" style="max-width:80px; margin-bottom:10px;">` : ''}
                <div class="company-name">${comp.name || 'Косметологическая клиника'}</div>
                <div class="company-details">${comp.legal_address || comp.actual_address || ''}</div>
                <div class="company-details">ИНН: ${comp.inn || '—'} | ОГРН: ${comp.ogrn || '—'}</div>
                <div class="company-details">Тел.: ${comp.phone || ''} | Email: ${comp.email || ''}</div>
                <p style="margin-top:15px; font-size:14pt; font-weight:bold;">ДОКУМЕНТ ПОСТАВКИ № ${data.number}</p>
                <p style="font-size:10pt; color:#555;">г. ${data.city || 'Москва'} | ${formatDate(data.created_at)}</p>
            </div>
            
            <div style="margin: 15px 0;">
                <p><strong>Поставщик:</strong> ${data.supplier_name}</p>
                <p><strong>ИНН:</strong> ${data.supplier_inn || '—'}</p>
                <p><strong>Дата поставки:</strong> ${formatDate(data.received_at) || formatDate(data.confirmed_at)}</p>
            </div>
            
            <table>
                <thead>
                    <tr><th>№</th><th>Наименование</th><th>Кол-во</th><th>Ед. изм.</th><th>Цена</th><th>Сумма</th></tr>
                </thead>
                <tbody>
                    ${data.items.map((item, idx) => `
                        <tr>
                            <td class="text-center">${idx + 1}</td>
                            <td>${item.material_name}</td>
                            <td class="text-right">${item.quantity}</td>
                            <td class="text-center">${item.unit}</td>
                            <td class="text-right">${formatPrice(item.price)}</td>
                            <td class="text-right">${formatPrice(item.total)}</td>
                        </tr>
                    `).join('')}
                    <tr style="font-weight: bold; background: #f9f9f9;">
                        <td colspan="5" class="text-right">ИТОГО:</td>
                        <td class="text-right">${formatPrice(data.total_amount)}</td>
                    </tr>
                </tbody>
            </table>
            
            <div class="signatures">
                <div class="signature">
                    <div class="signature-title">Поставщик</div>
                    <div class="signature-line"></div>
                    <p>${data.supplier_name}</p>
                </div>
                <div class="signature">
                    <div class="signature-title">Покупатель</div>
                    <div class="signature-line"></div>
                    <p>${comp.director_name || props.accountant?.employee_name}</p>
                    <p>Директор ${comp.name || 'ООО "Косметологическая клиника"'}</p>
                </div>
            </div>
            
            <div class="footer">
                Документ сформирован автоматически ${currentDate}
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

const exportSupplyDocumentToExcel = () => {
    if (!selectedSupplyOrder.value) return;
    const data = selectedSupplyOrder.value;
    const excelData = [
        ['ДОКУМЕНТ ПОСТАВКИ'],
        [`№ ${data.number}`],
        [`Дата: ${formatDate(data.created_at)}`],
        [],
        ['Поставщик:', data.supplier_name],
        [],
        ['СПИСОК МАТЕРИАЛОВ'],
        ['№', 'Наименование', 'Кол-во', 'Ед. изм.', 'Цена', 'Сумма']
    ];
    data.items.forEach((item, idx) => {
        excelData.push([idx + 1, item.material_name, item.quantity, item.unit, item.price, item.total]);
    });
    excelData.push([], ['ИТОГО:', '', '', '', '', data.total_amount]);
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Поставка_${data.number}`);
    XLSX.writeFile(wb, `Поставка_${data.number}.xlsx`);
};

onMounted(() => {
    loadOrders();
});
</script>

<style scoped>
@keyframes slide-up {
    from { transform: translateY(100%); opacity: 0; }
    to { transform: translateY(0); opacity: 1; }
}
.animate-slide-up { animation: slide-up 0.3s ease-out; }
</style>