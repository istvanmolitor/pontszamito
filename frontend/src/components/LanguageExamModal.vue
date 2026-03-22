<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  languageOptions: {
    type: Array,
    default: () => [],
  },
  examTypeOptions: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'add'])

const languageExamForm = ref({
  language: '',
  examType: 'B2',
})

const modalError = ref('')

watch(() => props.show, (newVal) => {
  if (newVal) {
    modalError.value = ''
    languageExamForm.value = {
      language: '',
      examType: props.examTypeOptions[0]?.value || 'B2',
    }
  }
})

watch(() => props.examTypeOptions, (newOptions) => {
  if (newOptions.length > 0 && !newOptions.some(option => option.value === languageExamForm.value.examType)) {
    languageExamForm.value.examType = newOptions[0].value
  }
})

function closeModal() {
  modalError.value = ''
  emit('close')
}

function addLanguageExam() {
  modalError.value = ''

  const language = languageExamForm.value.language.trim()
  if (!language) {
    modalError.value = 'Válassz egy nyelvet a legördülőből.'
    return
  }

  if (!languageExamForm.value.examType) {
    modalError.value = 'Válassz nyelvvizsga-szintet a legördülőből.'
    return
  }

  emit('add', {
    language,
    examType: languageExamForm.value.examType,
  })

  languageExamForm.value = {
    language: '',
    examType: props.examTypeOptions[0]?.value || 'B2',
  }
  modalError.value = ''
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h2>Új nyelvvizsga hozzáadása</h2>
        <button class="modal-close" @click="closeModal">✕</button>
      </div>
      <div class="modal-body">
        <form class="calc-form" @submit.prevent="addLanguageExam">
          <label class="field">
            <span>Nyelv</span>
            <select v-model="languageExamForm.language" required>
              <option value="" disabled>Válassz egy nyelvet</option>
              <option v-for="lang in languageOptions" :key="lang.value" :value="lang.value">
                {{ lang.label }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Szint</span>
            <select v-model="languageExamForm.examType">
              <option v-for="examType in examTypeOptions" :key="examType.value" :value="examType.value">
                {{ examType.label }}
              </option>
            </select>
          </label>

          <p v-if="modalError" class="error">{{ modalError }}</p>

          <p v-if="!languageOptions.length" class="note">
            Jelenleg nincs választható nyelv, mert a backend nem adott vissza listát.
          </p>

          <p v-if="!examTypeOptions.length" class="note">
            Jelenleg nincs választható nyelvvizsga-szint, mert a backend nem adott vissza listát.
          </p>

          <div class="modal-actions">
            <button class="button" type="submit">Hozzáadása</button>
            <button class="button button-secondary" type="button" @click="closeModal">
              Mégsem
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

