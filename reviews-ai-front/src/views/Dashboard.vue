<script setup>
import { onMounted, ref } from "vue";
import api from "../services/api";

const stats = ref(null);
const loading = ref(true);
const error = ref(null);

const fetchDashboard = async () => {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.get("/dashboard");
    stats.value = res.data;
  } catch (e) {
    console.error(e);
    error.value = "Impossible de charger le dashboard.";
  } finally {
    loading.value = false;
  }
};

const badgeClass = (sentiment) => {
  if (sentiment === "positive") return "badge badge--positive";
  if (sentiment === "negative") return "badge badge--negative";
  return "badge badge--neutral";
};

const formatDate = (iso) => {
  if (!iso) return "—";
  const d = new Date(iso);
  if (Number.isNaN(d.getTime())) return iso;
  return d.toLocaleString();
};

onMounted(fetchDashboard);
</script>

<template>
  <div class="page">
    <div class="page__header">
      <h1>Dashboard</h1>

      <button class="btn btn--ghost" @click="fetchDashboard" :disabled="loading">
        Rafraîchir
      </button>
    </div>

    <p v-if="loading" class="muted">Chargement...</p>
    <p v-else-if="error" class="error">{{ error }}</p>

    <div v-else>
      <!-- KPI -->
      <div class="grid">
        <div class="card card--span2">
          <div class="card__label">Total reviews</div>
          <div class="card__value">{{ stats?.total_reviews ?? 0 }}</div>
        </div>

        <div class="card card--span2">
          <div class="card__label">Score moyen</div>
          <div class="card__value">
            {{ stats?.avg_score ?? "—" }}
            <span class="card__unit">/100</span>
          </div>
        </div>

        <div class="card card--span2">
          <div class="card__label">Sentiment dominant</div>
          <div class="card__value" style="display:flex; align-items:center; gap:10px;">
            <span :class="badgeClass(stats?.sentiments?.dominant ?? 'neutral')">
              {{ stats?.sentiments?.dominant ?? "neutral" }}
            </span>
            <span class="muted" style="font-size:14px;">
              (sur l’ensemble)
            </span>
          </div>
        </div>

        <div class="card card--span3">
          <div class="card__label">Sentiment positif</div>
          <div class="card__value">
            {{ stats?.sentiments?.positive?.count ?? 0 }}
            <span class="card__unit">({{ stats?.sentiments?.positive?.percent ?? 0 }}%)</span>
          </div>
        </div>

        <div class="card card--span3">
          <div class="card__label">Sentiment neutre</div>
          <div class="card__value">
            {{ stats?.sentiments?.neutral?.count ?? 0 }}
            <span class="card__unit">({{ stats?.sentiments?.neutral?.percent ?? 0 }}%)</span>
          </div>
        </div>

        <div class="card card--span3">
          <div class="card__label">Sentiment négatif</div>
          <div class="card__value">
            {{ stats?.sentiments?.negative?.count ?? 0 }}
            <span class="card__unit">({{ stats?.sentiments?.negative?.percent ?? 0 }}%)</span>
          </div>
        </div>

        <!-- Top topics -->
        <div class="card card--span3">
          <div class="card__label">Top topics</div>

          <div v-if="stats?.top_topics?.length" style="display:flex; gap:8px; flex-wrap:wrap;">
            <span class="badge" v-for="t in stats.top_topics" :key="t.topic">
              {{ t.topic }} · {{ t.count }}
            </span>
          </div>

          <div v-else class="muted">Aucun topic pour le moment.</div>
        </div>

        <!-- Latest reviews -->
        <div class="card card--span6">
          <div class="card__label">Dernières reviews</div>

          <div v-if="stats?.latest_reviews?.length" style="display:flex; flex-direction:column; gap:10px;">
            <div
              v-for="r in stats.latest_reviews"
              :key="r.id"
              style="border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:12px; background: rgba(255,255,255,.03);"
            >
              <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
                <div class="muted" style="display:flex; gap:10px; align-items:center;">
                  <span class="code">#{{ r.id }}</span>
                  <span :class="badgeClass(r.sentiment)">{{ r.sentiment }}</span>
                  <span class="code">{{ r.score ?? "—" }}/100</span>
                </div>

                <div class="muted">{{ formatDate(r.created_at) }}</div>
              </div>

              <div style="margin-top:8px;">
                {{ r.content }}
              </div>

              <div v-if="r.topics?.length" class="muted" style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;">
                <span class="badge" v-for="t in r.topics" :key="t">{{ t }}</span>
              </div>
            </div>
          </div>

          <div v-else class="muted">Aucune review enregistrée pour l’instant.</div>
        </div>
      </div>
    </div>
  </div>
</template>
