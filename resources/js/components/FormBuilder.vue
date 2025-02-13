<template>
    <div class="bg-white shadow-lg rounded-lg p-6 max-w-4xl mx-auto mt-8">
        <div v-if="!previewMode">
            <form-header v-model:title="formData.title"></form-header>
            <div
                v-if="statusMessage"
                class="mt-4 p-4 bg-blue-100 text-blue-700 rounded"
            >
                {{ statusMessage }}
            </div>
            <div class="mt-8">
                <!-- Default questions -->
                <question-field
                    v-for="question in defaultQuestions"
                    :key="question.id"
                    :question="question"
                    :is-default="true"
                ></question-field>

                <!-- Draggable custom questions -->
                <draggable
                    v-model="customQuestions"
                    group="questions"
                    item-key="id"
                    handle=".drag-handle"
                    ghost-class="opacity-50"
                    @start="drag = true"
                    @end="drag = false"
                >
                    <template #item="{ element }">
                        <question-field
                            :question="element"
                            :is-default="false"
                            @duplicate="duplicateQuestion"
                            @delete="deleteQuestion"
                        ></question-field>
                    </template>
                </draggable>
                <button
                    @click="addQuestion"
                    class="mt-4 bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Add Question
                </button>
            </div>

            <div class="mt-8 flex justify-end space-x-4">
                <button
                    class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
                >
                    Save
                </button>
                <button
                    @click="togglePreview"
                    class="bg-green-500 text-white px-4 py-2 rounded hover:bg-green-600"
                >
                    Preview
                </button>
                <button
                    @click="publishForm"
                    class="bg-blue-500 text-white px-4 py-2 rounded hover:bg-blue-600"
                >
                    Publish
                </button>
            </div>
        </div>

        <div v-else class="preview-mode">
            <h2 class="text-2xl font-bold mb-4">{{ formData.title }}</h2>
            <form @submit.prevent="submitForm">
                <div
                    v-for="question in formData.questions"
                    :key="question.id"
                    class="mb-4"
                >
                    <preview-question :question="question"></preview-question>
                </div>
                <div class="mt-4">
                    <button
                        @click="togglePreview"
                        class="bg-gray-300 text-gray-700 px-4 py-2 rounded hover:bg-gray-400"
                    >
                        Back to Edit
                    </button>
                </div>
            </form>
        </div>
    </div>
    
</template>

<script>
import { ref, computed } from "vue";
import FormHeader from "./FormHeader.vue";
import QuestionField from "./QuestionField.vue";
import PreviewQuestion from "./PreviewQuestion.vue";
import draggable from "vuedraggable";

export default {
    components: {
        FormHeader,
        QuestionField,
        PreviewQuestion,
        draggable,
    },
    setup() {
        const formData = ref({
            title: "Event Registration",
            questions: [
                {
                    id: "name",
                    text: "Name",
                    type: "short_answer",
                    required: true,
                    options: [],
                    isDefault: true,
                },
                {
                    id: "email",
                    text: "Email",
                    type: "short_answer",
                    required: true,
                    options: [],
                    isDefault: true,
                },
            ],
        });

        const defaultQuestions = computed(() =>
            formData.value.questions.filter((q) => q.isDefault)
        );
        const customQuestions = computed({
            get: () => formData.value.questions.filter((q) => !q.isDefault),
            set: (value) => {
                formData.value.questions = [
                    ...defaultQuestions.value,
                    ...value,
                ];
            },
        });
        const previewMode = ref(false);
        const drag = ref(false);
        const statusMessage = ref("");

        const addQuestion = () => {
            formData.value.questions.push({
                id: Date.now(),
                text: "New Question",
                type: "short_answer",
                required: false,
                options: [],
                isDefault: false,
            });
        };

        const duplicateQuestion = (question) => {
            const newQuestion = JSON.parse(JSON.stringify(question));
            newQuestion.id = Date.now();
            newQuestion.isDefault = false;
            formData.value.questions.push(newQuestion);
        };

        const deleteQuestion = (id) => {
            formData.value.questions = formData.value.questions.filter(
                (q) => q.id !== id || q.isDefault
            );
        };

        const togglePreview = () => {
            previewMode.value = !previewMode.value;
        };

        const submitForm = () => {
            // Handle form submission logic here
            console.log("Form submitted:", formData.value);
        };

        const publishForm = async () => {
            try {
                statusMessage.value = "Publishing form...";
                const response = await axios.post(
                    "/publish-form",
                    formData.value
                );
                if (response.data.success) {
                    statusMessage.value = `Form published successfully! URL: ${response.data.url}`;
                } else {
                    statusMessage.value =
                        "Failed to publish form: " + response.data.message;
                }
            } catch (error) {
                console.error("Error publishing form:", error);
                if (error.response) {
                    console.error("Error response:", error.response.data);
                    statusMessage.value =
                        "Server error: " +
                        (error.response.data.message || "Unknown error");
                } else if (error.request) {
                    console.error("No response received:", error.request);
                    statusMessage.value =
                        "No response from server. Please try again.";
                } else {
                    console.error("Error setting up request:", error.message);
                    statusMessage.value =
                        "Error setting up request: " + error.message;
                }
            }
        };

        return {
            formData,
            defaultQuestions,
            customQuestions,
            previewMode,
            drag,
            addQuestion,
            duplicateQuestion,
            deleteQuestion,
            togglePreview,
            submitForm,
            publishForm,
            statusMessage,
        };
    },
};
</script>
