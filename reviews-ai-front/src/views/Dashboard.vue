  <!-- src/views/Dashboard.vue -->
  <script setup>
  import { computed, onMounted, ref } from "vue";
  import api from "../services/api";
  import { notifySuccess, notifyError } from "../services/notify";

  const stats = ref(null);
  const loading = ref(true);
  const error = ref(null);

  const fetchDashboard = async (showSuccessToast = false) => {
    loading.value = true;
    error.value = null;

    try {
      const res = await api.get("/dashboard");
      stats.value = res.data;

      if (showSuccessToast) notifySuccess("Dashboard mis à jour ✅", 2000);
    } catch (e) {
      console.error(e);
      error.value = "Impossible de charger le dashboard.";

      const status = e?.response?.status;
      if (status === 401) notifyError("Non autorisé. Reconnecte-toi.");
      else notifyError("Impossible de charger le dashboard.");
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

  const percentSafe = (v) => {
    const n = Number(v);
    if (Number.isNaN(n) || n < 0) return 0;
    if (n > 100) return 100;
    return n;
  };

  const posPct = computed(() => percentSafe(stats.value?.sentiments?.positive?.percent ?? 0));
  const neuPct = computed(() => percentSafe(stats.value?.sentiments?.neutral?.percent ?? 0));
  const negPct = computed(() => percentSafe(stats.value?.sentiments?.negative?.percent ?? 0));

  const dominantLabel = computed(() => {
    const d = stats.value?.sentiments?.dominant ?? "neutral";
    if (d === "positive") return "Plutôt positif";
    if (d === "negative") return "Plutôt négatif";
    return "Plutôt neutre";
  });

  const topTopic = computed(() => {
    const arr = stats.value?.top_topics;
    if (!Array.isArray(arr) || arr.length === 0) return null;
    return arr[0];
  });

  onMounted(() => fetchDashboard(false));
  </script>

  <template>
    <div class="page">
      <div class="page__header">
        <div>
          <h1>Dashboard</h1>
          <div class="muted" style="margin-top:4px;">
            Vue rapide : volume, qualité, sentiments et tendances.
          </div>
        </div>

        <button class="btn btn--ghost" @click="fetchDashboard(true)" :disabled="loading">
          Rafraîchir
        </button>
      </div>

      <p v-if="loading" class="muted">Chargement...</p>
      <p v-else-if="error" class="error">{{ error }}</p>

      <div v-else>
        <div class="grid">
          <div class="card card--span2 kpi">
            <div class="kpi__top">
              <div class="kpi__icon">🧾</div>
              <div class="card__label">Total reviews</div>
            </div>
            <div class="card__value">{{ stats?.total_reviews ?? 0 }}</div>
            <div class="muted kpi__hint">Nombre d’avis stockés</div>
          </div>

          <div class="card card--span2 kpi">
            <div class="kpi__top">
              <div class="kpi__icon">⭐</div>
              <div class="card__label">Score moyen</div>
            </div>
            <div class="card__value">
              {{ stats?.avg_score ?? "—" }}
              <span class="card__unit">/100</span>
            </div>
            <div class="muted kpi__hint">Qualité perçue globale</div>
          </div>

          <div class="card card--span2 kpi">
            <div class="kpi__top">
              <div class="kpi__icon">🎭</div>
              <div class="card__label">Tendance</div>
            </div>

            <div class="card__value" style="display:flex; align-items:center; gap:10px;">
              <span :class="badgeClass(stats?.sentiments?.dominant ?? 'neutral')">
                {{ stats?.sentiments?.dominant ?? "neutral" }}
              </span>
              <span class="muted" style="font-size:14px;">{{ dominantLabel }}</span>
            </div>

            <div class="muted kpi__hint">Sentiment dominant</div>
          </div>

          <!-- Répartition visuelle -->
          <div class="card card--span6">
            <div class="card__label">Répartition des sentiments</div>

            <div class="dist" style="margin-top:12px;">
              <div class="dist__bar" aria-label="Répartition des sentiments">
                <div class="dist__seg dist__seg--pos" :style="{ width: posPct + '%' }"></div>
                <div class="dist__seg dist__seg--neu" :style="{ width: neuPct + '%' }"></div>
                <div class="dist__seg dist__seg--neg" :style="{ width: negPct + '%' }"></div>
              </div>

              <div class="dist__legend">
                <div class="dist__item">
                  <span class="dot dot--pos"></span>
                  <span><b>Positif</b> : {{ stats?.sentiments?.positive?.count ?? 0 }} ({{ posPct }}%)</span>
                </div>
                <div class="dist__item">
                  <span class="dot dot--neu"></span>
                  <span><b>Neutre</b> : {{ stats?.sentiments?.neutral?.count ?? 0 }} ({{ neuPct }}%)</span>
                </div>
                <div class="dist__item">
                  <span class="dot dot--neg"></span>
                  <span><b>Négatif</b> : {{ stats?.sentiments?.negative?.count ?? 0 }} ({{ negPct }}%)</span>
                </div>
              </div>
            </div>
          </div>

          <!-- Top topic + topics -->
          <div class="card card--span3">
            <div class="card__label">Topic principal</div>

            <div v-if="topTopic" style="margin-top:10px; display:flex; flex-direction:column; gap:8px;">
              <div style="display:flex; align-items:center; gap:10px; flex-wrap:wrap;">
                <span class="badge">{{ topTopic.topic }}</span>
                <span class="muted">({{ topTopic.count }} occurrences)</span>
              </div>

              <div class="muted" style="font-size:14px;">
                Le sujet le plus mentionné dans les derniers avis.
              </div>
            </div>

            <div v-else class="muted" style="margin-top:10px;">
              Aucun topic pour le moment.
            </div>

            <div class="card__label" style="margin-top:16px;">Top topics</div>

            <div v-if="stats?.top_topics?.length" style="display:flex; gap:8px; flex-wrap:wrap; margin-top:10px;">
              <span class="badge" v-for="t in stats.top_topics" :key="t.topic">
                {{ t.topic }} · {{ t.count }}
              </span>
            </div>
            <div v-else class="muted" style="margin-top:10px;">Aucun topic pour le moment.</div>
          </div>

          <!-- Dernières reviews -->
          <div class="card card--span3">
            <div class="card__label">Dernières reviews</div>

            <div v-if="stats?.latest_reviews?.length" class="latest" style="margin-top:10px;">
              <div
                v-for="r in stats.latest_reviews"
                :key="r.id"
                class="latest__item"
              >
                <div class="latest__meta">
                  <div class="muted" style="display:flex; gap:10px; align-items:center; flex-wrap:wrap;">
                    <span class="code">#{{ r.id }}</span>
                    <span :class="badgeClass(r.sentiment)">{{ r.sentiment }}</span>
                    <span class="code">{{ r.score ?? "—" }}/100</span>
                  </div>
                  <div class="muted">{{ formatDate(r.created_at) }}</div>
                </div>

                <div class="latest__content">
                  {{ r.content }}
                </div>

                <div
                  v-if="r.topics?.length"
                  class="muted"
                  style="margin-top:8px; display:flex; gap:6px; flex-wrap:wrap;"
                >
                  <span class="badge" v-for="t in r.topics" :key="t">{{ t }}</span>
                </div>

                <!-- ✅ IA explicable : keywords_detected -->
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
              Aucune review enregistrée pour l’instant. Va sur <b>Analyser</b> pour en ajouter.
            </div>
          </div>

          <!-- Empty state global si pas de data -->
          <div v-if="(stats?.total_reviews ?? 0) === 0" class="card card--span6" style="margin-top:4px;">
            <div class="card__label">Aucune donnée</div>
            <div class="muted" style="margin-top:8px;">
              Commence par analyser et enregistrer quelques avis pour alimenter le dashboard.
            </div>
          </div>
        </div>
      </div>
    </div>
  </template>

  <style scoped>
  /* KPI */
  .kpi {
    display: flex;
    flex-direction: column;
    gap: 6px;
  }
  .kpi__top {
    display: flex;
    align-items: center;
    gap: 10px;
  }
  .kpi__icon {
    width: 34px;
    height: 34px;
    border-radius: 12px;
    display: grid;
    place-items: center;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
  }
  .kpi__hint {
    font-size: 13px;
  }

  /* Distribution bar */
  .dist__bar {
    width: 100%;
    height: 14px;
    border-radius: 999px;
    overflow: hidden;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    display: flex;
  }
  .dist__seg {
    height: 100%;
  }
  .dist__seg--pos { background: rgba(46, 204, 113, 0.85); }
  .dist__seg--neu { background: rgba(149, 165, 166, 0.85); }
  .dist__seg--neg { background: rgba(231, 76, 60, 0.85); }

  .dist__legend {
    margin-top: 10px;
    display: flex;
    gap: 14px;
    flex-wrap: wrap;
  }
  .dist__item {
    display: flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
  }
  .dot {
    width: 10px;
    height: 10px;
    border-radius: 999px;
    display: inline-block;
  }
  .dot--pos { background: rgba(46, 204, 113, 0.95); }
  .dot--neu { background: rgba(149, 165, 166, 0.95); }
  .dot--neg { background: rgba(231, 76, 60, 0.95); }

  /* Latest reviews */
  .latest {
    display: flex;
    flex-direction: column;
    gap: 10px;
  }
  .latest__item {
    border: 1px solid rgba(255, 255, 255, 0.06);
    border-radius: 14px;
    padding: 12px;
    background: rgba(255, 255, 255, 0.03);
  }
  .latest__meta {
    display: flex;
    justify-content: space-between;
    gap: 10px;
    flex-wrap: wrap;
  }
  .latest__content {
    margin-top: 8px;
    display: -webkit-box;
    -webkit-line-clamp: 2; /* 2 lignes max */
    -webkit-box-orient: vertical;
    overflow: hidden;
  }

  /* Mini-chips keywords */
  .chip--small {
    padding: 6px 10px;
    border-radius: 999px;
    background: rgba(255, 255, 255, 0.06);
    border: 1px solid rgba(255, 255, 255, 0.08);
    font-size: 12px;
  }
  </style>
