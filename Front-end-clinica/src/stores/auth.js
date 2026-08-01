import { computed, ref } from 'vue';
import { defineStore } from 'pinia';
import axios from '../services/axios.js';

export const useAuthStore = defineStore('auth', () => {
  const token = ref(localStorage.getItem('token'));

  const getUserFromStorage = () => {
    const userData = localStorage.getItem('user');
    return userData ? JSON.parse(userData) : null;
  };

  const user = ref(getUserFromStorage());

  const profileId = computed(() => user.value?.profile_id ?? null);

  const profileName = computed(() => {
    const map = {
      1: 'Administrador',
      2: 'Recepção',
      3: 'Profissional',
    };
    return map[profileId.value] ?? 'Usuário';
  });

  function setToken(tokenValue) {
    localStorage.setItem('token', tokenValue);
    token.value = tokenValue;
  }

  function setUser(userValue) {
    localStorage.setItem('user', JSON.stringify(userValue));
    user.value = userValue;
  }

  function clear() {
    localStorage.removeItem('token');
    localStorage.removeItem('user');
    token.value = '';
    user.value = null;
  }

  function hasProfile(...ids) {
    if (!profileId.value) return false;
    return ids.includes(Number(profileId.value));
  }

  async function checkToken() {
    if (!token.value) {
      return false;
    }

    try {
      const { data } = await axios.get('/auth/verify');
      if (data?.user) {
        setUser(data.user);
      }
      return true;
    } catch {
      clear();
      return false;
    }
  }

  return {
    token,
    user,
    profileId,
    profileName,
    setToken,
    setUser,
    checkToken,
    clear,
    hasProfile,
  };
});
