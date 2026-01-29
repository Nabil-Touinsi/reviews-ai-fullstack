<script setup>
import { ref, onMounted } from "vue";
import api from "../services/api";

const loading = ref(true);
const stats = ref(null);
const error = ref(null);

const loadStats = async () => {
  loading.value = true;
  error.value = null;

  try {
    const res = await api.get("/admin/stats");
    stats.value = res.data;
  } catch (e) {
    const status = e?.response?.status;

    if (status === 403) {
      error.value = "Accès refusé : réservé aux administrateurs.";
    } else {
      error.value = "Erreur lors du chargement des statistiques admin.";
    }
  } finally {
    loading.value = false;
  }
};

onMounted(loadStats);
</script>

<template>
  <div>
    <h1>Admin</h1>

    <p v-if="loading">Chargement...</p>
    <p v-else-if="error" style="color: red">{{ error }}</p>

    <div v-else class="card" style="margin-top: 16px">
      <p><strong>Statut :</strong> {{ stats.message }}</p>
      <p><strong>Nombre d’utilisateurs :</strong> {{ stats.users_count }}</p>
      <p><strong>Nombre d’avis :</strong> {{ stats.reviews_count }}</p>

      <button class="btn" style="margin-top: 12px" @click="loadStats">
        Rafraîchir
      </button>
    </div>
  </div>
</template>
