<script setup>
import { computed, onMounted, ref } from 'vue'

const subjects = ref([])
const loading = ref(true)
const error = ref('')
const selectedSubjects = ref([])
const formError = ref('')
const formSuccess = ref('')
const subjectForm = ref({
  name: '',
  level: 'middle',
  evaluation: '',
})

const subjectOptions = computed(() => {
  const names = subjects.value
    .map((subject) => {
      if (typeof subject === 'string') {
        return subject.trim()
      }

      if (subject && typeof subject === 'object' && typeof subject.name === 'string') {
        return subject.name.trim()
      }

      return ''
    })
    .filter(Boolean)

  return [...new Set(names)].sort((first, second) => first.localeCompare(second, 'hu'))
})

function normalizeBaseUrl(value) {
  if (typeof value !== 'string') {
    return ''
  }

  return value.trim().replace(/\/+$/, '')
}

function buildApiBaseUrls() {
  const configuredBaseUrl = normalizeBaseUrl(import.meta.env.VITE_API_BASE_URL)
  if (configuredBaseUrl) {
    return [configuredBaseUrl]
  }

  const origin = window.location.origin.replace(/:\d+$/, '')
  const candidates = [
    '',
    `${origin}:8000`,
    'http://127.0.0.1:8000',
    'http://localhost:8000',
  ]

  return [...new Set(candidates.map(normalizeBaseUrl))]
}

async function loadSubjects() {
  loading.value = true
  error.value = ''
  const baseUrls = buildApiBaseUrls()
  let lastError = null

  for (const baseUrl of baseUrls) {
    try {
      const endpoint = baseUrl ? `${baseUrl}/api/subjects` : '/api/subjects'
      const response = await fetch(endpoint)

      if (!response.ok) {
        throw new Error(`Hiba a lekérés során: ${response.status}`)
      }

      const data = await response.json()
      subjects.value = data.subjects || []
      loading.value = false
      return
    } catch (e) {
      lastError = e
    }
  }

  if (lastError instanceof TypeError) {
    error.value =
      'Nem érhető el a backend API. Indítsd el a backendet: "cd backend && composer start".'
  } else {
    error.value =
      lastError instanceof Error ? lastError.message : 'Ismeretlen hiba történt.'
  }

  loading.value = false
}

function formatLevel(level) {
  return level === 'advanced' ? 'Emelt szint' : 'Középszint'
}

function addSubjectForCalculation() {
  formError.value = ''
  formSuccess.value = ''

  const name = subjectForm.value.name.trim()
  if (!name) {
    formError.value = 'Válassz tantárgyat a legördülőből.'
    return
  }

  const evaluation = Number(subjectForm.value.evaluation)
  if (!Number.isInteger(evaluation) || evaluation < 0 || evaluation > 100) {
    formError.value = 'Az értékelés csak 0 és 100 közötti egész szám lehet.'
    return
  }

  selectedSubjects.value.push({
    name,
    level: subjectForm.value.level,
    evaluation,
  })

  subjectForm.value = {
    name: '',
    level: 'middle',
    evaluation: '',
  }

  formSuccess.value = 'A tantárgy bekerült a pontszámításhoz.'
}

function removeSubjectFromCalculation(index) {
  selectedSubjects.value.splice(index, 1)
  formError.value = ''
  formSuccess.value = 'A tantárgy törölve lett.'
}

onMounted(loadSubjects)
</script>

<template>
  <main class="page">
    <section class="card">
      <h1>Pontszámító</h1>

      <p v-if="loading">Betöltés...</p>
      <p v-else-if="error" class="error">{{ error }}</p>

      <div v-if="!loading && !error" class="section">
        <h2>Új tantárgy hozzáadása a pontszámításhoz</h2>
        <form class="calc-form" @submit.prevent="addSubjectForCalculation">
          <label class="field">
            <span>Tantárgy</span>
            <select v-model="subjectForm.name" required>
              <option value="" disabled>Válassz tantárgyat</option>
              <option v-for="name in subjectOptions" :key="name" :value="name">
                {{ name }}
              </option>
            </select>
          </label>

          <label class="field">
            <span>Szint</span>
            <select v-model="subjectForm.level">
              <option value="middle">Középszint</option>
              <option value="advanced">Emelt szint</option>
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

          <button class="button" type="submit">Tantárgy hozzáadása</button>
        </form>
        <p v-if="!subjectOptions.length" class="note">
          Jelenleg nincs választható tantárgy, mert a backend nem adott vissza listát.
        </p>

        <p v-if="formError" class="error">{{ formError }}</p>
        <p v-else-if="formSuccess" class="info">{{ formSuccess }}</p>
      </div>

      <div v-if="!loading && !error" class="section">
        <h2>Pontszámításhoz hozzáadott tantárgyak</h2>
        <p v-if="!selectedSubjects.length" class="note">Még nincs hozzáadott tantárgy.</p>
        <ul v-else class="subject-list">
          <li
            v-for="(selectedSubject, index) in selectedSubjects"
            :key="`${selectedSubject.name}-${selectedSubject.level}-${selectedSubject.evaluation}-${index}`"
            class="subject-item"
          >
            <div>
              <strong>{{ selectedSubject.name }}</strong>
              <small>{{ formatLevel(selectedSubject.level) }}</small>
            </div>
            <div class="subject-actions">
              <span>{{ selectedSubject.evaluation }}%</span>
              <button
                type="button"
                class="button button-danger button-small"
                @click="removeSubjectFromCalculation(index)"
              >
                Törlés
              </button>
            </div>
          </li>
        </ul>
      </div>
    </section>
  </main>
</template>
