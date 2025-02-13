<template>
  <div class="max-w-3xl mx-auto bg-white shadow-lg rounded-lg overflow-hidden">
    <div class="bg-gray-100 py-4 px-6 border-b">
      <h2 class="text-2xl font-bold">{{ formData.title }}</h2>
    </div>
    <form @submit.prevent="submitForm" class="p-6">
      <div v-for="question in formData.questions" :key="question.id" class="mb-6">
        <label class="block font-medium mb-2">
          {{ question.text }}
          <span v-if="question.required" class="text-red-500">*</span>
        </label>

        <div v-if="question.type === 'short_answer'">
          <input 
            type="text" 
            v-model="responses[question.id]"
            class="w-full p-2 border rounded bg-gray-50"
            :required="question.required"
            :placeholder="'Please fill out this field.'"
          >
        </div>

        <div v-else-if="question.type === 'paragraph'">
          <textarea 
            v-model="responses[question.id]"
            class="w-full p-2 border rounded bg-gray-50"
            rows="3" 
            :required="question.required"
            :placeholder="'Please fill out this field.'"
          ></textarea>
        </div>

        <div v-else-if="question.type === 'multiple_choice'">
          <div v-for="option in question.options" :key="option.id" class="mb-2">
            <label class="inline-flex items-center">
              <input 
                type="radio" 
                :name="'question_' + question.id" 
                :value="option.text"
                v-model="responses[question.id]"
                :required="question.required"
                class="form-radio h-4 w-4 text-blue-600"
              >
              <span class="ml-2">{{ option.text }}</span>
            </label>
          </div>
        </div>

        <div v-else-if="question.type === 'checkbox'">
          <div v-for="option in question.options" :key="option.id" class="mb-2">
            <label class="inline-flex items-center">
              <input 
                type="checkbox" 
                :value="option.text"
                v-model="responses[question.id]"
                class="form-checkbox h-4 w-4 text-blue-600"
              >
              <span class="ml-2">{{ option.text }}</span>
            </label>
          </div>
        </div>

        <div v-else-if="question.type === 'dropdown'">
          <select 
            v-model="responses[question.id]"
            class="w-full p-2 border rounded bg-gray-50"
            :required="question.required"
          >
            <option value="">Select an option</option>
            <option v-for="option in question.options" :key="option.id" :value="option.text">
              {{ option.text }}
            </option>
          </select>
        </div>
      </div>
      <div class="mt-6">
        <button type="submit" class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600">
          Submit
        </button>
      </div>
    </form>
    
  </div>
</template>

<script>
import { ref } from 'vue';
import axios from 'axios';

export default {
  props: {
    formData: {
      type: Object,
      required: true
    }
  },
  setup(props) {
    const responses = ref({});

    // Initialize responses object
    props.formData.questions.forEach(question => {
      if (question.type === 'checkbox') {
        responses.value[question.id] = [];
      } else {
        responses.value[question.id] = '';
      }
    });

    const submitForm = async () => {
      try {
        
        const response = await axios.post(`/submit-form/${props.formData.id}`, responses.value);
        if (response.data.success) {
          alert('Form submitted successfully!');
          // Reset form after submission
          props.formData.questions.forEach(question => {
            if (question.type === 'checkbox') {
              responses.value[question.id] = [];
            } else {
              responses.value[question.id] = '';
            }
          });
        }
      } catch (error) {
        console.error('Error submitting form:', error);
        alert('An error occurred while submitting the form. Please try again.');
      }
    };

    return {
      responses,
      submitForm
    };
  }
};
</script>