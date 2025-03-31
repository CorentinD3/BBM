import { createRouter, createWebHistory } from 'vue-router';
import CalClient from '/assets/js/CalendarClient.vue';

const routes = [
    {
        path: '/cal',
        component: CalClient // Votre composant Vue pour le calendrier
    },
    {
        path: '/confirm-appointment',
        // Aucune action ici, car nous redirigeons avec un bouton
        name: 'ConfirmAppointment' // Nom de la route pour une utilisation ultérieure
    },
    {
        path: '/cancel-appointment',
        name: 'CancelAppointment' // Nom de la route pour une utilisation ultérieure
    }
];

const router = createRouter({
    history: createWebHistory(),
    routes
});

export default router;