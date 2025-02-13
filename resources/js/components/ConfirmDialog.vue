<template>
  <div v-if="show" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full flex items-center justify-center">
    <div class="bg-white p-5 rounded-lg shadow-lg max-w-sm">
      <h3 class="text-lg font-semibold mb-2">{{ title }}</h3>
      <p class="mb-4">{{ message }}</p>
      <div class="flex justify-end space-x-2">
        <button @click="$emit('cancel')" class="px-4 py-2 bg-red-500 text-white rounded hover:bg-red-600">
          Cancel
        </button>
        <button @click="$emit('confirm')" class="px-4 py-2 bg-green-500 text-white rounded hover:bg-green-600">
          OK
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    show: Boolean,
    title: String,
    message: String,
  },
  emits: ['confirm', 'cancel'],
}
</script>