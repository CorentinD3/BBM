// Importation des styles
import './styles/app.css';
// Démarrer l'application Stimulus
import './bootstrap';

import { createApp } from 'vue';
import CalAdmin from '/assets/js/CalendarAdmin.vue';
import CalAdminTest from '/assets/js/CalendarAdminTest.vue';
import CalClient from '/assets/js/CalendarClient.vue';
import SliderHybride from '/assets/js/SliderHybride.vue';
import SliderRusse from '/assets/js/SliderRusse.vue';
import SliderCilACil from '/assets/js/SliderCilACil.vue';
import SliderInstagram from '/assets/js/SliderInstagram.vue';

// Créez une instance de l'application Vue et montez-la


const appCalAdmin = createApp(CalAdmin);
appCalAdmin.mount('#appCalAdmin');

const appCalAdminTest = createApp(CalAdminTest);
appCalAdminTest.mount('#appCalAdminTest');

const appCalClient = createApp(CalClient);
appCalClient.mount('#appCalClient');

const appSliderHybride = createApp(SliderHybride);
appSliderHybride.mount('#appSliderHybride');

const appSliderRusse = createApp(SliderRusse);
appSliderRusse.mount('#appSliderRusse');

const appSliderCilACil = createApp(SliderCilACil);
appSliderCilACil.mount('#appSliderCilACil');

const appSliderInstagram = createApp(SliderInstagram);
appSliderInstagram.mount('#appSliderInstagram');
