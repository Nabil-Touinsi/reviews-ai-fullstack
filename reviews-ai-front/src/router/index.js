import { createRouter, createWebHistory } from "vue-router";
import Login from "../views/Login.vue";
import Dashboard from "../views/Dashboard.vue";
import Reviews from "../views/Reviews.vue";
import Analyze from "../views/Analyze.vue";
import Admin from "../views/Admin.vue";

const router = createRouter({
  history: createWebHistory(),
  routes: [
    { path: "/", component: Login, meta: { guest: true } },

    { path: "/dashboard", component: Dashboard, meta: { requiresAuth: true } },
    { path: "/reviews", component: Reviews, meta: { requiresAuth: true } },
    { path: "/analyze", component: Analyze, meta: { requiresAuth: true } },

    // Admin (protégé)
    {
      path: "/admin",
      component: Admin,
      meta: { requiresAuth: true, requiresAdmin: true },
    },
  ],
});

router.beforeEach((to) => {
  const token = localStorage.getItem("token");
  const isAuth = !!token;

  // On récupère le user stocké par Login.vue
  let user = null;
  try {
    user = JSON.parse(localStorage.getItem("user"));
  } catch {
    user = null;
  }

  if (to.meta.requiresAuth && !isAuth) return "/";
  if (to.meta.guest && isAuth) return "/dashboard";

  // Protection admin
  if (to.meta.requiresAdmin && user?.role !== "admin") return "/dashboard";
});

export default router;
