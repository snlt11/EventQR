import './bootstrap';
import { createApp } from 'vue';
import FormBuilder from './components/FormBuilder.vue';
import FormHeader from './components/FormHeader.vue';
import QuestionField from './components/QuestionField.vue';
import PreviewQuestion from './components/PreviewQuestion.vue';
import PublishedForm from './components/PublishedForm.vue';

const app = createApp({});

// Register components
app.component('form-builder', FormBuilder);
app.component('form-header', FormHeader);
app.component('question-field', QuestionField);
app.component('preview-question', PreviewQuestion);
app.component('published-form', PublishedForm);

// Mount the app
app.mount('#app');
