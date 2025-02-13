<template>
  <div class="bg-white border border-gray-300 rounded-lg p-4">
    <h3 class="text-lg font-semibold mb-4">Add Question</h3>
    <div class="space-y-2">
      <div
        v-for="option in questionOptions"
        :key="option.type"
        class="p-2 bg-gray-100 rounded cursor-move hover:bg-gray-200 transition-colors duration-200"
        draggable="true"
        @dragstart="dragStart($event, option.type)"
      >
        <i :class="option.icon" class="mr-2"></i>
        {{ option.label }}
      </div>
    </div>
  </div>
</template>

<script>
export default {
  emits: ['add-question'],
  setup(props, { emit }) {
    const questionOptions = [
      { type: 'short_answer', label: 'Short Answer', icon: 'fas fa-font' },
      { type: 'paragraph', label: 'Paragraph', icon: 'fas fa-paragraph' },
      { type: 'multiple_choice', label: 'Multiple Choice', icon: 'fas fa-list-ul' },
      { type: 'checkbox', label: 'Checkbox', icon: 'fas fa-check-square' },
      { type: 'dropdown', label: 'Dropdown', icon: 'fas fa-caret-square-down' },
    ]

    const dragStart = (event, questionType) => {
      event.dataTransfer.setData('text/plain', questionType)
      emit('add-question', questionType)
    }

    return {
      questionOptions,
      dragStart,
    }
  },
}
</script>