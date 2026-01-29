<script setup>
import { computed, ref } from "vue";
import api from "../services/api";
import { notifySuccess, notifyError } from "../services/notify";

const text = ref("");

const analyzing = ref(false);
const saving = ref(false);

const analysis = ref(null);

const canAnalyze = computed(() => text.value.trim().length >= 3);
const canSave = computed(() => text.value.trim().length >= 3 && !saving.value);

const badgeClass = (s) => {
  if (s === "positive") return "badge badge--positive";
  if (s === "negative") return "badge badge--negative";
  return "badge badge--neutral";
};

const examplesBySentiment = {
  positive: [
    "Application super fluide et intuitive, je trouve tout facilement et ça répond vite.",
    "Très satisfait : interface claire, résultats cohérents, et aucune lenteur.",
    "Franchement top : rapide, pratique, et les fonctionnalités sont bien pensées.",
    "J’adore ! Le design est propre et l’expérience utilisateur est excellente.",
    "Service impeccable, je recommande. Tout fonctionne parfaitement."
  ],
  neutral: [
    "Globalement correct, mais parfois un peu lent sur certaines pages.",
    "L’app fait le job, sans plus. Quelques améliorations seraient bienvenues.",
    "Expérience moyenne : pas de gros bugs, mais rien d’exceptionnel non plus.",
    "C’est utilisable, mais il manque des options (mode sombre, recherche plus rapide).",
    "Pas mal, mais certains textes ne sont pas très clairs et l’UX peut être améliorée."
  ],
  negative: [
    "Très déçu : l’application plante souvent et les chargements sont interminables.",
    "Mauvaise expérience, bugs réguliers et impossible d’utiliser certaines fonctions.",
    "Le support ne répond pas et l’app est instable. Je ne recommande pas.",
    "Interface confuse, erreurs fréquentes, et résultats parfois incohérents.",
    "Catastrophique : crash au démarrage et perte de données après enregistrement."
  ]
};

const pickRandom = (arr) => arr[Math.floor(Math.random() * arr.length)];

const fillExample = (type = "random") => {
  analysis.value = null;

  if (type === "random") {
    const types = ["positive", "neutral", "negative"];
    const t = pickRandom(types);
    text.value = pickRandom(examplesBySentiment[t]);
    return;
  }

  text.value = pickRandom(examplesBySentiment[type]);
};

const runAnalyze = async () => {
  analysis.value = null;

  if (!canAnalyze.value) {
    notifyError("Le texte doit faire au moins 3 caractères.");
    return;
  }

  analyzing.value = true;
  try {
    const res = await api.post("/analyze", { text: text.value });
    analysis.value = res.data;
    notifySuccess("Analyse terminée ✅", 2000);
  } catch (e) {
    console.error(e);
    notifyError("Erreur pendant l'analyse.");
  } finally {
    analyzing.value = false;
  }
};

const saveReview = async () => {
  if (!canAnalyze.value) {
    notifyError("Le texte doit faire au moins 3 caractères.");
    return;
  }

  saving.value = true;
  try {
    await api.post("/reviews", { content: text.value });
    notifySuccess("Review enregistrée ✅");
    text.value = "";
    analysis.value = null;
  } catch (e) {
    console.error(e);
    const status = e?.response?.status;

    if (status === 401) notifyError("Non autorisé. Reconnecte-toi.");
    else if (status === 422) notifyError("Validation échouée (texte trop court).");
    else notifyError("Impossible d'enregistrer la review.");
  } finally {
    saving.value = false;
  }
};

const clearAll = () => {
  text.value = "";
  analysis.value = null;
};
</script>

<template>
  <div class="page">
    <div class="page__header">
      <h1>Analyser une review</h1>

      <div style="display:flex; gap:10px; flex-wrap:wrap;">
        <button class="btn btn--ghost" @click="fillExample('random')">Exemple aléatoire</button>
        <button class="btn btn--ghost" @click="fillExample('positive')">Exemple positif</button>
        <button class="btn btn--ghost" @click="fillExample('neutral')">Exemple neutre</button>
        <button class="btn btn--ghost" @click="fillExample('negative')">Exemple négatif</button>
        <button class="btn" @click="clearAll">Vider</button>
      </div>
    </div>

    <div class="grid">
      <!-- INPUT -->
      <div class="card card--span3">
        <div class="card__label">Texte à analyser</div>

        <textarea v-model="text" placeholder="Colle ici un avis utilisateur…" />

        <div class="actions">
          <button class="btn btn--primary" @click="runAnalyze" :disabled="analyzing || !canAnalyze">
            {{ analyzing ? "Analyse..." : "Analyser" }}
          </button>

          <button class="btn" @click="saveReview" :disabled="saving || !canSave">
            {{ saving ? "Enregistrement..." : "Enregistrer" }}
          </button>

          <span class="muted" v-if="!canAnalyze">Min 3 caractères</span>
        </div>
      </div>

      <!-- RESULT -->
      <div class="card card--span3">
        <div class="card__label">Résultat</div>

        <div v-if="!analysis" class="muted" style="margin-top:10px;">
          Lance une analyse pour afficher le sentiment, le score et les topics.
        </div>

        <div v-else class="result">
          <!-- Sentiment + score -->
          <div class="result__row">
            <span :class="badgeClass(analysis.sentiment)">
              {{ analysis.sentiment }}
            </span>
            <div class="score">
              <div class="score__value">{{ analysis.score }}</div>
              <div class="muted">/100</div>
            </div>
          </div>

          <!-- Topics -->
          <div class="card__label" style="margin-top:14px;">Topics</div>

          <div v-if="!analysis.topics?.length" class="muted" style="margin-top:8px;">
            Aucun topic détecté.
          </div>

          <div v-else class="chips">
            <span v-for="t in analysis.topics" :key="t" class="chip">
              {{ t }}
            </span>
          </div>

          <!-- IA EXPLICABLE -->
          <div class="card__label" style="margin-top:14px;">Pourquoi ?</div>

          <div v-if="!analysis.keywords_detected?.length" class="muted" style="margin-top:8px;">
            Aucun mot-clé explicatif détecté.
          </div>

          <div v-else class="chips" style="margin-top:12px;">
            <span
              v-for="k in analysis.keywords_detected"
              :key="k"
              class="chip chip--why"
            >
              {{ k }}
            </span>
          </div>
        </div>
      </div>
    </div>

    <div class="muted" style="margin-top:16px;">
      Tip : “Analyser” ne sauvegarde pas, “Enregistrer” crée une review en base.
    </div>
  </div>
</template>

<style scoped>
.actions {
  display: flex;
  gap: 10px;
  align-items: center;
  flex-wrap: wrap;
  margin-top: 12px;
}

.result { margin-top: 12px; }

.result__row {
  display: flex;
  align-items: center;
  justify-content: space-between;
  gap: 12px;
  flex-wrap: wrap;
}

.score {
  display: flex;
  align-items: baseline;
  gap: 6px;
}

.score__value {
  font-size: 34px;
  font-weight: 900;
}

.chips {
  display: flex;
  flex-wrap: wrap;
  gap: 10px;
  margin-top: 12px;
}

.chip {
  padding: 8px 10px;
  border-radius: 999px;
  background: rgba(0, 0, 0, 0.18);
  border: 1px solid rgba(255, 255, 255, 0.08);
  font-size: 13px;
}

.chip--why {
  background: rgba(255, 255, 255, 0.06);
}
</style>
