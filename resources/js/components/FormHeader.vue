<template>
  <div class="mb-6">
    <input
      v-model="title"
      class="text-3xl font-bold w-full mb-2 p-2 border-b-2 border-gray-300 focus:border-blue-500 outline-none"
      placeholder="Form Title"
      @input="$emit('update:title', title)"
    />
  </div>
</template>

<script>
import { ref, watch } from 'vue'

export default {
  props: ['title'],
  emits: ['update:title'],
  setup(props, { emit }) {
    const title = ref(props.title)

    watch(() => props.title, (newTitle) => {
      title.value = newTitle
    })

    return { title }
  }
}
</script>