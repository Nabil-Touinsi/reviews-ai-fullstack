<script setup>
import { ref } from "vue";
import api from "../services/api";
import { useRouter } from "vue-router";
import { notifySuccess, notifyError } from "../services/notify";

const email = ref("");
const password = ref("");
const router = useRouter();

const login = async () => {
  try {
    const res = await api.post("/login", {
      email: email.value,
      password: password.value,
    });

    // Stockage auth
    localStorage.setItem("token", res.data.token);
    localStorage.setItem("user", JSON.stringify(res.data.user));

    notifySuccess("Connexion réussie ✅");
    router.push("/dashboard");
  } catch (e) {
    const status = e?.response?.status;

    if (status === 401) {
      notifyError("Identifiants incorrects.");
    } else {
      notifyError("Erreur de connexion. Réessaie.");
    }
  }
};
</script>

<template>
  <h1>Login</h1>

  <form @submit.prevent="login">
    <input v-model="email" placeholder="Email" />
    <input v-model="password" type="password" placeholder="Mot de passe" />
    <button>Se connecter</button>
  </form>
</template>
