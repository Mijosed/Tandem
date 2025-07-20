export default defineNuxtRouteMiddleware((to, from) => {
  const publicRoutes = ['/login', '/register', '/forgot-password'];
  if (publicRoutes.includes(to.path)) {
    return;
  }
  let token;
  if (process.client) {
    token = localStorage.getItem('jwt');
    if (!token) {
      console.warn('Aucun token JWT trouvé, redirection vers /login');
      return navigateTo('/login');
    }
  }
});