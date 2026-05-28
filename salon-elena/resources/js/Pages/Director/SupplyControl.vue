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

        <!-- Модальное окно ДОКУМЕНТА ДЛЯ ОПЛАТЫ -->
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
                            <!-- Шапка документа с данными компании из пропса -->
                            <div class="text-center mb-8">
                                <img v-if="company?.logo_url" 
                                    :src="company.logo_url" 
                                    class="h-16 mx-auto mb-2 object-contain" />
                                <h2 class="text-2xl font-bold">{{ company?.name || 'Косметологическая клиника' }}</h2>
                                <p class="text-sm text-gray-500">{{ company?.legal_address || company?.actual_address || '' }}</p>
                                <p class="text-sm text-gray-500">ИНН: {{ company?.inn || '—' }} | ОГРН: {{ company?.ogrn || '—' }}</p>
                                <p class="text-sm text-gray-500">Тел.: {{ company?.phone || '' }} | Email: {{ company?.email || '' }}</p>
                                <p class="text-lg font-semibold mt-4">ДОГОВОР ПОСТАВКИ № {{ selectedPaymentDocument.number }}</p>
                                <p class="text-sm text-gray-500">г. {{ selectedPaymentDocument.city }} | {{ formatDate(selectedPaymentDocument.created_at) }}</p>
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
                                    <p><strong>{{ company?.name || 'ООО "Косметологическая клиника"' }}</strong></p>
                                    <p class="text-sm mt-1">ИНН: {{ company?.inn || '—' }}</p>
                                    <p class="text-sm">КПП: {{ company?.kpp || '—' }}</p>
                                    <p class="text-sm">ОГРН: {{ company?.ogrn || '—' }}</p>
                                    <p class="text-sm">Юр. адрес: {{ company?.legal_address || '—' }}</p>
                                    <p class="text-sm">Факт. адрес: {{ company?.actual_address || '—' }}</p>
                                    <p class="text-sm">Телефон: {{ company?.phone || '—' }}</p>
                                    <p class="text-sm">Email: {{ company?.email || '—' }}</p>
                                    <p class="text-sm mt-2">В лице: Директора <strong>{{ company?.director_name || director?.employee_name }}</strong></p>
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

                            <!-- Спецификация -->
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
                                <p class="text-sm text-gray-500 mt-2">* НДС не облагается (упрощенная система налогообложения)</p>
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
                                    <p class="text-sm">КПП: {{ selectedPaymentDocument.supplier_kpp || '—' }}</p>
                                    <p class="text-sm">Банк: {{ selectedPaymentDocument.bank_name || '—' }}</p>
                                    <p class="text-sm">БИК: {{ selectedPaymentDocument.bic || '—' }}</p>
                                    <p class="text-sm">Корр. счет: {{ selectedPaymentDocument.correspondent_account || '—' }}</p>
                                    <p class="text-sm">Р/с: {{ selectedPaymentDocument.payment_account || '—' }}</p>
                                </div>
                                <div>
                                    <h4 class="font-semibold mb-2">Реквизиты Покупателя</h4>
                                    <p class="text-sm">Наименование: {{ company?.name || '—' }}</p>
                                    <p class="text-sm">ИНН: {{ company?.inn || '—' }}</p>
                                    <p class="text-sm">КПП: {{ company?.kpp || '—' }}</p>
                                    <p class="text-sm">ОГРН: {{ company?.ogrn || '—' }}</p>
                                    
                                    <div v-if="company?.bank_details && company.bank_details.length > 0">
                                        <div v-for="(bank, idx) in company.bank_details" :key="idx" class="mt-2">
                                            <p class="text-sm font-medium mt-1">Банк {{ idx + 1 }}:</p>
                                            <p class="text-sm">{{ bank.bank_name || '—' }}</p>
                                            <p class="text-sm">БИК: {{ bank.bik || '—' }}</p>
                                            <p class="text-sm">Корр. счет: {{ bank.correspondent_account || '—' }}</p>
                                            <p class="text-sm">Р/с: {{ bank.payment_account || '—' }}</p>
                                        </div>
                                    </div>
                                    <div v-else>
                                        <p class="text-sm">Банк: —</p>
                                        <p class="text-sm">БИК: —</p>
                                        <p class="text-sm">Р/с: —</p>
                                    </div>
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
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ company?.director_name || director?.employee_name }}</p>
                                    <p class="text-xs text-gray-400 mt-1">Директор {{ company?.name || 'ООО "Косметологическая клиника"' }}</p>
                                    <img v-if="company?.stamp_url" 
                                        :src="company.stamp_url" 
                                        class="h-16 mx-auto mt-2 opacity-50" />
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

        <!-- Модальное окно документа поставки -->
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
                            <div class="text-center mb-8">
                                <img v-if="company?.logo_url" 
                                    :src="company.logo_url" 
                                    class="h-16 mx-auto mb-2 object-contain" />
                                <h2 class="text-2xl font-bold">{{ company?.name || 'Косметологическая клиника' }}</h2>
                                <p class="text-gray-500">Документ поставки № {{ selectedReceipt.number }}</p>
                                <p class="text-sm text-gray-400">Дата формирования: {{ formatDateTime(selectedReceipt.created_at) }}</p>
                            </div>

                            <div class="grid grid-cols-2 gap-4 mb-6 pb-4 border-b">
                                <div>
                                    <p class="text-sm text-gray-500">Поставщик:</p>
                                    <p class="font-medium">{{ selectedReceipt.supplier_name }}</p>
                                    <p class="text-sm">ИНН: {{ selectedReceipt.supplier_inn || '—' }}</p>
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
                                    <p class="mt-6 pt-4 border-t border-gray-300">{{ company?.director_name || director?.employee_name }}</p>
                                    <img v-if="company?.stamp_url" 
                                        :src="company.stamp_url" 
                                        class="h-16 mx-auto mt-2 opacity-50" />
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
    orders: Array,
    company: Object
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

const showDetails = (order) => {
    selectedOrder.value = order;
    showDetailsModal.value = true;
};

const closeDetailsModal = () => {
    showDetailsModal.value = false;
    selectedOrder.value = null;
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

// Печать документа для оплаты
const printPaymentDocument = () => {
    const comp = props.company || {};
    const data = selectedPaymentDocument.value;
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
                        <p>В лице: Директора <strong>${comp.director_name || props.director?.employee_name}</strong></p>
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
                        <p>${comp.director_name || props.director?.employee_name}</p>
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
    const comp = props.company || {};
    
    const excelData = [
        [comp.name || 'Косметологическая клиника'],
        ['ДОГОВОР ПОСТАВКИ'],
        [`№ ${data.number}`],
        [`г. ${data.city} | ${formatDate(data.created_at)}`],
        [],
        ['1. СТОРОНЫ ДОГОВОРА'],
        ['Поставщик:', data.supplier_name],
        ['ИНН Поставщика:', data.supplier_inn || '—'],
        ['КПП Поставщика:', data.supplier_kpp || '—'],
        ['Адрес Поставщика:', data.supplier_address || '—'],
        ['Телефон Поставщика:', data.supplier_phone || '—'],
        ['Email Поставщика:', data.supplier_email || '—'],
        [],
        ['Покупатель:', comp.name || '—'],
        ['ИНН Покупателя:', comp.inn || '—'],
        ['КПП Покупателя:', comp.kpp || '—'],
        ['ОГРН Покупателя:', comp.ogrn || '—'],
        ['Юр. адрес:', comp.legal_address || '—'],
        ['Факт. адрес:', comp.actual_address || '—'],
        ['Директор:', comp.director_name || props.director?.employee_name],
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
    excelData.push(['Корр. счет:', data.correspondent_account || '—']);
    excelData.push(['Расчетный счет:', data.payment_account || '—']);
    excelData.push([]);
    excelData.push(['4. РЕКВИЗИТЫ ПОКУПАТЕЛЯ']);
    excelData.push(['Наименование:', comp.name || '—']);
    excelData.push(['ИНН:', comp.inn || '—']);
    excelData.push(['КПП:', comp.kpp || '—']);
    excelData.push(['ОГРН:', comp.ogrn || '—']);
    
    if (comp.bank_details && comp.bank_details.length > 0) {
        comp.bank_details.forEach((bank, idx) => {
            excelData.push([`Банк ${idx + 1}:`, bank.bank_name || '—']);
            excelData.push(['БИК:', bank.bik || '—']);
            excelData.push(['Корр. счет:', bank.correspondent_account || '—']);
            excelData.push(['Расчетный счет:', bank.payment_account || '—']);
        });
    }
    
    const ws = XLSX.utils.aoa_to_sheet(excelData);
    ws['!cols'] = [{ wch: 25 }, { wch: 40 }];
    const wb = XLSX.utils.book_new();
    XLSX.utils.book_append_sheet(wb, ws, `Договор_${data.number}`);
    XLSX.writeFile(wb, `Договор_поставки_${data.number}.xlsx`);
};

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
    const comp = props.company || {};
    const data = selectedReceipt.value;
    
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
                table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                th, td { border: 1px solid #000; padding: 6px 8px; text-align: left; }
                th { background: #f5f5f5; text-align: center; }
                .text-right { text-align: right; }
                .text-center { text-align: center; }
                .signatures { display: flex; justify-content: space-between; margin-top: 30px; }
                .signature { text-align: center; flex: 1; }
                .signature-line { margin-top: 30px; padding-top: 5px; border-top: 1px solid #000; }
                @media print { body { padding: 15mm; } }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="company-name">${comp.name || 'Косметологическая клиника'}</div>
                <p>Документ поставки № ${data.number}</p>
                <p>Дата формирования: ${formatDateTime(data.created_at)}</p>
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
                <div class="signature"><div class="signature-line"></div><p>${data.supplier_name}</p><p>Поставщик</p></div>
                <div class="signature"><div class="signature-line"></div><p>${comp.director_name || props.director?.employee_name}</p><p>Директор</p></div>
            </div>
        </body>
        </html>
    `);
    printWindow.document.close();
    printWindow.print();
};

const exportReceiptToExcel = () => {
    if (!selectedReceipt.value) return;
    
    const data = selectedReceipt.value;
    const excelData = [
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