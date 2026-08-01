import axios from "axios";

const axiosInstance = axios.create({
  baseURL: "http://localhost:8000/api",
  headers: {
    "Content-type": "application/json",
  },
});

axiosInstance.interceptors.request.use((config) => {
  config.headers = config.headers ?? {};

  // FormData precisa do boundary automático do browser
  if (typeof FormData !== "undefined" && config.data instanceof FormData) {
    if (typeof config.headers.delete === "function") {
      config.headers.delete("Content-Type");
      config.headers.delete("Content-type");
    } else {
      delete config.headers["Content-Type"];
      delete config.headers["Content-type"];
    }
  }

  const token = localStorage.getItem("token");
  if (token) {
    config.headers.Authorization = `Bearer ${token}`;
  }

  const slug =
    localStorage.getItem("clinic_slug") ||
    import.meta.env.VITE_CLINIC_SLUG ||
    "";
  // Branding público GET não exige header; demais rotas sim
  const url = String(config.url ?? "");
  const method = String(config.method || "get").toLowerCase();
  const isPublicBranding = method === "get" && url.includes("/clinic/branding");
  if (slug && !isPublicBranding) {
    config.headers["X-Clinic-Slug"] = slug;
  }

  return config;
});

axiosInstance.interceptors.response.use(
  (response) => response,
  (error) => {
    if (error.response?.status === 401) {
      const url = String(error.config?.url ?? "");
      const isAuthLogin = url.includes("/auth") && error.config?.method === "post";
      if (!isAuthLogin) {
        localStorage.removeItem("token");
        localStorage.removeItem("user");
        if (window.location.pathname !== "/") {
          window.location.href = "/";
        }
      }
    }
    return Promise.reject(error);
  }
);

export default axiosInstance;
