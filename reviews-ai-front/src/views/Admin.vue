<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";
import { notifySuccess, notifyError } from "../services/notify";

const loading = ref(true);
const stats = ref(null);
const error = ref(null);

const loadStats = async (showToast = false) => {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.get("/admin/stats");
    stats.value = res.data;
    if (showToast) notifySuccess("Stats admin mises à jour ✅", 2000);
  } catch (e) {
    const status = e?.response?.status;

    if (status === 403) {
      error.value = "Accès refusé : réservé aux administrateurs.";
      notifyError("Accès refusé : réservé aux administrateurs.");
    } else if (status === 401) {
      error.value = "Non autorisé. Reconnecte-toi.";
      notifyError("Non autorisé. Reconnecte-toi.");
    } else {
      error.value = "Erreur lors du chargement des statistiques admin.";
      notifyError("Erreur lors du chargement des statistiques admin.");
    }
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

onMounted(() => loadStats(false));
</script>

<template>
  <div class="page">
    <div
      class="page__header"
      style="display:flex; justify-content:space-between; gap:12px; flex-wrap:wrap;"
    >
      <div>
        <h1>Admin</h1>
        <div class="muted" style="margin-top:4px;">Stats + historique récent</div>
      </div>

      <button class="btn btn--ghost" :disabled="loading" @click="loadStats(true)">
        Rafraîchir
      </button>
    </div>

    <p v-if="loading" class="muted">Chargement...</p>
    <p v-else-if="error" class="error">{{ error }}</p>

    <div v-else class="grid">
      <!-- Bloc stats -->
      <div class="card card--span3">
        <div class="card__label">Statut</div>
        <div style="margin-top:8px;"><b>{{ stats?.message ?? "OK" }}</b></div>

        <div style="margin-top:12px;">
          <div class="muted">Nombre d’utilisateurs</div>
          <div class="card__value">{{ stats?.users_count ?? 0 }}</div>
        </div>

        <div style="margin-top:12px;">
          <div class="muted">Nombre d’avis</div>
          <div class="card__value">{{ stats?.reviews_count ?? 0 }}</div>
        </div>
      </div>

      <!-- Historique -->
      <div class="card card--span3">
        <div class="card__label">Historique (5 dernières reviews)</div>

        <div
          v-if="stats?.recent_reviews?.length"
          style="margin-top:10px; display:flex; flex-direction:column; gap:10px;"
        >
          <div
            v-for="r in stats.recent_reviews"
            :key="r.id"
            style="border:1px solid rgba(255,255,255,.06); border-radius:14px; padding:12px; background: rgba(255,255,255,.03);"
          >
            <div style="display:flex; justify-content:space-between; gap:10px; flex-wrap:wrap;">
              <div class="muted" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                <span class="code">#{{ r.id }}</span>
                <span :class="badgeClass(r.sentiment)">{{ r.sentiment ?? "neutral" }}</span>
                <span class="code">{{ r.score ?? "—" }}/100</span>
              </div>

              <div class="muted">{{ formatDate(r.created_at) }}</div>
            </div>

            <div style="margin-top:8px;">
              {{ r.content }}
            </div>

            <div
              v-if="r.topics?.length"
              class="muted"
              style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;"
            >
              <span class="badge" v-for="t in r.topics" :key="t">{{ t }}</span>
            </div>

            <div
              v-if="r.keywords_detected?.length"
              class="muted"
              style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;"
            >
              <span class="chip chip--small" v-for="k in r.keywords_detected" :key="k">
                {{ k }}
              </span>
            </div>
          </div>
        </div>

        <div v-else class="muted" style="margin-top:10px;">
          Aucun historique pour le moment.
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
.chip--small {
  padding: 6px 10px;
  border-radius: 999px;
  background: rgba(255, 255, 255, 0.06);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 12px;
}
</style>
