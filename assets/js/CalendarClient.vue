<template>

  <div v-if="loading" class="flex justify-center items-center lg:h-[50vh] h-[80vh]">
    <span class="loader"></span>
  </div>
  <div v-else class="">
  <div class=" mx-auto ">
    <div class="flex flex-col items-center mx-auto container mx-6 border-b border-black">
      <!-- Titre de la section -->
      <div class="border-b border-black self-start pb-3 lg:mb-12 mb-6 lg:mt-12 mt-6 w-full">
        <h1 class="text-xl font-bold tracking-wide mb-3 px-6">Sélectionnez une prestation</h1>
        <!-- Gestion des erreurs API -->
        <div v-if="apiError" class="fixed inset-x-0 top-28 z-50 flex justify-center">
          <div
              id="error-message"
              class="flex items-center justify-between p-2 rounded-md bg-[#f8d7da] border-2 border-[#8a4b4b] text-[#8a4b4b] shadow-lg max-w-[90%] mx-auto"
          >
            <!-- Icône d'avertissement -->
            <div class="flex items-center mr-3">
              <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1" stroke="currentColor" class="w-12 h-12">
                <path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
              </svg>
            </div>
            <!-- Message d'erreur -->
            <p class="text-sm font-medium w-fit  flex-grow pr-3">
              {{ apiError }}
            </p>

          </div>
        </div>


      </div>
      <!-- Liste des prestations -->
      <div class="flex lg:flex-row flex-col justify-center w-full lg:mb-12 mb-6 px-6 gap-1 items-center">
        <div
            v-for="prestation in prestations"
            :key="prestation.id"
            @click="selectedPrestation === prestation.id ? (selectedPrestation = null, needVariant = false, selectedVariant = null) : (selectedPrestation = prestation.id, needVariant = false, selectedVariant = null)"
            :class=" {
        'outline outline-[1.5px] outline-black bg-[#d3e9d1] text-black': selectedPrestation === prestation.id,
        'shadow bg-white text-gray-600 prestation': selectedPrestation !== prestation.id
      }"
            class="cursor-pointer shadow-lg transition text-center m-2 rounded-md w-full"
        >
          <div class="border border-black p-3 text-center rounded-md w-full h-full transition-all duration-500 ease-in-out">
            <h3 class="text-base font-medium">{{ prestation.label }}</h3>
            <p v-if="!needVariant" class="text-sm mt-1 opacity-100 translate-y-0 transition-all duration-500 ease-in-out">
              {{ prestation.duration }}
            </p>
          </div>
        </div>
      </div>

      <!-- Ajout ou retrait des remplissages -->
      <div v-if="selectedPrestation && needVariant === false" class="w-auto self-start text-xl font-bold tracking-wide mb-4 transition-all duration-500 ease-in-out px-6 container">
        <label class="flex cursor-pointer items-center space-x-2">
          <input
              type="checkbox"
              v-model="needVariant"
              @change="handleNeedVariantChange"
              class="hidden"
          />
          <span class="text-xl cursor-pointer font-semibold tracking-wide">Ajouter un remplissage</span>
          <span class="bg-stone-700 text-white rounded-full w-8 h-8 flex justify-center">+</span>
          <button
              @click="showExplanation = !showExplanation"
              class="ml-3 bg-gray-200 text-black rounded-full w-8 h-8 flex items-center justify-center shadow hover:shadow-lg"
              title="Qu'est-ce qu'un remplissage ?"
          >
            ?
          </button>
        </label>
        <!-- Bouton d'explication -->

        <!-- Texte explicatif -->
        <p
            v-if="showExplanation"
            class="mt-2 text-sm bg-gray-100 p-3 rounded-md shadow-md border border-gray-300"
        >
          Un remplissage permet de compléter ou de modifier une prestation déjà existante.
          Il s'applique uniquement aux prestations sélectionnées, et peut inclure des ajustements mineurs ou des ajouts.
        </p>
      </div>

      <div v-else-if="selectedPrestation && needVariant === true" class="w-auto self-start text-xl font-bold tracking-wide mb-4 transition-all duration-500 ease-in-out">
        <label class="flex cursor-pointer items-center space-x-2">
          <input
              type="checkbox"
              v-model="needVariant"
              @change="handleNeedVariantChange"
              class="hidden"
          />
          <span class="text-xl font-semibold tracking-wide pl-6">Retirer les remplissages</span>
          <span class="bg-stone-700 text-white rounded-full w-8 h-8 flex justify-center">-</span>
        </label>
      </div>

      <!-- Liste des variants -->
      <div v-if="needVariant && prestations.find(p => p.id === selectedPrestation).variants.length > 0" class="w-full">
        <div class="flex flex-col items-center justify-center mx-auto lg:mb-12 mb-6">
          <div class="flex lg:flex-row flex-col justify-center w-full px-6 gap-1 items-center">
            <div
                v-for="variant in prestations.find(p => p.id === selectedPrestation).variants"
                :key="variant.id"
                @click="selectedVariant = selectedVariant === variant.id ? null : variant.id"
                :class="{
            'outline outline-[1.5px] outline-black bg-[#d3e9d1] text-black': selectedVariant === variant.id,
            'shadow bg-white text-gray-600 prestation': selectedVariant !== variant.id
          }"
                class="cursor-pointer shadow-lg transition text-center m-2 rounded-md w-full"
            >
              <div class="border border-black p-3 text-center rounded-md w-full h-full">
                <h3 class="text-base font-medium">{{ variant.label }}</h3>
                <p class="text-sm mt-1">{{ variant.duration }}</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>


    <div class="flex flex-col lg:flex-row items-start mx-auto container px-6">
      <div ref="calendrier" class="flex flex-col 2xl:w-[34%] lg:w-[50%] sm:w-[100%] items-center justify-center mx-auto lg:border-r lg:border-b-0 border-b border-black w-full lg:pr-14  lg:pb-12">
        <div class="mb-6 lg:mt-14 mt-6 w-full h-auto">
          <div class="flex flex-row justify-between items-center w-full mb-2">
            <button @click="prevMonth" class="hover:shadow-inner shadow rounded-md w-10 h-10 flex items-center justify-center">
              <svg class="w-4 h-4 rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 185.343 185.343">
                <g stroke="#010002" stroke-width="3" fill="#010002">
                  <path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175 l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934 c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z" />
                </g>
              </svg>
            </button>

            <span class="text-xl font-medium text-center w-40">{{
                monthYear.charAt(0).toUpperCase() + monthYear.slice(1)
              }}</span>

            <button @click="nextMonth" class="hover:shadow-inner shadow rounded-md w-10 h-10 flex items-center justify-center">
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 185.343 185.343">
                <g stroke="#010002" stroke-width="3" fill="#010002">
                  <path d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149c-4.194-4.194-4.194-10.981,0-15.175 l74.352-74.347L44.114,18.32c-4.194-4.194-4.194-10.987,0-15.175c4.194-4.194,10.987-4.194,15.18,0l81.934,81.934 c4.194,4.194,4.194,10.987,0,15.175l-81.934,81.939C57.201,184.293,54.454,185.343,51.707,185.343z" />
                </g>
              </svg>
            </button>
          </div>

          <div class="flex flex-col items-center justify-center w-full">
            <!-- Jours de la semaine -->
            <div class="grid grid-cols-7 w-full text-center font-normal my-[4px] gap-2">
              <div v-for="day in weekDays" :key="day" class="w-full aspect-square flex items-center justify-center font-medium">
                {{ day }}.
              </div>
            </div>

            <!-- Jours du mois -->
            <div v-for="(week, weekIndex) in weeks" :key="weekIndex" class="grid grid-cols-7 w-full my-[4px] gap-2 font-medium">
              <div
                  v-for="(date, dateIndex) in week"
                  :key="dateIndex"
                  @click="date && selectDate(date)"
                  :class="{
            'bg-transparent  border-none cursor-default shadow-none pointer-events-none': !date,
            'text-gray-300 shadow-md border-none ring-none bg-gray-100 cursor-not-allowed pointer-events-none': isDateDisabled(date),
            'text-black shadow-md relative after:content-empty after:absolute after:bottom-1 after:left-1/2 after:transform after:-translate-x-1/2 after:w-1.5 after:h-1.5 after:bg-black after:rounded-full': isCurrentDay(date) && isDateDisabled(date),
            'text-black shadow-md relative after:content-empty after:absolute after:bottom-1 after:left-1/2 after:transform after:-translate-x-1/2 after:w-1.5 after:h-1.5 after:bg-black after:rounded-full': isUserLoggedIn && hasReservedSlots(date) && isDateDisabled(date),
            'bg-[#d3e9d1] ring-[0.5px] ring-black text-black shadow-md cursor-pointer': hasActiveTimeSlots(date) && !isDateFullyReserved(date) && !checkOverlappingSlots(date) && !isCurrentDay(date) && !isDateDisabled(date),
            'bg-[#dbeafe] ring-[0.5px] ring-black text-black shadow-md cursor-pointer': isUserLoggedIn && hasReservedSlots(date) && !isCurrentDay(date),
            'text-black shadow-md relative after:content-empty after:absolute after:bottom-1 after:left-1/2 after:transform after:-translate-x-1/2 after:w-1.5 after:h-1.5 after:bg-black after:rounded-full': isCurrentDay(date),
            'border-2 border-black shadow-md font-medium': isSelectedDate(date),
          }"
                  class="shadow-md  aspect-square w-full rounded-lg flex items-center justify-center cursor-pointer lg:hover:border-2 lg:hover:border-black"
              >
                {{ date ? date.getDate() : '' }}
              </div>
            </div>
          </div>
        </div>
      </div>

      <div ref="creneaux" class="flex flex-col 2xl:w-[66%] lg:w-[50%] sm:w-[100%] w-full items-center mx-auto lg:pl-14 pl-0 lg:pb-12">
        <div class="slotList mb-6 lg:mt-14 mt-6 w-full h-full overflow-hidden relative">

          <!-- Message si la date sélectionnée est aujourd'hui -->
          <div v-if="isCurrentDay(selectedDate)">
            <h3 class="text-xl font-medium flex items-center justify-center h-[40px] mb-2">
              Plages horaires pour {{ formattedSelectedDate }}
            </h3>
            <p class="text-center mt-6 mx-[5vw]">
              Les créneaux pour aujourd'hui ne sont plus disponibles à la réservation.
            </p>
          </div>

          <!-- Aucun créneau disponible -->
          <div v-else-if="selectedDate && isUserLoggedIn && !hasReservedSlots(selectedDate) && checkOverlappingSlots(selectedDate) || !isUserLoggedIn && checkOverlappingSlots(selectedDate)">
            <h3 class="text-xl font-medium flex items-center justify-center h-[40px] mb-2">
              Plages horaires pour {{ formattedSelectedDate }}
            </h3>
            <p class="text-center mt-6 mx-[5vw]">
              Aucun créneau horaire n'est disponible pour la date sélectionnée.
            </p>
          </div>

          <!-- Message par défaut si aucune date sélectionnée -->
          <h3 v-else-if="!selectedDate" class="text-xl font-medium flex items-center justify-center h-[40px] mb-2">
            Veuillez sélectionner une date.
          </h3>

          <!-- Aucun créneau pour la date -->
          <div v-else-if="selectedDate && !hasTimeSlotsForDate(selectedDate)">
            <h3 class="text-xl font-medium flex items-center justify-center h-[40px] mb-2">
              Plages horaires pour {{ formattedSelectedDate }}
            </h3>
            <p class="text-center mt-6 mx-[5vw]">
              Aucun créneau horaire n'est disponible pour la date sélectionnée.
            </p>
          </div>

          <!-- Liste des créneaux -->
          <div v-else-if="allSlots.length > 0" class="mx-auto flex flex-col h-full relative">
            <h3 class="text-xl font-medium flex items-center justify-center h-[40px] mb-2">
              Plages horaires pour {{ formattedSelectedDate }}
            </h3>

            <div v-if="loadingDate" class="flex justify-center items-center lg:h-[272px] h-[283px]">
              <span class="loader"></span>
            </div>

            <div v-else class="relative flex flex-col h-full overflow-y-auto mt-6 h-[85%]">
              <!-- Message si aucun créneau utilisable -->
              <div
                  v-if="availableNonOverlappingSlots.length === 0"
              >
                <p class="text-center mx-[5vw]">
                  Les créneaux disponibles ne suffisent pas pour la durée de la prestation sélectionnée.
                </p>
              </div>

              <!-- Liste des créneaux horaires -->
              <div
                  v-else
                  ref="slotListContainer"
                  class="flex flex-col h-[300px]"
              >
                <div
                    v-for="(slot, index) in availableNonOverlappingSlots"
                    :ref="'slot-' + slot.id"
                    :key="index"
                    @click="toggleTimeSlot(slot)"
                    :class="{
          'time-slot-button py-1 rounded shadow-lg border-[1px] border-black w-auto text-center': true,
          'bg-[#dbeafe] text-black': isMySlot(slot),
          'bg-[#d3e9d1] text-black': !isMySlot(slot),
          'select': selectedSlot === slot,
        }"
                    class="flex items-center justify-center cursor-pointer gap-1"
                >
                  {{ slot.hour }}
                </div>

                <!-- Dernière entrée vide -->
                <div class="empty-slot py-1 rounded shadow w-auto text-center"></div>
              </div>

              <!-- Gradient overlay outside of the scrollable div -->
              <div class="scroll-gradient absolute bottom-0 left-0 w-full h-8 pointer-events-none"></div>
            </div>
          </div>

          </div>
      </div>

    </div>
    <div v-if="selectedSlot" class="flex mx-auto container px-6 justify-center text-center border-t border-black pt-6 pb-14 ">
      <div v-if="!isUserLoggedIn" class="flex  xl:flex-row flex-col xl:gap-x-3 items-center gap-x-0 pt-6">
        <p class="flex justify-center my-2 font-semibold">
          Créneau sélectionné : {{ formattedSelectedDate }} à {{ selectedSlot.hour }}.
        </p>
        <button
            @click="redirectToLogin"
            class="flex items-center justify-center shadow text-[#5d7f64] font-medium  py-2 rounded-md bg-[#e0f3e0] transition px-3 border-2 border-transparent hover:border-black border2 gap-2 mx-auto lg:mt-0 mt-4">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2"
               stroke="currentColor" class="w-5 h-5">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z"/>
          </svg>
          Connectez-vous pour réserver
        </button>
      </div>
      <div v-else-if="isMySlot(selectedSlot)" class="flex justify-end xl:flex-row flex-col xl:gap-x-3	gap-x-0 pt-6">
        <p class="my-2 font-semibold">
          Créneau sélectionné : {{ formattedSelectedDate }} à {{ selectedSlot.hour }}.
        </p>
        <button
            onclick="openModal()"
            class="flex items-center justify-center shadow text-[#8a4b4b] font-medium  py-2 rounded-md bg-[#f8d7da] transition px-3 border-2 border-transparent hover:border-black border2 gap-2 mx-auto lg:mt-0 mt-4">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
          Annuler la réservation
        </button>
      </div>
      <div v-else-if="isAvailableSlot(selectedSlot) && !hasExistingAppointment()" class="flex justify-end xl:flex-row flex-col xl:gap-x-3	gap-x-0 pt-6">
        <p class="my-2 text-center font-semibold">
          Créneau sélectionné : {{ formattedSelectedDate }} à {{ selectedSlot.hour }}.
        </p>
        <button
            @click="saveSelectedHour"
            class="flex items-center justify-center shadow text-[#5d7f64] font-medium  py-2 rounded-md bg-[#e0f3e0] transition px-3 border-2 border-transparent hover:border-black border2 gap-2 mx-auto sm:mt-0 mt-4">
          <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"></path>
          </svg>
          Réserver ce créneau
        </button>
      </div>
      <div v-else class="flex mx-auto flex-col xl:gap-x-3 gap-x-0 pt-6">
        <div v-if="isUserAppointmentToday()">
          <p class="my-2 font-semibold">Vous avez un rendez-vous prévu aujourd'hui à {{ userAppointment.hour }}.</p>
        </div>
        <div v-else-if="isPastAppointment()" class="flex mx-auto xl:flex-row flex-col">
          <p class="my-2 font-semibold text-red-600">Votre réservation n'a pas pu être finalisée. Merci de contacter l'administrateur pour assistance.</p>
        </div>
        <div v-else class="flex mx-auto xl:flex-row flex-col items-center">
          <p class="my-2 font-semibold">Vous avez déjà une réservation confirmée le :</p>
          <a href="#" @click.prevent="goToReservedAppointment" class="my-2 font-semibold text-blue-600 hover:underline cursor-pointer ml-1">
            {{ formatDate(userAppointment.date) }} à {{ userAppointment.hour }}
          </a>
        </div>
      </div>
    </div>



  </div>
</div>
</template>
<script>
export default {
  name: 'CalendarClient',
  data() {
    return {
      loading: true, // Indicateur pour afficher un loader pendant le chargement
      loadingDate: false,
      weekDays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'], // Noms des jours de la semaine
      currentMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1, 0, 0, 0),
      selectedDate: null, // Date actuellement sélectionnée
      selectedSlot: null, // Créneau horaire sélectionné
      selectedPrestation: '', // Prestation sélectionnée par l'utilisateur
      prestations: [], // Liste des prestations disponibles
      needVariant: false, // État de la case "besoin d'un remplissage"
      selectedVariant: null, // Variante sélectionnée
      apiError: "", // Champ pour stocker les erreurs API
      isUserLoggedIn: false, // État de connexion de l'utilisateur
      userAppointment: null, // Détails du rendez-vous de l'utilisateur
      dates: {}, // Données des créneaux horaires par date
      showExplanation: false, // Par défaut, l'explication est masquée

    };
  },
  computed: {
    // Génère les semaines du calendrier pour le mois affiché
    weeks() {
      return this.generateCalendar();
    },
    // Affiche le mois et l'année au format français
    monthYear() {
      return this.currentMonth.toLocaleDateString('fr-FR', {month: 'long', year: 'numeric', timeZone: 'Europe/Paris'});
    },
    // Formate la date sélectionnée pour un affichage lisible
    formattedSelectedDate() {
      return this.selectedDate
          ? this.selectedDate.toLocaleDateString('fr-FR', {
            month: 'long',
            day: 'numeric',
            timeZone: 'Europe/Paris'
          })
          : '';
    },
    // Retourne les créneaux horaires disponibles (non réservés) pour la date sélectionnée
    availableSlots() {
      if (this.selectedDate) {
        const dateKey = this.selectedDate.toLocaleDateString('fr-CA', {timeZone: 'Europe/Paris'});
        // Filtrer uniquement les créneaux dont le champ user est null (non réservés)
        return this.dates[dateKey] ? this.dates[dateKey].filter(slot => slot.user === null) : [];
      }
      return [];
    },
    // Retourne tous les créneaux horaires pour la date sélectionnée, triés par heure
    allSlots() {
      if (this.selectedDate) {
        const dateKey = this.selectedDate.toLocaleDateString('fr-CA', {timeZone: 'Europe/Paris'});
        let slots = this.dates[dateKey] || []; // Récupère toutes les heures enregistrées pour la date sélectionnée
        return slots.sort((a, b) => a.hour.localeCompare(b.hour)); // Trier les créneaux horaires par heure
      }
      return [];
    },
    availableNonOverlappingSlots() {
      if (!this.selectedDate) return []; // Pas de date sélectionnée, retour vide

      const dateKey = this.selectedDate.toLocaleDateString('fr-CA', { timeZone: 'Europe/Paris' });
      const slots = this.dates[dateKey] || [];

      // Filtrer les créneaux qui ne sont ni réservés, ni désactivés par une prestation ou un chevauchement
      return slots.filter(slot => {
        return (
            !this.isReservedSlot(slot) &&
            !this.isDisabledByAdjacentReservation(slot) &&
            !this.isDisabledBySelectedPrestation(slot)
        );
      });
    },
  },
  watch: {
    apiError(newVal) {
      if (newVal) {
        setTimeout(() => {
          this.apiError = "";
        }, 4000);
      }
    },
    // Surveiller les changements dans la date sélectionnée
    selectedDate(newDate) {
      console.log('Date sélectionnée:', newDate);
      this.selectedSlot = null; // Réinitialiser le créneau sélectionné
    },

    selectedPrestation(newVal) {
      if (newVal) {
        this.apiError = false; // Réinitialiser l'erreur dès qu'une prestation est sélectionnée

        // Vérifier si la prestation sélectionnée désactive le créneau actuellement sélectionné
        if (this.selectedSlot && this.isDisabledBySelectedPrestation(this.selectedSlot)) {
          this.selectedSlot = null; // Désélectionner le créneau si désactivé
        }
      }
    },
    selectedVariant(newVal) {
      if (newVal) {
        this.apiError = false; // Réinitialiser l'erreur dès qu'une variante est sélectionnée

        // Vérifie si la variante sélectionnée désactive le créneau actuellement sélectionné
        if (this.selectedSlot && this.isDisabledBySelectedPrestation(this.selectedSlot)) {
          this.selectedSlot = null; // Désélectionner le créneau si désactivé
        }
      }
    },
  },

  methods: {
    syncHeight() {
      const calendrier = this.$refs.calendrier;
      const creneaux = this.$refs.creneaux;

      if (calendrier && creneaux) {
        const isLargeScreen = window.matchMedia("(min-width: 1024px)").matches;

        if (isLargeScreen) {
          creneaux.style.height = `${calendrier.offsetHeight}px`;
        } else {
          creneaux.style.height = "auto"; // Hauteur automatique sur mobile
        }
      }
    },




    // Charge les données initiales (modèles d'heures et créneaux sauvegardés) à partir de l'API
    async fetchInitialData() {
      const MAX_RETRIES = 3;

      try {
        // Appels API simultanés avec réessais intégrés
        const [hoursResponse, datesResponse] = await Promise.all([
          (async () => {
            for (let i = 0; i < MAX_RETRIES; i++) {
              try {
                const r = await fetch('/api/hours-templates');
                if (!r.ok) throw new Error('Erreur HTTP');
                return r;
              } catch (e) {
                if (i === MAX_RETRIES - 1) throw e;
                await new Promise(resolve => setTimeout(resolve, 1000));
              }
            }
          })(),
          (async () => {
            for (let i = 0; i < MAX_RETRIES; i++) {
              try {
                const r = await fetch('/api/dates');
                if (!r.ok) throw new Error('Erreur HTTP');
                return r;
              } catch (e) {
                if (i === MAX_RETRIES - 1) throw e;
                await new Promise(resolve => setTimeout(resolve, 1000));
              }
            }
          })()
        ]);

        // Traitement des réponses
        this.hoursTemplates = await hoursResponse.json();
        const data = await datesResponse.json();
        this.currentUserId = data.currentUserId;

        data.dates.forEach(item => {
          this.dates[item.date] = item.hours;
        });

      } catch (error) {
        console.error('Erreur:', error);
        this.apiError = 'Une erreur inattendue est survenue.';
      } finally {
        this.loading = false;
      }
    },

    async fetchSavedDates() {
      const MAX_RETRIES = 3;

      try {
        this.apiError = false;
        let response;

        // Logique de réessai simple avec boucle for
        for (let i = 0; i < MAX_RETRIES; i++) {
          try {
            response = await fetch('/api/dates');
            if (!response.ok) throw new Error('Erreur HTTP');
            break; // Si réussite, sortir de la boucle
          } catch (error) {
            if (i === MAX_RETRIES - 1) throw error;
            await new Promise(resolve => setTimeout(resolve, 1000));
          }
        }

        const data = await response.json();
        this.currentUserId = data.currentUserId || null;
        this.isUserLoggedIn = !!this.currentUserId;
        this.userAppointment = data.userAppointment;

        data.dates.forEach(item => {
          this.dates[item.date] = item.hours;
        });

      } catch (error) {
        console.error('Erreur:', error);
        this.apiError = 'Erreur lors du chargement des créneaux.';
      } finally {
        this.loading = false;
      }
    },

    // Récupère les prestations disponibles depuis l'API
    async fetchPrestations() {
      try {
        const response = await fetch('/api/prestations');
        if (response.ok) {
          const data = await response.json();
          this.prestations = data.prestations.map(prestation => ({
            ...prestation,
            variants: prestation.variants || [], // Inclure les variantes ou un tableau vide si aucune variante
          }));
        } else {
          this.apiError = 'Erreur lors du chargement des prestations.';
        }
      } catch (error) {
        this.apiError = 'Erreur lors du chargement des données. Veuillez réessayer plus tard.';
      }
    },

    redirectToLogin() {
      const redirectUrl = window.location.pathname; // Récupère l'URL actuelle
      window.location.href = `/login?redirect=${encodeURIComponent(redirectUrl)}`;
    },
    goToReservedAppointment() {
      this.loadingDate = true;
      if (this.userAppointment) {
        this.selectedDate = new Date(this.userAppointment.date);

        this.fetchSavedDates().then(() => {
          const slot = this.allSlots.find(s => s.hour === this.userAppointment.hour);
          if (slot) {
            this.selectedSlot = slot;
            this.loadingDate = false;

            this.$nextTick(() => {
              const slotElement = this.$refs['slot-' + slot.id];
              if (slotElement && slotElement[0]) {
                slotElement[0].scrollIntoView({
                  behavior: 'smooth',
                  block: 'center',
                });
              }
            });
          } else {
            console.error("Aucun créneau trouvé pour l'heure spécifiée :", this.userAppointment.hour);
          }
        });
      } else {
        console.error("Aucun rendez-vous utilisateur trouvé");
      }
    }

    ,
    async saveSelectedHour() {

      // Si aucune prestation n'est sélectionnée, afficher un message d'erreur
      if (!this.selectedPrestation || typeof this.selectedPrestation.id !== 'number') {
        this.apiError = "Veuillez sélectionner une prestation";
      }

      // Si besoin de variante mais aucune n'est sélectionnée, afficher une erreur
      if (this.needVariant && !this.selectedVariant) {
        this.apiError = "Veuillez sélectionner un remplissage.";
        return;
      }

      if (!this.selectedSlot || !this.selectedPrestation) return;

      this.apiError = false;

      const selectedHourId = this.selectedSlot.id;
      const selectedPrestationId = this.selectedPrestation;
      const variantId = this.needVariant ? this.selectedVariant : null;

      try {
        const response = await fetch('/api/confirm-appointment', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="tokenCalCli"]').getAttribute('content')
          },
          body: JSON.stringify({
            hourId: selectedHourId,
            prestationId: selectedPrestationId,
            variantId: variantId,
          }),
        });

        if (response.ok) {
          const data = await response.json();
          // Stocker les données dans une session ou rediriger vers la page de confirmation
          window.location.href = '/recap-page'; // Pas besoin de passer des paramètres dans l'URL
        } else {
          this.apiError = 'Erreur lors de la confirmation du rendez-vous. Veuillez réessayer plus tard.';
        }
      } catch (error) {
        this.apiError = 'Erreur lors du chargement des données. Veuillez réessayer plus tard.';
      }
    },


    // Génère le calendrier pour le mois actuel
    generateCalendar() {
      const now = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
      const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
      const startDay = new Date(now.getFullYear(), now.getMonth(), 1).getDay();
      const calendar = [];
      let week = [];

      // Remplir le début de la semaine avec des cases vides avant le premier jour du mois
      for (let i = 0; i < startDay; i++) {
        week.push(null);
      }

      // Ajouter les jours du mois
      for (let day = 1; day <= daysInMonth; day++) {
        week.push(new Date(now.getFullYear(), now.getMonth(), day));

        if (week.length === 7) {
          calendar.push(week);
          week = [];
        }
      }

      // Compléter la dernière semaine avec des null si nécessaire, sans ajouter de dates après la fin du mois
      if (week.length) {
        while (week.length < 7) {
          week.push(null);  // Ajout de cases vides après le dernier jour du mois
        }
        calendar.push(week);
      }

      return calendar;
    },

    // Sélectionne une date et recharge les créneaux horaires pour cette date
    async selectDate(date) {
      if (date && !this.isDateDisabled(date)) {
        this.loadingDate = true; // Activer le loader au début de la sélection

        // Si la date est déjà sélectionnée, la désélectionner
        if (this.selectedDate && this.selectedDate.getTime() === date.getTime()) {
          this.selectedDate = null; // Désélectionne la date
          this.selectedSlot = null; // Réinitialiser le créneau sélectionné
          return; // Sortir de la fonction
        }

        // Sélectionner une nouvelle date
        this.selectedDate = date;
        this.selectedSlot = null; // Réinitialiser le créneau sélectionné

        // Ajouter un délai de chargement simulé
        setTimeout(async () => {
          try {
            await this.fetchSavedDates(); // Recharger les créneaux horaires pour la nouvelle date
          } catch (error) {
            this.apiError = 'Erreur lors du chargement des créneaux horaires. Veuillez réessayer plus tard.';
          } finally {
            this.loadingDate = false; // Désactiver le loader après le chargement
          }
        }, 0); // 300ms pour simuler un petit temps de chargement
      }
    },

    // Vérifie si une date est désactivée (par exemple, si elle est dans le passé)
    isDateDisabled(date) {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      if (!date) {
        return false;  // Ne pas désactiver les cases vides
      }
      // Désactiver les dates passées et les dimanches (si tu souhaites désactiver les dimanches)
      return date.getDay() === 0 || date <= today - 1;
    },

    // Vérifie si une date contient des créneaux actifs (non réservés)
    hasActiveTimeSlots(date) {
      const dateKey = date ? date.toLocaleDateString('fr-CA', {timeZone: 'Europe/Paris'}) : '';
      return this.dates[dateKey] && this.dates[dateKey].length > 0;
    },
    handleNeedVariantChange() {
      if (!this.needVariant) {
        // Si la case est décochée, réinitialiser la variante sélectionnée
        this.selectedVariant = null;
      }
    },
    // Vérifie si une date est entièrement réservée
    isDateFullyReserved(date) {
      const dateKey = date ? date.toLocaleDateString('fr-CA', {timeZone: 'Europe/Paris'}) : '';
      if (!this.dates[dateKey]) return false; // Pas de créneaux, donc pas réservé

      // Vérifie si tous les créneaux de cette date sont réservés par d'autres utilisateurs
      return this.dates[dateKey].every(slot => slot.user !== null && slot.user !== this.currentUserId);
    },
    isUserAppointmentToday() {
      if (!this.userAppointment || !this.userAppointment.date) return false;

      const appointmentDate = new Date(this.userAppointment.date);
      const today = new Date();

      // Mettre les heures, minutes, secondes et millisecondes à zéro pour comparer uniquement la date
      today.setHours(0, 0, 0, 0);
      appointmentDate.setHours(0, 0, 0, 0);

      // Comparaison des dates
      return today.getTime() === appointmentDate.getTime();
    },
    isPastAppointment() {
      if (!this.userAppointment || !this.userAppointment.date) return false;

      const appointmentDate = new Date(this.userAppointment.date);
      const today = new Date();

      // Mettre les heures, minutes, secondes et millisecondes à zéro pour comparer uniquement la date
      today.setHours(0, 0, 0, 0);
      appointmentDate.setHours(0, 0, 0, 0);

      // Retourne true si le rendez-vous est dans le passé
      return appointmentDate.getTime() < today.getTime();
    },

    // Vérifie les chevauchements de créneaux
    checkOverlappingSlots(date) {
      if (!date) return false;

      const dateKey = date.toLocaleDateString('fr-CA', { timeZone: 'Europe/Paris' });
      const slots = this.dates[dateKey];
      if (!slots || slots.length === 0) return false;

      const reservedSlots = slots.filter(slot => slot.user !== null);
      const availableSlots = slots.filter(slot => slot.user === null);

      if (reservedSlots.length === 0) return false;

      const selectedPrestationOrVariant = this.needVariant
          ? this.prestations
              .find(prestation => prestation.id === this.selectedPrestation)
              ?.variants.find(variant => variant.id === this.selectedVariant)
          : this.prestations.find(prestation => prestation.id === this.selectedPrestation);

      if (!selectedPrestationOrVariant) return false;

      const duration = this.getPrestationDurationInMinutes(selectedPrestationOrVariant.duration);

      return availableSlots.every(available => {
        const availableStart = this.convertTimeToMinutes(available.hour);

        return reservedSlots.some(reserved => {
          // Utiliser la durée du variant réservé si présent, sinon prestation
          const reservedDuration = reserved.variant
              ? this.getPrestationDurationInMinutes(reserved.variant.duration)
              : reserved.prestation
                  ? this.getPrestationDurationInMinutes(reserved.prestation.duration)
                  : 0;

          const reservedStart = this.convertTimeToMinutes(reserved.hour);
          const reservedEnd = reservedStart + reservedDuration;

          // Ignorer le créneau réservé par l'utilisateur actuel
          if (reserved.user === this.currentUserId && available.hour === reserved.hour) {
            return false;
          }

          return availableStart >= reservedStart && availableStart < reservedEnd;
        });
      });
    },

    // Vérifie si des créneaux sont réservés par l'utilisateur actuel pour une date donnée
    hasReservedSlots(date) {
      const dateKey = date ? date.toLocaleDateString('fr-CA', { timeZone: 'Europe/Paris' }) : '';
      return (
          this.dates[dateKey] &&
          this.dates[dateKey].some(slot => slot.user && slot.user.id === this.currentUserId)
      );
    },

    // Vérifie si la date sélectionnée est identique à une date donnée
    isSelectedDate(date) {
      return this.selectedDate && date && date.toDateString() === this.selectedDate.toDateString();
    },

    prevMonth() {
      this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() - 1, 1, 0, 0, 0);
      this.loading = false;
    },

    nextMonth() {
      this.currentMonth = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth() + 1, 1, 0, 0, 0);
      this.loading = false;
    },

    // Vérifie si le créneau est réservé par l'utilisateur actuel
    isMySlot(slot) {
      return this.currentUserId && slot.user && slot.user.id === this.currentUserId;
    },

    // Vérifie si le créneau est réservé par un autre utilisateur
    isReservedSlot(slot) {
      if (!slot.user) return false; // Le slot n'est pas réservé
      // Si l'utilisateur n'est pas connecté, tous les slots réservés sont considérés comme réservés par quelqu'un
      if (!this.currentUserId) return true;
      // Sinon, on considère le slot comme réservé par quelqu'un d'autre uniquement si l'ID est différent
      return slot.user.id !== this.currentUserId;
    },

    // Vérifie si un créneau est disponible
    isAvailableSlot(slot) {
      return this.availableSlots.some(s => s.hour === slot.hour);
    },

    // Vérifie si l'utilisateur a déjà un rendez-vous
    hasExistingAppointment() {
      // Vérifie si userAppointment n'est pas null et contient une date
      return this.userAppointment && this.userAppointment.date && this.userAppointment.hour;
    },

    // Vérifie si une date est le jour actuel
    isCurrentDay(date) {
      const today = new Date();
      today.setHours(0, 0, 0, 0); // Réinitialise l'heure à minuit pour comparer uniquement les dates
      return date && date.getTime() === today.getTime();
    },

    toggleTimeSlot(slot) {
      if (this.selectedSlot === slot) {
        this.selectedSlot = null; // Désélectionner si déjà sélectionné
        return;
      }

      // Vérifier si le créneau est désactivé
      if (
          this.isReservedSlot(slot) ||
          this.isDisabledByAdjacentReservation(slot) ||
          this.isDisabledBySelectedPrestation(slot) // Vérifie si le créneau est désactivé par la variante
      ) {
        return;
      }

      this.selectedSlot = slot; // Sélectionner un nouveau créneau
    },

    // Vérifie si une date a des créneaux horaires
    hasTimeSlotsForDate(date) {
      // Formater la date au format 'YYYY-MM-DD' pour correspondre à votre objet dates
      const dateKey = date.toLocaleDateString('fr-CA', {timeZone: 'Europe/Paris'});

      // Vérifier si cette date a des heures associées
      return this.dates[dateKey] && this.dates[dateKey].length > 0;
    },

    // Formate une date pour affichage
    formatDate(dateString) {
      const options = {day: '2-digit', month: 'long', year: 'numeric', timeZone: 'Europe/Paris'};
      return new Date(dateString).toLocaleDateString('fr-FR', options);
    },

    isDisabledByAdjacentReservation(slot) {
      const currentSlotTime = this.convertTimeToMinutes(slot.hour);
      console.log("All slots:", this.allSlots); // Affiche tous les créneaux réservés

      // Parcourir tous les créneaux réservés
      for (const reservedSlot of this.allSlots) {
        console.log("Reserved slot:", reservedSlot); // Affiche chaque créneau réservé

        if (!reservedSlot || !reservedSlot.user) continue;

        const reservedSlotTime = this.convertTimeToMinutes(reservedSlot.hour);

        // Ignorer le créneau réservé par l'utilisateur actuel
        if (reservedSlot.user && reservedSlot.user.id === this.currentUserId && slot.hour === reservedSlot.hour) {
          continue;
        }

        const reservedPrestation = reservedSlot.prestation || null;
        if (!reservedPrestation) continue;
        console.log("Reserved prestation:", reservedPrestation); // Affiche la prestation réservée
        console.log("Reserved variant:", reservedSlot.variant); // Affiche la variante (si elle exis
        // Récupérer la durée de la prestation ou de la variante réservée
        let reservedDuration;

        if (reservedSlot.variant) {
          // Si une variante est sélectionnée, utiliser sa durée
          reservedDuration = this.getPrestationDurationInMinutes(reservedSlot.variant.duration);
        } else {
          // Sinon, utiliser la durée de la prestation de base
          reservedDuration = this.getPrestationDurationInMinutes(reservedPrestation.duration);
        }

        // Calculer le début et la fin du créneau réservé
        const reservedStart = reservedSlotTime;
        const reservedEnd = reservedSlotTime + reservedDuration;

        // Vérifier si le créneau actuel est dans la plage de la prestation réservée
        if (currentSlotTime >= reservedStart && currentSlotTime < reservedEnd) {
          return true; // Chevauchement détecté, désactive le créneau
        }
      }

      return false; // Pas de chevauchement détecté
    },

    // Désactive les créneaux en fonction de la prestation sélectionnée par l'utilisateur
    isDisabledBySelectedPrestation(slot) {
      const currentSlotTime = this.convertTimeToMinutes(slot.hour);

      // Obtenir la prestation ou variante sélectionnée
      const selectedPrestationOrVariant = this.needVariant
          ? this.prestations
              .find(prestation => prestation.id === this.selectedPrestation)
              ?.variants.find(variant => variant.id === this.selectedVariant)
          : this.prestations.find(prestation => prestation.id === this.selectedPrestation);

      // Si aucune prestation ni variante n'est sélectionnée, ne pas désactiver
      if (!selectedPrestationOrVariant) return false;

      const duration = this.getPrestationDurationInMinutes(selectedPrestationOrVariant.duration);

      // Vérifier si le créneau actuel chevauche une prestation réservée
      for (let reservedSlot of this.allSlots) {
        if (!reservedSlot || !reservedSlot.user) continue;

        // Utiliser la durée du variant réservé si présent, sinon prestation
        const reservedDuration = reservedSlot.variant
            ? this.getPrestationDurationInMinutes(reservedSlot.variant.duration)
            : reservedSlot.prestation
                ? this.getPrestationDurationInMinutes(reservedSlot.prestation.duration)
                : 0;

        const reservedSlotTime = this.convertTimeToMinutes(reservedSlot.hour);
        const startOfPrestationTime = reservedSlotTime - duration + 1;

        if (currentSlotTime < reservedSlotTime && currentSlotTime >= startOfPrestationTime) {
          return true; // Le créneau est désactivé
        }
      }

      return false; // Le créneau reste actif
    },

    // Convertir une heure de format 'HH:MM' en minutes depuis minuit
    convertTimeToMinutes(time) {
      const [hours, minutes] = time.split(':').map(Number);
      return hours * 60 + minutes;
    },

    // Convertit la durée de la prestation de format 'HH:MM:SS' en minutes
    getPrestationDurationInMinutes(duration) {
      const [hours, minutes] = duration.split(':').map(Number);
      return hours * 60 + minutes;
    },

    confirmAppointment() {
      this.$router.push({name: 'ConfirmAppointment'}); // Utilise le nom de la route
    },

  },

  // Charge les données initiales au montage du composant
  async mounted() {
    this.loading = true;
    await this.fetchPrestations(); // Appel en premier
    await this.fetchSavedDates(); // Autres données en second
    this.loading = false;
    this.syncHeight();
    window.addEventListener("resize", this.syncHeight);
    window.addEventListener("orientationchange", this.syncHeight); // Écoute l'orientation des mobiles
  },



};
</script>

<style scoped>
/* Styles pour les boutons des créneaux horaires */
.time-slot-button {
  min-width: 100px;
  text-align: center;
  padding: 10px;
  margin: 5px;
  border-radius: 5px;
}


.mode-selection button {
  margin-right: 10px;
}

.time-slot-button {
  cursor: pointer;
}

/* HTML: <div class="loader"></div> */
.loader {
  width: 50px;
  padding: 8px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: #fee2e2;
  --_m: conic-gradient(#0000 10%, #000),
  linear-gradient(#000, #fff) content-box;
  -webkit-mask: var(--_m);
  mask: var(--_m);
  -webkit-mask-composite: source-out;
  mask-composite: subtract;
  animation: l3 1s infinite linear;
}

@keyframes l3 {
  to {
    transform: rotate(1turn)
  }
}

.select {
  outline: 2px solid black;
}

.prestation:hover {
  outline: 1.5px solid black;
}


.time-slot-button:hover {
  outline: 2px solid black;
}

/* Désactiver l'effet hover sur mobile */
@media (max-width: 640px) {
  .prestation:hover
  {
    border: none;
    outline: none;
  }

  .time-slot-button:hover {
    outline: none;

  }

  .select.time-slot-button:hover {
    outline: 2px solid black;
  }

}

.select {
  outline: 2px solid black;
}


.empty-slot {
  background-color: transparent;
  color: transparent;
  cursor: default;
  pointer-events: none;
  box-shadow: none;
}

</style>
