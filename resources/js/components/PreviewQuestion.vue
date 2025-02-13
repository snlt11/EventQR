<template>
  <div class="mb-4">
    <label class="block font-bold mb-2">
      {{ question.text }}
      <span v-if="question.required" class="text-red-500">*</span>
    </label>
    <p v-if="question.description" class="text-sm text-gray-600 mb-2">{{ question.description }}</p>

    <div v-if="question.type === 'short_answer'">
      <input type="text" class="w-full p-2 border rounded" :required="question.required">
    </div>

    <div v-else-if="question.type === 'paragraph'">
      <textarea class="w-full p-2 border rounded" rows="3" :required="question.required"></textarea>
    </div>

    <div v-else-if="question.type === 'multiple_choice'">
      <div v-for="option in question.options" :key="option.id" class="mb-2">
        <label class="inline-flex items-center">
          <input type="radio" :name="'question_' + question.id" :value="option.text" :required="question.required">
          <span class="ml-2">{{ option.text }}</span>
        </label>
      </div>
    </div>

    <div v-else-if="question.type === 'checkbox'">
      <div v-for="option in question.options" :key="option.id" class="mb-2">
        <label class="inline-flex items-center">
          <input type="checkbox" :value="option.text">
          <span class="ml-2">{{ option.text }}</span>
        </label>
      </div>
    </div>

    <div v-else-if="question.type === 'dropdown'">
      <select class="w-full p-2 border rounded" :required="question.required">
        <option value="">Select an option</option>
        <option v-for="option in question.options" :key="option.id" :value="option.text">
          {{ option.text }}
        </option>
      </select>
    </div>
  </div>
</template>

<script>
export default {
  props: ['question'],
}
</script>