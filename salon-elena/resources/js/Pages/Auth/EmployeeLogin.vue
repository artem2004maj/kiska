<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100">
    <div class="bg-white p-8 rounded-xl shadow-xl w-full max-w-md">
      <h2 class="text-2xl font-bold text-center mb-6">Вход для сотрудников</h2>

      <form @submit.prevent="submit" class="space-y-6">
        <div>
          <label class="block text-sm font-medium">Логин</label>
          <input v-model="form.login" type="text" required
                 class="mt-1 block w-full rounded-md border-gray-300">
        </div>

        <div>
          <label class="block text-sm font-medium">Пароль</label>
          <input v-model="form.password" type="password" required
                 class="mt-1 block w-full rounded-md border-gray-300">
        </div>

        <button type="submit"
                class="w-full bg-blue-600 text-white py-3 rounded-lg hover:bg-blue-700 transition">
          Войти
        </button>
      </form>

      <div class="mt-6 text-center">
        <a href="/" class="text-blue-600 hover:underline">Вернуться на главную</a>
      </div>
    </div>
  </div>
</template>

<script setup>
import { useForm } from '@inertiajs/vue3'

const form = useForm({
  login: '',
  password: '',
})

const submit = () => {
  form.post('/employee/login')
}
</script>