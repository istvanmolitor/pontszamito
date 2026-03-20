<script setup>
import { onMounted, ref } from 'vue'

const subjects = ref([])
const loading = ref(true)
const error = ref('')

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

onMounted(loadSubjects)
</script>

<template>
  <main class="page">
    <section class="card">
      <h1>Tantárgyak</h1>

      <p v-if="loading">Betöltés...</p>
      <p v-else-if="error" class="error">{{ error }}</p>

      <ul v-else class="subject-list">
        <li v-for="subject in subjects" :key="subject.id" class="subject-item">
          <div>
            <strong>{{ subject.name }}</strong>
            <small>{{ subject.code }}</small>
          </div>
          <span>{{ subject.credits }} kredit</span>
        </li>
      </ul>
    </section>
  </main>
</template>
