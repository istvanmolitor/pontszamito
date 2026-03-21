<script setup>
import { computed, onMounted, ref } from 'vue'

const subjects = ref([])
const loading = ref(true)
const error = ref('')
const selectedSubjects = ref([])
const selectedLanguageExams = ref([])
const formError = ref('')
const formSuccess = ref('')
const showSubjectModal = ref(false)
const showLanguageExamModal = ref(false)
const subjectForm = ref({
  name: '',
  level: 'middle',
  evaluation: '',
})
const languageExamForm = ref({
  language: '',
  examType: 'B2',
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

const languageOptions = computed(() => {
  return [
    { value: 'english', label: 'Angol' },
    { value: 'german', label: 'Német' },
    { value: 'french', label: 'Francia' },
    { value: 'spanish', label: 'Spanyol' },
    { value: 'italian', label: 'Olasz' },
    { value: 'russian', label: 'Orosz' },
  ]
})

const examTypeOptions = computed(() => {
  return [
    { value: 'B2', label: 'B2 (Középfok)' },
    { value: 'C2', label: 'C2 (Felsőfok)' },
  ]
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

function getLanguageLabel(languageValue) {
  const option = languageOptions.value.find((opt) => opt.value === languageValue)
  return option ? option.label : languageValue
}

function closeSubjectModal() {
  showSubjectModal.value = false
  formError.value = ''
}

function closeLanguageExamModal() {
  showLanguageExamModal.value = false
  formError.value = ''
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
  showSubjectModal.value = false
}

function removeSubjectFromCalculation(index) {
  selectedSubjects.value.splice(index, 1)
  formError.value = ''
  formSuccess.value = 'A tantárgy törölve lett.'
}

function addLanguageExamForCalculation() {
  formError.value = ''
  formSuccess.value = ''

  const language = languageExamForm.value.language.trim()
  if (!language) {
    formError.value = 'Válassz egy nyelvet a legördülőből.'
    return
  }

  const evaluation = Number(languageExamForm.value.evaluation)
  if (!Number.isInteger(evaluation) || evaluation < 0 || evaluation > 100) {
    formError.value = 'Az értékelés csak 0 és 100 közötti egész szám lehet.'
    return
  }

  selectedLanguageExams.value.push({
    language,
    examType: languageExamForm.value.examType,
    evaluation,
  })

  languageExamForm.value = {
    language: '',
    examType: 'B2',
    evaluation: '',
  }

  formSuccess.value = 'A nyelvvizsga bekerült a pontszámításhoz.'
  showLanguageExamModal.value = false
}

function removeLanguageExamFromCalculation(index) {
  selectedLanguageExams.value.splice(index, 1)
  formError.value = ''
  formSuccess.value = 'A nyelvvizsga törölve lett.'
}

async function calculateScore() {
  formError.value = ''
  formSuccess.value = ''

  if (selectedSubjects.value.length === 0 && selectedLanguageExams.value.length === 0) {
    formError.value = 'Legalább egy tantárgyat vagy nyelvvizsgát hozzá kell adni a pontszámítás előtt.'
    return
  }

  try {
    const baseUrls = buildApiBaseUrls()
    let lastError = null

    for (const baseUrl of baseUrls) {
      try {
        const endpoint = baseUrl ? `${baseUrl}/api/calculate` : '/api/calculate'
        const response = await fetch(endpoint, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify({
            subjects: selectedSubjects.value,
            languageExams: selectedLanguageExams.value,
          }),
        })

        if (!response.ok) {
          throw new Error(`Hiba a pontszámítás során: ${response.status}`)
        }

        const data = await response.json()
        formSuccess.value = 'A pontszámítás sikeres volt!'
        console.log('Pontszámítás eredménye:', data)
        return
      } catch (e) {
        lastError = e
      }
    }

    if (lastError instanceof TypeError) {
      formError.value = 'Nem érhető el a backend API.'
    } else {
      formError.value =
        lastError instanceof Error ? lastError.message : 'Ismeretlen hiba történt.'
    }
  } catch (e) {
    formError.value = 'Hiba a pontszámítás során.'
    console.error(e)
  }
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
        <h2>Tantárgy hozzáadása</h2>
        <button class="button" @click="showSubjectModal = true">
          + Új tantárgy hozzáadása
        </button>
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

      <div v-if="!loading && !error" class="section">
        <h2>Nyelvvizsga hozzáadása</h2>
        <button class="button" @click="showLanguageExamModal = true">
          + Új nyelvvizsga hozzáadása
        </button>
      </div>

      <div v-if="!loading && !error" class="section">
        <h2>Pontszámításhoz hozzáadott nyelvvizsgák</h2>
        <p v-if="!selectedLanguageExams.length" class="note">Még nincs hozzáadott nyelvvizsga.</p>
        <ul v-else class="subject-list">
          <li
            v-for="(languageExam, index) in selectedLanguageExams"
            :key="`${languageExam.language}-${languageExam.examType}-${languageExam.evaluation}-${index}`"
            class="subject-item"
          >
            <div>
              <strong>{{ getLanguageLabel(languageExam.language) }}</strong>
              <small>{{ languageExam.examType }}</small>
              <div style="margin-top: 4px; font-size: 0.9em; color: #666;">Értékelés: {{ languageExam.evaluation }}%</div>
            </div>
            <div class="subject-actions">
              <button
                type="button"
                class="button button-danger button-small"
                @click="removeLanguageExamFromCalculation(index)"
              >
                Törlés
              </button>
            </div>
          </li>
        </ul>
      </div>

      <div v-if="!loading && !error && (selectedSubjects.length > 0 || selectedLanguageExams.length > 0)" class="section">
        <button class="button button-primary" @click="calculateScore" style="font-size: 1.1em; padding: 10px 20px;">
          Pontszámítás
        </button>
      </div>
    </section>

    <!-- Subject Modal -->
    <div v-if="showSubjectModal" class="modal-overlay" @click="closeSubjectModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Új tantárgy hozzáadása</h2>
          <button class="modal-close" @click="closeSubjectModal">✕</button>
        </div>
        <div class="modal-body">
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

            <p v-if="!subjectOptions.length" class="note">
              Jelenleg nincs választható tantárgy, mert a backend nem adott vissza listát.
            </p>

            <p v-if="formError" class="error">{{ formError }}</p>

            <div class="modal-actions">
              <button class="button" type="submit">Hozzáadása</button>
              <button class="button button-secondary" type="button" @click="closeSubjectModal">
                Mégsem
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Language Exam Modal -->
    <div v-if="showLanguageExamModal" class="modal-overlay" @click="closeLanguageExamModal">
      <div class="modal" @click.stop>
        <div class="modal-header">
          <h2>Új nyelvvizsga hozzáadása</h2>
          <button class="modal-close" @click="closeLanguageExamModal">✕</button>
        </div>
        <div class="modal-body">
          <form class="calc-form" @submit.prevent="addLanguageExamForCalculation">
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

            <label class="field">
              <span>Értékelés (%)</span>
              <input
                v-model.number="languageExamForm.evaluation"
                type="number"
                min="0"
                max="100"
                step="1"
                placeholder="0-100"
                required
              />
            </label>

            <p v-if="formError" class="error">{{ formError }}</p>

            <div class="modal-actions">
              <button class="button" type="submit">Hozzáadása</button>
              <button class="button button-secondary" type="button" @click="closeLanguageExamModal">
                Mégsem
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>
</template>
