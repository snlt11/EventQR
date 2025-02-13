<template>
  <div class="bg-white border border-gray-300 rounded-lg p-4 mb-4 relative">
    <div v-if="!isDefault" class="drag-handle cursor-move absolute top-2 right-2">
      <i class="fas fa-grip-vertical text-gray-400"></i>
    </div>
    <input
      v-model="question.text"
      :disabled="isDefault"
      class="text-xl w-full mb-2 p-2 border-b-2 border-gray-300 focus:border-blue-500 outline-none"
      placeholder="Question"
    />
    <input
      v-if="!isDefault"
      v-model="question.description"
      class="w-full mb-2 p-2 border-b border-gray-300 focus:border-blue-500 outline-none text-sm text-gray-600"
      placeholder="Question description (optional)"
    />
    <div class="mb-2">
      <select
        v-model="question.type"
        :disabled="isDefault"
        class="p-2 border border-gray-300 rounded focus:outline-none focus:border-blue-500"
      >
        <option value="short_answer">Short answer</option>
        <option value="paragraph">Paragraph</option>
        <option value="multiple_choice">Multiple choice</option>
        <option value="checkbox">Checkbox</option>
        <option value="dropdown">Dropdown</option>
      </select>
    </div>
    <div v-if="['checkbox', 'dropdown'].includes(question.type)" class="mb-2">
      <div v-for="(option, index) in question.options" :key="index" class="flex items-center mb-1">
        <input
          v-model="option.text"
          class="p-2 border rounded flex-grow"
          placeholder="Option"
        />
        <button @click="removeOption(index)" class="ml-2 text-red-500">
          <i class="fas fa-trash"></i>
        </button>
      </div>
      <button @click="addOption" class="text-blue-500">
        <i class="fas fa-plus mr-1"></i> Add Option
      </button>
    </div>
    <div v-if="question.type === 'multiple_choice'" class="mt-2">
      <div v-for="(option, index) in question.options" :key="index" class="flex items-center mb-1">
        <input
          v-model="option.text"
          class="p-2 border rounded flex-grow"
          placeholder="Option"
        />
        <button @click="removeOption(index)" class="ml-2 text-red-500">
          <i class="fas fa-trash"></i>
        </button>
      </div>
      <button @click="addOption" class="text-blue-500">
        <i class="fas fa-plus mr-1"></i> Add Option
      </button>
    </div>
    <div class="flex items-center justify-between mt-4">
      <label class="flex items-center">
        <input type="checkbox" v-model="question.required" :disabled="isDefault" class="mr-2" />
        Required
      </label>
      <div v-if="!isDefault">
        <button @click="$emit('duplicate', question)" class="text-blue-500 mr-2">
          <i class="fas fa-copy"></i>
        </button>
        <button @click="$emit('delete', question.id)" class="text-red-500">
          <i class="fas fa-trash"></i>
        </button>
      </div>
    </div>
  </div>
</template>

<script>
export default {
  props: {
    question: {
      type: Object,
      required: true
    },
    isDefault: {
      type: Boolean,
      default: false
    }
  },
  emits: ['duplicate', 'delete'],
  methods: {
    addOption() {
      if (!this.question.options) {
        this.$set(this.question, 'options', [])
      }
      this.question.options.push({ text: '' })
    },
    removeOption(index) {
      this.question.options.splice(index, 1)
    }
  },
  watch: {
    'question.type': {
      immediate: true,
      handler(newType) {
        if (['multiple_choice', 'checkbox', 'dropdown'].includes(newType) && !this.question.options) {
          this.$set(this.question, 'options', [{ text: '' }])
        }
      }
    }
  }
}
</script>