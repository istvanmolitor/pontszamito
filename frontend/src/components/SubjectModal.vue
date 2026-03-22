<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  show: {
    type: Boolean,
    default: false,
  },
  subjectOptions: {
    type: Array,
    default: () => [],
  },
  subjectLevelOptions: {
    type: Array,
    default: () => [],
  },
})

const emit = defineEmits(['close', 'add'])

const subjectForm = ref({
  name: '',
  level: 'middle',
  evaluation: '',
})

const modalError = ref('')

watch(() => props.show, (newVal) => {
  if (newVal) {
    modalError.value = ''
    subjectForm.value = {
      name: '',
      level: props.subjectLevelOptions[0]?.value || 'middle',
      evaluation: '',
    }
  }
})

watch(() => props.subjectLevelOptions, (newOptions) => {
  if (newOptions.length > 0 && !newOptions.some(option => option.value === subjectForm.value.level)) {
    subjectForm.value.level = newOptions[0].value
  }
})

function closeModal() {
  modalError.value = ''
  emit('close')
}

function addSubject() {
  modalError.value = ''

  const name = subjectForm.value.name.trim()
  if (!name) {
    modalError.value = 'Válassz tantárgyat a legördülőből.'
    return
  }

  const evaluation = Number(subjectForm.value.evaluation)
  if (!Number.isInteger(evaluation) || evaluation < 0 || evaluation > 100) {
    modalError.value = 'Az értékelés csak 0 és 100 közötti egész szám lehet.'
    return
  }

  if (!subjectForm.value.level) {
    modalError.value = 'Válassz szintet a legördülőből.'
    return
  }

  emit('add', {
    name,
    level: subjectForm.value.level,
    evaluation,
  })

  subjectForm.value = {
    name: '',
    level: props.subjectLevelOptions[0]?.value || 'middle',
    evaluation: '',
  }
  modalError.value = ''
}
</script>

<template>
  <div v-if="show" class="modal-overlay" @click="closeModal">
    <div class="modal" @click.stop>
      <div class="modal-header">
        <h2>Új tantárgy hozzáadása</h2>
        <button class="modal-close" @click="closeModal">✕</button>
      </div>
      <div class="modal-body">
        <form class="calc-form" @submit.prevent="addSubject">
          <label class="field">
            <span>Tantárgy</span>
            <select v-model="subjectForm.name" required>
              <option value="" disabled>Válassz tantárgyat</option>
              <option v-for="subject in subjectOptions" :key="subject.value" :value="subject.value">
                {{ subject.label }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Szint</span>
            <select v-model="subjectForm.level">
              <option v-for="level in subjectLevelOptions" :key="level.value" :value="level.value">
                {{ level.label }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Értékelés (%)</span>
            <input
              v-model.number="subjectForm.evaluation"
              type="number"
              min="0"
              max="100"
              step="1"
              placeholder="0-100"
              required
            />
          </label>

          <p v-if="!subjectOptions.length" class="note">
            Jelenleg nincs választható tantárgy, mert a backend nem adott vissza listát.
          </p>

          <p v-if="!subjectLevelOptions.length" class="note">
            Jelenleg nincs választható szint, mert a backend nem adott vissza listát.
          </p>

          <p v-if="modalError" class="error">{{ modalError }}</p>

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

