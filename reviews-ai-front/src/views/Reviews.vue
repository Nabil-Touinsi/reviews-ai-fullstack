<script setup>
import { computed, onMounted, ref } from "vue";
import api from "../services/api";

const items = ref([]);
const loading = ref(true);
const error = ref(null);
const q = ref("");

const fetchReviews = async () => {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.get("/reviews");
    // On accepte soit { data: [...] } soit [...]
    items.value = Array.isArray(res.data) ? res.data : res.data?.data ?? [];
  } catch (e) {
    console.error(e);
    error.value = "Impossible de charger la liste des reviews.";
  } finally {
    loading.value = false;
  }
};

const badgeClass = (sentiment) => {
  if (sentiment === "positive") return "badge badge--positive";
  if (sentiment === "negative") return "badge badge--negative";
  return "badge badge--neutral";
};

const filtered = computed(() => {
  const query = q.value.trim().toLowerCase();
  if (!query) return items.value;

  return items.value.filter((r) => {
    const content = (r.content ?? "").toLowerCase();
    const sentiment = (r.sentiment ?? "").toLowerCase();
    const topics = Array.isArray(r.topics) ? r.topics.join(" ").toLowerCase() : "";
    return (
      content.includes(query) ||
      sentiment.includes(query) ||
      topics.includes(query) ||
      String(r.id ?? "").includes(query)
    );
  });
});

const formatDate = (iso) => {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso;
  return d.toLocaleString();
};

onMounted(fetchReviews);
</script>

<template>
  <div class="page">
    <div class="page__header">
      <h1>Reviews</h1>

      <div style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
        <input
          v-model="q"
          placeholder="Rechercher (id, texte, sentiment, topics...)"
          style="width:320px; max-width: 100%;"
        />

        <button class="btn btn--ghost" @click="fetchReviews" :disabled="loading">
          Rafraîchir
        </button>
      </div>
    </div>

    <p v-if="loading" class="muted">Chargement...</p>
    <p v-else-if="error" class="error">{{ error }}</p>

    <div v-else>
      <!-- Empty state -->
      <div v-if="!items.length" class="card">
        <div class="card__label">Aucune review</div>
        <div class="muted">
          Va sur <b>Analyser</b> pour ajouter quelques avis, puis reviens ici.
        </div>
      </div>

      <!-- No results after filter -->
      <div v-else-if="!filtered.length" class="card">
        <div class="card__label">Aucun résultat</div>
        <div class="muted">
          Essaie un autre mot-clé (ex: <span class="code">positive</span>, <span class="code">delivery</span>, etc.)
        </div>
      </div>

      <!-- Table -->
      <table v-else class="table">
        <thead>
          <tr>
            <th style="width:80px;">ID</th>
            <th>Contenu</th>
            <th style="width:140px;">Sentiment</th>
            <th style="width:90px;">Score</th>
            <th style="width:220px;">Date</th>
          </tr>
        </thead>

        <tbody>
          <tr v-for="r in filtered" :key="r.id">
            <td class="code">{{ r.id }}</td>

            <td>
              <div style="display:flex; flex-direction:column; gap:6px;">
                <div>{{ r.content }}</div>

                <div v-if="r.topics?.length" class="muted" style="display:flex; gap:6px; flex-wrap:wrap;">
                  <span class="badge" v-for="t in r.topics" :key="t">{{ t }}</span>
                </div>
              </div>
            </td>

            <td>
              <span :class="badgeClass(r.sentiment)">
                {{ r.sentiment ?? "neutral" }}
              </span>
            </td>

            <td class="code">
              {{ r.score ?? "—" }}
            </td>

            <td class="muted">
              {{ formatDate(r.created_at) }}
            </td>
          </tr>
        </tbody>
      </table>

      <div class="muted" style="margin-top:10px;">
        Total : <b>{{ filtered.length }}</b> / {{ items.length }}
      </div>
    </div>
  </div>
</template>
