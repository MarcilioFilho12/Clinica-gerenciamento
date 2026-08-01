import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

const getToken = () => {
    const auth = JSON.parse(localStorage.getItem('token') || 'null');
    return auth || null;
};

const echoInstance = new Echo({
    broadcaster: 'pusher', // Reverb é compatível com a API do Pusher
    key: import.meta.env.VITE_REVERB_APP_KEY || 'shdc6ppskwfcxu15qpog',
    wsHost: import.meta.env.VITE_REVERB_HOST || window.location.hostname,
    wsPort: import.meta.env.VITE_REVERB_PORT || 8080,
    wssPort: import.meta.env.VITE_REVERB_PORT || 8080,
    forceTLS: import.meta.env.VITE_REVERB_SCHEME === 'https',
    enabledTransports: ['ws', 'wss'],
    disableStats: true,
    // Para Laravel Reverb (auto-hospedado), wsHost e wsPort permitem conexão direta sem cluster
    // Para canal público, não precisa de autenticação
    // Se precisar de autenticação JWT, descomentar:
    // authEndpoint: '/api/broadcasting/auth',
    // auth: {
    //     headers: {
    //         Authorization: `Bearer ${getToken()}`
    //     }
    // }
});

export default echoInstance;

