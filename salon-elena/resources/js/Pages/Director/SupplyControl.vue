<!-- resources/js/Pages/Director/SupplyControl.vue -->
<template>
    <DirectorLayout 
        :director="director"
        :pageTitle="'КОНТРОЛЬ ПОСТАВОК'"
    >
        <div class="space-y-6">
            <!-- Фильтры по статусу -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg p-4 shadow">
                <div class="flex flex-wrap gap-3">
                    <button 
                        v-for="tab in tabs" 
                        :key="tab.value"
                        @click="selectedStatus = tab.value"
                        class="px-4 py-2 rounded-md transition"
                        :class="selectedStatus === tab.value 
                            ? 'bg-[#8b5cf6] text-white' 
                            : 'bg-gray-100 dark:bg-zinc-800 text-gray-700 dark:text-gray-300 hover:bg-gray-200 dark:hover:bg-zinc-700'"
                    >
                        {{ tab.label }}
                        <span v-if="tab.count > 0" class="ml-2 px-2 py-0.5 text-xs rounded-full bg-white/20">
                            {{ tab.count }}
                        </span>
                    </button>
                </div>
            </div>

            <!-- Таблица заказов -->
            <div class="bg-white dark:bg-zinc-900 rounded-lg shadow overflow-hidden">
                <div v-if="loading" class="text-center py-12">
                    <div class="inline-block animate-spin rounded-full h-8 w-8 border-b-2 border-[#8b5cf6]"></div>
                    <p class="mt-2 text-gray-500">Загрузка...</p>
                </div>

                <div v-else-if="filteredOrders.length === 0" class="text-center py-12 text-gray-500">
                    Нет заказов в этом статусе
                </div>

                <div v-else class="divide-y divide-gray-200 dark:divide-zinc-700">
                    <div v-for="order in filteredOrders" :key="order.id" class="p-6 hover:bg-gray-50 dark:hover:bg-zinc-800 transition">
                        <!-- Заголовок заказа -->
                        <div class="flex flex-wrap justify-between items-start gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-3">
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
                                <p class="text-sm text-gray-500 mt-1">
                                    Поставщик: {{ order.supplier_name }} | 
                                    Дата создания: {{ formatDate(order.created_at) }}
                                </p>
                            </div>
                            <div class="text-right">
                                <p class="text-2xl font-bold text-[#8b5cf6]">{{ formatPrice(order.total_amount) }}</p>
                                <p class="text-xs text-gray-500">Общая сумма заказа</p>
                            </div>
                        </div>

                        <!-- Состав заказа -->
                        <div class="mb-4">
                            <h4 class="text-sm font-medium text-gray-500 mb-2">Состав заказа:</h4>
                            <div class="flex flex-wrap gap-2">
                                <span v-for="(item, idx) in order.items" :key="idx"
                                      class="px-2 py-1 bg-gray-100 dark:bg-zinc-800 rounded-md text-sm">
                                    {{ item.material_name }}: {{ item.quantity }} {{ item.unit }} 
                                    ({{ formatPrice(item.price) }}/{{ item.unit }})
                                </span>
                            </div>
                        </div>

                        <!-- Кнопки действий -->
                        <div v-if="order.status === 0" class="flex flex-wrap gap-3 pt-2 border-t border-gray-100 dark:border-zinc-700">
                            <button 
                                @click="confirmOrder(order)"
                                :disabled="processingOrder === order.id"
                                class="px-4 py-2 bg-green-500 text-white rounded-md hover:bg-green-600 transition disabled:opacity-50 flex items-center gap-2"
                            >
                                <svg v-if="processingOrder === order.id" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span>✅ Подтвердить заказ</span>
                            </button>
                            <button 
                                @click="rejectOrder(order)"
                                :disabled="processingOrder === order.id"
                                class="px-4 py-2 bg-red-500 text-white rounded-md hover:bg-red-600 transition disabled:opacity-50 flex items-center gap-2"
                            >
                                <span>❌ Отклонить заказ</span>
                            </button>
                            <button 
                                @click="showDetails(order)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition"
                            >
                                📋 Детали
                            </button>
                            <!-- НОВАЯ КНОПКА - Документ для оплаты -->
                            <button 
                                @click="showPaymentDocument(order)"
                                class="px-4 py-2 border border-[#8b5cf6] text-[#8b5cf6] rounded-md hover:bg-[#8b5cf6]/10 transition flex items-center gap-2"
                            >
                                📄 Документ для оплаты
                            </button>
                        </div>

                        <div v-else class="flex flex-wrap gap-3 pt-2 border-t border-gray-100 dark:border-zinc-700">
                            <button 
                                @click="showDetails(order)"
                                class="px-4 py-2 border border-gray-300 text-gray-700 rounded-md hover:bg-gray-100 transition"
                            >
                                📋 Детали заказа
                            </button>
                            <button 
                                v-if="order.status === 2"
                                @click="showReceipt(order)"
                                class="px-4 py-2 border border-[#8b5cf6] text-[#8b5cf6] rounded-md hover:bg-[#8b5cf6]/10 transition"
                            >
                                📄 Документ поставки
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Модальное окно деталей заказа (без изменений) -->
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
                            <!-- Основная информация -->
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
                                    <p class="text-lg font-bold text-[#8b5cf6]">{{ formatPrice(selectedOrder.total_amount) }}</p>
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
                            
                            <!-- Таблица материалов -->
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
                                            <td class="px-4 py-2 text-right text-[#8b5cf6]">{{ formatPrice(selectedOrder.total_amount) }}</td>
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

        <!-- Модальное окно ДОКУМЕНТА ДЛЯ ОПЛАТЫ (новое) -->
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
                                <h2 class="text-2xl font-bold">ELENA Beauty Clinic</h2>
                                <p class="text-lg font-semibold mt-2">ДОГОВОР ПОСТАВКИ № {{ selectedPaymentDocument.number }}</p>
                                <p class="text-sm text-gray-500">г. {{ selectedPaymentDocument.city }} | {{ formatDate(selectedPaymentDocument.created_at) }}</p>
                            </div>

                            <!-- Информация о сторонах -->
                            <div class="grid grid-cols-2 gap-6 mb-8 pb-4 border-b">
                                <div>
                                    <h4 class="font-semibold mb-2">Поставщик:</h4>
                                    <p><strong>{{ selectedPaymentDocument.supplier_name }}</strong></p>
                                    <p class="text-sm mt-1">ИНН: {{ selectedPaymentDocument.supplier_inn || '—' }}</p>
                                    <p class="text-sm">Адрес: {{ selectedPaymentDocument.supplier_address || '—' }}</p>
                                    <p class="text-sm">Телефон: {{ selectedPaymentDocument.supplier_phone || '—' }}</p>
                                    <p class="text-sm">Email: {{ selectedPaymentDocument.supplier_email || '—' }}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Покупатель:</h4>
                                    <p><strong>ООО "ELENA Beauty Clinic"</strong></p>
                                    <p class="text-sm mt-1">ИНН: 1234567890</p>
                                    <p class="text-sm">Адрес: г. Москва, ул. Примерная, д. 1</p>
                                    <p class="text-sm">Телефон: +7 (999) 123-45-67</p>
                                    <p class="text-sm">Email: info@elena-clinic.ru</p>
                                    <p class="text-sm mt-2">В лице: Директора {{ director?.employee_name }}</p>
                                </div>
                            </div>

                            <!-- Предмет договора -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">1. ПРЕДМЕТ ДОГОВОРА</h4>
                                <p class="text-sm leading-relaxed">
                                    Поставщик обязуется передать в собственность Покупателя, а Покупатель обязуется принять и оплатить 
                                    материалы для косметологических процедур в ассортименте, количестве и по ценам, указанным в Спецификации 
                                    (Приложение №1), которая является неотъемлемой частью настоящего Договора.
                                </p>
                            </div>

                            <!-- Спецификация (таблица материалов) -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">Приложение №1. Спецификация</h4>
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
                                <p class="text-sm text-gray-500 mt-2">
                                    * НДС не облагается (упрощенная система налогообложения)
                                </p>
                            </div>

                            <!-- Условия оплаты -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">2. УСЛОВИЯ ОПЛАТЫ</h4>
                                <p class="text-sm leading-relaxed">
                                    Покупатель производит 100% предоплату в течение 5 (пяти) рабочих дней с момента подписания настоящего договора. 
                                    Оплата производится безналичным перечислением на расчетный счет Поставщика.
                                </p>
                            </div>

                            <!-- Сроки поставки -->
                            <div class="mb-6">
                                <h4 class="font-semibold mb-2">3. СРОКИ ПОСТАВКИ</h4>
                                <p class="text-sm leading-relaxed">
                                    Поставка товара осуществляется в течение 14 (четырнадцати) рабочих дней с момента поступления оплаты на расчетный счет Поставщика.
                                </p>
                            </div>

                            <!-- Реквизиты -->
                            <div class="grid grid-cols-2 gap-6 mt-8 pt-4 border-t">
                                <div>
                                    <h4 class="font-semibold mb-2">Реквизиты Поставщика</h4>
                                    <p class="text-sm">Наименование: {{ selectedPaymentDocument.supplier_name }}</p>
                                    <p class="text-sm">ИНН: {{ selectedPaymentDocument.supplier_inn || '—' }}</p>
                                    <p class="text-sm">Банк: {{ selectedPaymentDocument.bank_name || '—' }}</p>
                                    <p class="text-sm">БИК: {{ selectedPaymentDocument.bic || '—' }}</p>
                                    <p class="text-sm">Р/с: {{ selectedPaymentDocument.payment_account || '—' }}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Реквизиты Покупателя</h4>
                                    <p class="text-sm">ООО "ELENA Beauty Clinic"</p>
                                    <p class="text-sm">ИНН: 1234567890</p>
                                    <p class="text-sm">КПП: 123456789</p>
                                    <p class="text-sm">Банк: ПАО Сбербанк</p>
                                    <p class="text-sm">БИК: 044525225</p>
                                    <p class="text-sm">Р/с: 40702810123456789012</p>
                                </div>
                            </div>

                            <!-- Подписи -->
                            <div class="grid grid-cols-2 gap-8 mt-8 pt-4 border-t">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Поставщик</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ selectedPaymentDocument.supplier_name }}</p>
                                    <p class="text-xs text-gray-400 mt-1">(подпись, печать)</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Покупатель</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ director?.employee_name }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Директор ООО "ELENA Beauty Clinic"</p>
                                </div>
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

        <!-- Модальное окно документа поставки (полученные заказы) -->
        <Teleport to="body">
            <div v-if="showReceiptModal" class="fixed inset-0 z-50 overflow-y-auto">
                <div class="flex items-center justify-center min-h-screen px-4 py-8">
                    <div class="fixed inset-0 bg-black bg-opacity-50" @click="showReceiptModal = false"></div>
                    
                    <div class="relative bg-white dark:bg-zinc-900 rounded-lg max-w-4xl w-full max-h-[90vh] overflow-y-auto">
                        <div class="sticky top-0 bg-white dark:bg-zinc-900 border-b px-6 py-4 flex justify-between items-center">
                            <h3 class="text-xl font-semibold">Документ поставки №{{ selectedReceipt?.number }}</h3>
                            <button @click="showReceiptModal = false" class="text-gray-500 hover:text-gray-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                            </button>
                        </div>

                        <div class="p-6" id="receipt-content" v-if="selectedReceipt">
                            <!-- Содержимое документа поставки (как было) -->
                            <div class="text-center mb-8">
                                <h2 class="text-2xl font-bold">ELENA Beauty Clinic</h2>
                                <p class="text-gray-500">Документ поставки № {{ selectedReceipt.number }}</p>
                                <p class="text-sm text-gray-400">Дата формирования: {{ formatDateTime(selectedReceipt.created_at) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-500">Поставщик:</p>
                                    <p class="font-medium">{{ selectedReceipt.supplier_name }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-500">Дата поставки:</p>
                                    <p class="font-medium">{{ formatDate(selectedReceipt.received_at) || formatDate(selectedReceipt.confirmed_at) }}</p>
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
                                        <tr v-for="(item, idx) in selectedReceipt.items" :key="idx" class="border-t">
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
                                            <td class="px-4 py-2 text-right text-[#8b5cf6]">{{ formatPrice(selectedReceipt.total_amount) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>

                            <div class="grid grid-cols-2 gap-8 mt-8 pt-4 border-t">
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Поставщик</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ selectedReceipt.supplier_name }}</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-sm text-gray-500">Директор</p>
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ director?.employee_name }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="sticky bottom-0 bg-white dark:bg-zinc-900 border-t px-6 py-4 flex justify-end gap-3">
                            <button @click="printReceipt" 
                                    class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 transition">
                                🖨️ Печать
                            </button>
                            <button @click="exportReceiptToExcel" 
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
    </DirectorLayout>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue';
import axios from 'axios';
import DirectorLayout from '@/Layouts/DirectorLayout.vue';
import * as XLSX from 'xlsx';

const props = defineProps({
    director: Object,
    orders: Array
});

const ordersList = ref([]);
const loading = ref(false);
const selectedStatus = ref('all');
const processingOrder = ref(null);
const showDetailsModal = ref(false);
const showPaymentModal = ref(false);
const showReceiptModal = ref(false);
const selectedOrder = ref(null);
const selectedPaymentDocument = ref(null);
const selectedReceipt = ref(null);

const notification = ref({
    show: false,
    type: 'success',
    message: ''
});

const tabs = computed(() => {
    const counts = {
        0: ordersList.value.filter(o => o.status === 0).length,
        1: ordersList.value.filter(o => o.status === 1).length,
        2: ordersList.value.filter(o => o.status === 2).length,
        3: ordersList.value.filter(o => o.status === 3).length,
        all: ordersList.value.length
    };
    
    return [
        { value: 'all', label: 'Все заказы', count: counts.all },
        { value: 0, label: 'Ожидают подтверждения', count: counts[0] },
        { value: 1, label: 'В пути', count: counts[1] },
        { value: 2, label: 'Получены', count: counts[2] },
        { value: 3, label: 'Отклонены', count: counts[3] }
    ];
});

const filteredOrders = computed(() => {
    if (selectedStatus.value === 'all') return ordersList.value;
    return ordersList.value.filter(o => o.status === selectedStatus.value);
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

// Подтверждение заказа
const confirmOrder = async (order) => {
    if (!confirm(`Подтвердить заказ №${order.number} от поставщика ${order.supplier_name} на сумму ${formatPrice(order.total_amount)}?`)) return;
    
    processingOrder.value = order.id;
    try {
        const response = await axios.post(`/api/director/orders/${order.id}/confirm`);
        if (response.data.success) {
            showNotification('success', `Заказ №${order.number} подтвержден и отправлен в путь`);
            const index = ordersList.value.findIndex(o => o.id === order.id);
            if (index !== -1) {
                ordersList.value[index].status = 1;
                ordersList.value[index].status_text = 'В пути';
                ordersList.value[index].confirmed_at = new Date().toISOString();
            }
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при подтверждении заказа');
    } finally {
        processingOrder.value = null;
    }
};

// Отклонение заказа
const rejectOrder = async (order) => {
    if (!confirm(`Отклонить заказ №${order.number} от поставщика ${order.supplier_name}?`)) return;
    
    processingOrder.value = order.id;
    try {
        const response = await axios.post(`/api/director/orders/${order.id}/reject`);
        if (response.data.success) {
            showNotification('success', `Заказ №${order.number} отклонен`);
            const index = ordersList.value.findIndex(o => o.id === order.id);
            if (index !== -1) {
                ordersList.value[index].status = 3;
                ordersList.value[index].status_text = 'Отклонен';
            }
        }
    } catch (error) {
        showNotification('error', error.response?.data?.error || 'Ошибка при отклонении заказа');
    } finally {
        processingOrder.value = null;
    }
};

// Показать детали
const showDetails = (order) => {
    selectedOrder.value = order;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedOrder.value = null;
};

// НОВЫЙ МЕТОД - Показать документ для оплаты
const showPaymentDocument = async (order) => {
    try {
        const response = await axios.get(`/api/accountant/orders/${order.id}/document`);
        selectedPaymentDocument.value = response.data;
        showPaymentModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка при загрузке документа');
    }
};

// Печать документа для оплаты
const printPaymentDocument = () => {
    const printContent = document.getElementById('payment-document-content');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Договор поставки ${selectedPaymentDocument.value.number}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .document { max-width: 1000px; margin: 0 auto; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    th { background-color: #f5f5f5; }
                    .text-right { text-align: right; }
                    .text-center { text-align: center; }
                    @media print { button { display: none; } }
                </style>
            </head>
            <body>
                <div class="document">${printContent.innerHTML}</div>
                <script>window.onload = () => { window.print(); setTimeout(() => window.close(), 500); }<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
};

// Экспорт документа для оплаты в Excel
const exportPaymentDocumentToExcel = () => {
    if (!selectedPaymentDocument.value) return;
    
    const data = selectedPaymentDocument.value;
    const excelData = [
        ['ELENA Beauty Clinic'],
        ['ДОГОВОР ПОСТАВКИ'],
        [`№ ${data.number}`],
        [`г. ${data.city} | ${formatDate(data.created_at)}`],
        [],
        ['1. СТОРОНЫ ДОГОВОРА'],
        ['Поставщик:', data.supplier_name],
        ['ИНН Поставщика:', data.supplier_inn || '—'],
        ['Адрес Поставщика:', data.supplier_address || '—'],
        ['Телефон Поставщика:', data.supplier_phone || '—'],
        ['Email Поставщика:', data.supplier_email || '—'],
        [],
        ['Покупатель:', 'ООО "ELENA Beauty Clinic"'],
        ['ИНН Покупателя:', '1234567890'],
        ['Адрес Покупателя:', 'г. Москва, ул. Примерная, д. 1'],
        ['Директор:', props.director?.employee_name],
        [],
        ['2. СПЕЦИФИКАЦИЯ'],
        ['№', 'Наименование', 'Количество', 'Ед. изм.', 'Цена', 'Сумма']
    ];
    
    data.items.forEach((item, idx) => {
        excelData.push([
            idx + 1,
            item.material_name,
            item.quantity,
            item.unit,
            item.price,
            item.total
        ]);
    });
    
    excelData.push([], ['ИТОГО:', '', '', '', '', data.total_amount]);
    excelData.push([]);
    excelData.push(['3. РЕКВИЗИТЫ ПОСТАВЩИКА']);
    excelData.push(['Наименование:', data.supplier_name]);
    excelData.push(['ИНН:', data.supplier_inn || '—']);
    excelData.push(['Банк:', data.bank_name || '—']);
    excelData.push(['БИК:', data.bic || '—']);
    excelData.push(['Расчетный счет:', data.payment_account || '—']);
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    ws['!cols'] = [{ wch: 20 }, { wch: 35 }];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Договор_${data.number}`);
    XLSX.writeFile(wb, `Договор_поставки_${data.number}.xlsx`);
};

// Показать документ поставки (для полученных заказов)
const showReceipt = async (order) => {
    try {
        const response = await axios.get(`/api/accountant/orders/${order.id}/document`);
        selectedReceipt.value = response.data;
        showReceiptModal.value = true;
    } catch (error) {
        showNotification('error', 'Ошибка при загрузке документа');
    }
};

const printReceipt = () => {
    const printContent = document.getElementById('receipt-content');
    const printWindow = window.open('', '_blank');
    printWindow.document.write(`
        <html>
            <head>
                <title>Документ поставки ${selectedReceipt.value.number}</title>
                <style>
                    body { font-family: Arial, sans-serif; margin: 20px; }
                    .receipt { max-width: 1000px; margin: 0 auto; }
                    table { width: 100%; border-collapse: collapse; }
                    th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
                    th { background-color: #f5f5f5; }
                    .text-right { text-align: right; }
                    @media print { button { display: none; } }
                </style>
            </head>
            <body>
                <div class="receipt">${printContent.innerHTML}</div>
                <script>window.onload = () => { window.print(); setTimeout(() => window.close(), 500); }<\/script>
            </body>
        </html>
    `);
    printWindow.document.close();
};

const exportReceiptToExcel = () => {
    if (!selectedReceipt.value) return;
    
    const data = selectedReceipt.value;
    const excelData = [
        ['ELENA Beauty Clinic'],
        ['Документ поставки'],
        [`№ ${data.number}`],
        [`Дата формирования: ${formatDateTime(data.created_at)}`],
        [],
        ['ИНФОРМАЦИЯ О ПОСТАВЩИКЕ'],
        ['Наименование', data.supplier_name],
        ['ИНН', data.supplier_inn || '—'],
        ['Адрес', data.supplier_address || '—'],
        [],
        ['СПИСОК МАТЕРИАЛОВ'],
        ['№', 'Наименование', 'Количество', 'Ед. изм.', 'Цена', 'Сумма']
    ];
    
    data.items.forEach((item, idx) => {
        excelData.push([
            idx + 1,
            item.material_name,
            item.quantity,
            item.unit,
            item.price,
            item.total
        ]);
    });
    
    excelData.push([], ['Итого:', '', '', '', '', data.total_amount]);
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    ws['!cols'] = [{ wch: 5 }, { wch: 30 }, { wch: 10 }, { wch: 10 }, { wch: 12 }, { wch: 15 }];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Поставка_${data.number}`);
    XLSX.writeFile(wb, `Поставка_${data.number}.xlsx`);
};

onMounted(() => {
    ordersList.value = JSON.parse(JSON.stringify(props.orders));
});
</script>

<style scoped>
@keyframes slide-up {
    from {
        transform: translateY(100%);
        opacity: 0;
    }
    to {
        transform: translateY(0);
        opacity: 1;
    }
}

.animate-slide-up {
    animation: slide-up 0.3s ease-out;
}
</style>