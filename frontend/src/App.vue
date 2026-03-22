<script setup>
import { computed, onMounted, ref } from 'vue'
import SubjectModal from './components/SubjectModal.vue'
import LanguageExamModal from './components/LanguageExamModal.vue'

const subjects = ref([])
const subjectLevelOptions = ref([])
const languageOptions = ref([])
const examTypeOptions = ref([])
const universityPrograms = ref([])
const selectedUniversityProgram = ref('')
const loading = ref(true)
const error = ref('')
const selectedSubjects = ref([])
const selectedLanguageExams = ref([])
const formError = ref('')
const formSuccess = ref('')
const totalScore = ref(null)
const showSubjectModal = ref(false)
const showLanguageExamModal = ref(false)

const subjectOptions = computed(() => {
  if (!Array.isArray(subjects.value)) {
    return []
  }

  return subjects.value
    .filter((subject) => subject && typeof subject === 'object' && subject.value && subject.label)
    .sort((first, second) => first.label.localeCompare(second.label, 'hu'))
})

const universityProgramOptions = computed(() => {
  if (!Array.isArray(universityPrograms.value)) {
    return []
  }

  return universityPrograms.value
    .filter((program) => program && typeof program === 'object' && program.value && program.label)
    .sort((first, second) => first.label.localeCompare(second.label, 'hu'))
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

async function fetchApiJson(path, requestInit) {
  const baseUrls = buildApiBaseUrls()
  let lastError = null

  for (const baseUrl of baseUrls) {
    try {
      const endpoint = baseUrl ? `${baseUrl}${path}` : path
      const response = await fetch(endpoint, requestInit)

      if (!response.ok) {
        try {
          const errorData = await response.json()
          const errorMessage = errorData.error || `Hiba a lekérés során: ${response.status}`
          lastError = new Error(errorMessage)
        } catch (e) {
          lastError = new Error(`Hiba a lekérés során: ${response.status}`)
        }
        continue
      }

      return await response.json()
    } catch (e) {
      lastError = e
    }
  }

  throw lastError ?? new Error('Ismeretlen hiba történt.')
}

function getOptionLabel(options, value, fallback = value) {
  const option = options.find((item) => item.value === value)
  return option ? option.label : fallback
}


async function loadOptions() {
  loading.value = true
  error.value = ''

  try {
    const [subjectData, examLevelData, languageExamData, universityProgramData] = await Promise.all([
      fetchApiJson('/api/subjects'),
      fetchApiJson('/api/exam-levels'),
      fetchApiJson('/api/language-exams'),
      fetchApiJson('/api/university-programs'),
    ])

    subjects.value = Array.isArray(subjectData.subjects) ? subjectData.subjects : []
    subjectLevelOptions.value = Array.isArray(examLevelData.levels) ? examLevelData.levels : []
    languageOptions.value = Array.isArray(languageExamData.languages)
      ? languageExamData.languages
      : []
    examTypeOptions.value = Array.isArray(languageExamData.examTypes)
      ? languageExamData.examTypes
      : []
    universityPrograms.value = Array.isArray(universityProgramData.programs)
      ? universityProgramData.programs
      : []
  } catch (lastError) {
    if (lastError instanceof TypeError) {
      error.value =
        'Nem érhető el a backend API. Indítsd el a backendet: "cd backend && composer start".'
    } else {
      error.value =
        lastError instanceof Error ? lastError.message : 'Ismeretlen hiba történt.'
    }
  } finally {
    loading.value = false
  }
}

function formatLevel(level) {
  return getOptionLabel(subjectLevelOptions.value, level, level)
}

function getSubjectLabel(subjectValue) {
  return getOptionLabel(subjects.value, subjectValue, subjectValue)
}

function getLanguageLabel(languageValue) {
  return getOptionLabel(languageOptions.value, languageValue, languageValue)
}

function getExamTypeLabel(examTypeValue) {
  return getOptionLabel(examTypeOptions.value, examTypeValue, examTypeValue)
}

function closeSubjectModal() {
  showSubjectModal.value = false
}

function closeLanguageExamModal() {
  showLanguageExamModal.value = false
}

function addSubjectForCalculation(subjectData) {
  formError.value = ''
  formSuccess.value = ''

  // Check if the same subject with the same level already exists
  const isDuplicate = selectedSubjects.value.some(
    (subject) => subject.name === subjectData.name && subject.level === subjectData.level
  )

  if (isDuplicate) {
    formError.value = 'Ez a tantárgy ugyanezen a szinten már hozzá van adva.'
    showSubjectModal.value = false
    return
  }

  selectedSubjects.value.push(subjectData)

  totalScore.value = null
  formSuccess.value = 'A tantárgy bekerült a pontszámításhoz.'
  showSubjectModal.value = false
}

function removeSubjectFromCalculation(index) {
  selectedSubjects.value.splice(index, 1)
  totalScore.value = null
  formError.value = ''
  formSuccess.value = 'A tantárgy törölve lett.'
}

function addLanguageExamForCalculation(languageExamData) {
  formError.value = ''
  formSuccess.value = ''

  selectedLanguageExams.value.push(languageExamData)

  totalScore.value = null
  formSuccess.value = 'A nyelvvizsga bekerült a pontszámításhoz.'
  showLanguageExamModal.value = false
}

function removeLanguageExamFromCalculation(index) {
  selectedLanguageExams.value.splice(index, 1)
  totalScore.value = null
  formError.value = ''
  formSuccess.value = 'A nyelvvizsga törölve lett.'
}

async function calculateScore() {
  formError.value = ''
  formSuccess.value = ''
  totalScore.value = null

  if (!selectedUniversityProgram.value) {
    formError.value = 'Válassz egy egyetemi szakot a pontszámítás előtt.'
    return
  }

  if (selectedSubjects.value.length === 0 && selectedLanguageExams.value.length === 0) {
    formError.value = 'Legalább egy tantárgyat vagy nyelvvizsgát hozzá kell adni a pontszámítás előtt.'
    return
  }

  try {
    const data = await fetchApiJson('/api/calculate', {
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
      },
      body: JSON.stringify({
        universityProgram: selectedUniversityProgram.value,
        subjects: selectedSubjects.value,
        languageExams: selectedLanguageExams.value,
      }),
    })

    totalScore.value = data.totalScore
    formSuccess.value = 'A pontszámítás sikeres volt!'
    console.log('Pontszámítás eredménye:', data)
  } catch (e) {
    totalScore.value = null
    if (e instanceof TypeError) {
      formError.value = 'Nem érhető el a backend API.'
    } else {
      formError.value = e instanceof Error ? e.message : 'Hiba a pontszámítás során.'
    }
  }
}

onMounted(loadOptions)
</script>

<template>
  <main class="page">
    <section class="card">
      <h1>Pontszámító</h1>

      <p v-if="loading">Betöltés...</p>
      <p v-else-if="error" class="error">{{ error }}</p>

      <div v-if="!loading && !error" class="section">
        <label class="field">
          <span>Egyetemi szak</span>
          <select v-model="selectedUniversityProgram">
            <option value="" disabled>Válassz egy szakot</option>
            <option v-for="program in universityProgramOptions" :key="program.value" :value="program.value">
              {{ program.label }}
            </option>
          </select>
        </label>
      </div>

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
              <strong>{{ getSubjectLabel(selectedSubject.name) }}</strong>
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
            :key="`${languageExam.language}-${languageExam.examType}-${index}`"
            class="subject-item"
          >
            <div>
              <strong>{{ getLanguageLabel(languageExam.language) }}</strong>
              <small>{{ getExamTypeLabel(languageExam.examType) }}</small>
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

      <div v-if="totalScore !== null" class="section result-section">
        <h2>Eredmény</h2>
        <div class="result-card">
          <div class="result-label">Összesített pontszám:</div>
          <div class="result-value">{{ totalScore }} pont</div>
        </div>
      </div>

      <p v-if="formSuccess" class="success">{{ formSuccess }}</p>
      <p v-if="formError" class="error">{{ formError }}</p>
    </section>

    <!-- Subject Modal -->
    <SubjectModal
      :show="showSubjectModal"
      :subject-options="subjectOptions"
      :subject-level-options="subjectLevelOptions"
      @close="closeSubjectModal"
      @add="addSubjectForCalculation"
    />

    <!-- Language Exam Modal -->
    <LanguageExamModal
      :show="showLanguageExamModal"
      :language-options="languageOptions"
      :exam-type-options="examTypeOptions"
      @close="closeLanguageExamModal"
      @add="addLanguageExamForCalculation"
    />
  </main>
</template>
