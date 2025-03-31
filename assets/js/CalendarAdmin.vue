<template>
  <!-- Loader identique à celui du client -->
  <div v-if="loading" class="flex justify-center items-center lg:h-[50vh] h-[80vh]">
    <span class="loader"></span>
  </div>

  <div v-else class="mx-auto container px-6 text-[#334155]">


    <!-- Layout 2 colonnes -->
    <div class="flex flex-col lg:flex-row items-start w-full">
      <!-- COLONNE GAUCHE : CALENDRIER -->
      <div
          ref="calendrier"
          class="flex flex-col 2xl:w-[34%] lg:w-[50%]
               sm:w-[100%] items-center justify-center mx-auto
               lg:border-r border-[#e5e7eb] w-full lg:pr-14 lg:pb-12 pb-6"
      >
        <!-- Navigation mois -->
        <div class="mb-6 lg:mt-14 mt-6 w-full h-auto">
          <div class="flex flex-row justify-between items-center w-full mb-2">
            <button
                @click="prevMonth"
                class="hover:shadow-inner shadow rounded-md w-10 h-10 flex items-center justify-center"
            >
              <!-- Flèche gauche -->
              <svg class="w-4 h-4 rotate-180" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 185.343 185.343">
                <g stroke="#010002" stroke-width="3" fill="#010002">
                  <path
                      d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149
                       c-4.194-4.194-4.194-10.981,0-15.175
                       l74.352-74.347L44.114,18.32
                       c-4.194-4.194-4.194-10.987,0-15.175
                       c4.194-4.194,10.987-4.194,15.18,0
                       l81.934,81.934
                       c4.194,4.194,4.194,10.987,0,15.175
                       l-81.934,81.939
                       C57.201,184.293,54.454,185.343,51.707,185.343z"
                  />
                </g>
              </svg>
            </button>

            <span class="text-xl font-medium text-center w-40">
              {{ monthYear.charAt(0).toUpperCase() + monthYear.slice(1) }}
            </span>

            <button
                @click="nextMonth"
                class="hover:shadow-inner shadow rounded-md w-10 h-10 flex items-center justify-center"
            >
              <!-- Flèche droite -->
              <svg class="w-4 h-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 185.343 185.343">
                <g stroke="#010002" stroke-width="3" fill="#010002">
                  <path
                      d="M51.707,185.343c-2.741,0-5.493-1.044-7.593-3.149
                       c-4.194-4.194-4.194-10.981,0-15.175
                       l74.352-74.347L44.114,18.32
                       c-4.194-4.194-4.194-10.987,0-15.175
                       c4.194-4.194,10.987-4.194,15.18,0
                       l81.934,81.934
                       c4.194,4.194,4.194,10.987,0,15.175
                       l-81.934,81.939
                       C57.201,184.293,54.454,185.343,51.707,185.343z"
                  />
                </g>
              </svg>
            </button>
          </div>

          <!-- Jours de la semaine -->
          <div class="flex flex-col items-center justify-center w-full">


            <!-- Jours du mois -->
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
            'text-black shadow-md relative after:content-empty after:absolute after:bottom-1 after:left-1/2 after:transform after:-translate-x-1/2 after:w-1.5 after:h-1.5 after:bg-black after:rounded-full': hasReservedSlots(date) && isDateDisabled(date),
            'bg-[#d3e9d1] ring-[0.5px] ring-black text-black shadow-md cursor-pointer': hasActiveTimeSlots(date) && !isDateDisabled(date),
            'bg-[#dbeafe] ring-[0.5px] ring-black text-black shadow-md cursor-pointer':  hasReservedSlots(date) && !isCurrentDay(date),
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
      </div>
      <!-- FIN COLONNE GAUCHE -->

      <!-- COLONNE DROITE : Heures -->
      <div
          ref="hoursPanel"
          class="flex flex-col 2xl:w-[66%] lg:w-[50%]
               sm:w-[100%] w-full items-center mx-auto
               lg:pl-14 pl-0 lg:pb-12"
      >
        <!-- Si pas de date sélectionnée -->
        <div v-if="!selectedDate" class="mb-6 lg:mt-14 mt-6 w-full h-auto">
          <p class="mt-6 text-base font-medium text-center">
            Veuillez sélectionner une date.
          </p>
        </div>

        <!-- Sinon, on affiche les créneaux pour cette date -->
        <div v-else class="mb-6 lg:mt-14 mt-6 w-full h-auto">
          <!-- Message d'erreur ou de succès (optionnel) -->
          <div v-if="message" :class="['mb-2 mt-2 w-full text-center font-medium', messageType === 'success' ? 'success' : 'error']">
            {{ message }}
          </div>
          <div v-if="apiError" class="error text-center">
            {{ apiError }}
          </div>

          <h3 class="text-center text-lg font-semibold mb-4">
            Plages horaires du {{ formattedSelectedDate }}
          </h3>

          <!-- Choix mode Add / Delete -->
          <div class="flex justify-center items-center space-x-4 pb-4">
            <button
                id="add"
                @click="setMode('add')"
                :class="[
                'px-4 py-2 rounded-md font-semibold cursor-pointer transition border-2 border-transparent hover:border-black',
                mode === 'add' ? 'bg-[#d3e9d1] border-[1.5px] border-black' : 'bg-white'
              ]"
            >
              Ajouter des heures
            </button>

            <button
                id="del"
                @click="setMode('delete')"
                :class="[
                'px-4 py-2 rounded-md font-semibold cursor-pointer transition border-2 border-transparent hover:border-black',
                mode === 'delete' ? 'bg-[#f8d7da] border-[1.5px] border-black' : 'bg-white'
              ]"
            >
              Supprimer des heures
            </button>
          </div>
          <div class="flex justify-center items-center space-x-4 pb-4">
            <button @click="selectAllDay">ALL</button>
            <button @click="selectMorning">AM</button>
            <button @click="selectAfternoon">PM</button>
          </div>
          <!-- Liste des heures -->
          <div class="flex flex-wrap justify-center gap-2 font-medium min-h-[150px]">
            <!-- Loader si loadingDate === true -->
            <div v-if="loadingDate" class="flex justify-center items-center w-full">
              <span class="loader"></span>
            </div>

            <!-- Sinon, affichage des créneaux -->
            <div v-else>
              <div class="flex flex-wrap justify-center items-center gap-2">
              <div

                  v-for="(slot, index) in timeSlots"
                  :key="index"
                  @click="toggleTimeSlot(slot)"
                  :class="{
        'flex items-center cursor-pointer w-24 justify-center py-3 px-4 rounded text-center transition border-2 border-transparent shadow-md lg:hover:border-2 lg:hover:border-black outline outline-[1.5px] outline-black': true,

        'selectAdd': isNewSlot(slot),
        'selectDel': isSlotMarkedForDeletion(slot),

        'disabledHour': isDisabledByAdjacentReservation(slot) && !isReservedSlot(slot),
        'unclickable ':
          (mode === 'add' && isActiveSlot(slot)) ||
          (mode === 'delete' && !isActiveSlot(slot)) ||
          isReservedSlot(slot) ||
          isDisabledByAdjacentReservation(slot),

        'bg-[#c8e6c9] text-black ': isActiveSlot(slot) && !isDisabledByAdjacentReservation(slot),
        'bg-blue-200 text-black': isReservedSlot(slot),
      }"
              >
                {{ slot }}
              </div>
              </div>
            </div>
          </div>

          <!-- Boutons d'action -->
          <div v-if="hasUnsavedChanges" class="mt-6 flex justify-center gap-4">
            <button
                v-if="mode === 'add' && newSlots.length > 0"
                @click="saveSelectedHours"
                class="bg-green-700 text-white font-medium px-4 py-2 rounded shadow hover:bg-green-800"
            >
              Sauvegarder
            </button>
            <button
                v-if="mode === 'delete' && slotsMarkedForDeletion.length > 0"
                @click="deleteSelectedHours"
                class="bg-red-700 text-white font-medium px-4 py-2 rounded shadow hover:bg-red-800"
            >
              Supprimer
            </button>
          </div>
        </div>
      </div>
      <!-- FIN COLONNE DROITE -->
    </div>
  </div>
</template>

<script>
export default {
  name: 'CalendarAdmin',
  data() {
    return {
      loading: true,
      weekDays: ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'],
      currentMonth: new Date(new Date().getFullYear(), new Date().getMonth(), 1),
      selectedDate: null,
      loadingDate: false,

      // Données
      dates: {},               // dates[ YYYY-MM-DD ] = [ { hour, user, ... }, ... ]
      hoursTemplates: [],      // Ex: [ { hour: "09:00" }, { hour: "09:30" } ... ]

      // États sélection
      mode: 'add',             // 'add' ou 'delete'
      newSlots: [],            // Heures sélectionnées (ajout)
      slotsMarkedForDeletion: [], // Heures sélectionnées (suppression)
      hasUnsavedChanges: false,

      // Champs feedback
      apiError: '',
      message: '',
      messageType: '',
    };
  },
  computed: {
    // Mois + année, ex : "Mars 2025"
    monthYear() {
      return this.currentMonth.toLocaleDateString('fr-FR', {
        month: 'long',
        year: 'numeric',
        timeZone: 'Europe/Paris'
      });
    },
    // Génère le tableau des semaines
    weeks() {
      return this.generateCalendar();
    },
    // Date sélectionnée format lisible
    formattedSelectedDate() {
      if (!this.selectedDate) return '';
      return this.selectedDate.toLocaleDateString('fr-FR', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
        timeZone: 'Europe/Paris'
      });
    },
    // Les heures à afficher (non réservées, etc.)
    timeSlots() {
      if (!this.selectedDate) return [];
      // On extrait juste le champ "hour" de hoursTemplates
      return this.hoursTemplates.map(t => t.hour);
    },
  },
  watch: {
    currentMonth: 'generateCalendar',
  },
  methods: {
    // Simule le chargement initial
    async mountedLoadingSimulation() {
      // Appels API...
      try {
        // EXEMPLE fetch
        const [hoursResponse, datesResponse] = await Promise.all([
          fetch('/api/hours-templates'),
          fetch('/api/dates')
        ]);

        this.hoursTemplates = await hoursResponse.json();
        const data = await datesResponse.json();

        // data.dates = [ { date: '2025-02-20', hours: [ { hour: '09:00', user: null }, ... ] } ]
        data.dates.forEach(item => {
          this.dates[item.date] = item.hours;
        });
      } catch (err) {
        this.apiError = 'Erreur lors du chargement';
      } finally {
        this.loading = false;
      }
    },

    // Génère un tableau [ [7 jours], [7 jours], ... ]
    generateCalendar() {
      const now = new Date(this.currentMonth.getFullYear(), this.currentMonth.getMonth(), 1);
      const daysInMonth = new Date(now.getFullYear(), now.getMonth() + 1, 0).getDate();
      const startDay = new Date(now.getFullYear(), now.getMonth(), 1).getDay();

      const calendar = [];
      let week = [];

      // Cases vides
      for (let i = 0; i < startDay; i++) {
        week.push(null);
      }

      // Jours du mois
      for (let day = 1; day <= daysInMonth; day++) {
        week.push(new Date(now.getFullYear(), now.getMonth(), day));
        if (week.length === 7) {
          calendar.push(week);
          week = [];
        }
      }

      // Compléter la dernière semaine
      if (week.length) {
        while (week.length < 7) {
          week.push(null);
        }
        calendar.push(week);
      }

      return calendar;
    },
    // Navigation
    prevMonth() {
      const newMonth = new Date(this.currentMonth);
      newMonth.setMonth(newMonth.getMonth() - 1);
      this.currentMonth = newMonth;
    },
    nextMonth() {
      const newMonth = new Date(this.currentMonth);
      newMonth.setMonth(newMonth.getMonth() + 1);
      this.currentMonth = newMonth;
    },

    // Sélection / dé-sélection date
    async selectDate(date) {
      if (!date || this.isDateDisabled(date)) return;
      this.loadingDate = true; // Activer le loader au début de la sélection

      // Toggle date
      if (this.selectedDate && date.getTime() === this.selectedDate.getTime()) {
        this.selectedDate = null;
      } else {
        this.selectedDate = date;
      }

      // Reset la sélection d'heures
      this.newSlots = [];
      this.slotsMarkedForDeletion = [];
      this.hasUnsavedChanges = false;

      // (Optionnel) re-fetch des dates sur le serveur
      try {
        const resp = await fetch('/api/dates');
        const data = await resp.json();
        data.dates.forEach(item => {
          this.dates[item.date] = item.hours;
          this.loadingDate = false;
        });
      } catch (err) {
        this.apiError = 'Erreur lors de la mise à jour des créneaux.';
      }
    },

    // Désactiver dates passées + dimanche
    isDateDisabled(date) {
      if (!date) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return date.getDay() === 0 || date < today;
    },
    // Jour actuel
    isCurrentDay(date) {
      if (!date) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      return date.getTime() === today.getTime();
    },
    // Date sélectionnée
    isSelectedDate(date) {
      return this.selectedDate && date && this.selectedDate.getTime() === date.getTime();
    },

    // A-t-il des créneaux actifs ?
    hasActiveTimeSlots(date) {
      if (!date) return false;
      const dateKey = date.toLocaleDateString('fr-CA');
      if (!this.dates[dateKey]) return false;

      // 'Actif' = user: null => encore libre
      return this.dates[dateKey].some(h => h.user === null);
    },
    // A-t-il des créneaux réservés ?
    hasReservedSlots(date) {
      if (!date) return false;
      const dateKey = date.toLocaleDateString('fr-CA');
      if (!this.dates[dateKey]) return false;

      // 'Réservé' = user !== null
      return this.dates[dateKey].some(h => h.user !== null);
    },

    // Bascule mode 'add' / 'delete', en resetant la sélection
    setMode(newMode) {
      this.mode = newMode;
      // Pour éviter d'avoir un item "add" quand on est en "delete"...
      this.newSlots = [];
      this.slotsMarkedForDeletion = [];
      this.hasUnsavedChanges = false;
    },

    // Clic sur un créneau
    toggleTimeSlot(slot) {
      if (!this.selectedDate) return;

      // Empêcher si overlap
      if (this.isDisabledByAdjacentReservation(slot)) return;

      if (this.mode === 'add') {
        const inNewSlots = this.newSlots.includes(slot);
        const isSaved = this.isActiveSlot(slot);
        const isReserved = this.isReservedSlot(slot);

        if (!isSaved && !inNewSlots && !isReserved) {
          // Sélection pour ajout
          this.newSlots.push(slot);
          this.hasUnsavedChanges = true;
        } else if (inNewSlots) {
          // Désélection
          this.newSlots = this.newSlots.filter(s => s !== slot);
          this.hasUnsavedChanges = this.newSlots.length > 0;
        }
      } else if (this.mode === 'delete') {
        const inDel = this.slotsMarkedForDeletion.includes(slot);
        const isSaved = this.isActiveSlot(slot);

        if (isSaved) {
          // Soit on marque, soit on dé-marque
          if (!inDel) {
            this.slotsMarkedForDeletion.push(slot);
            this.hasUnsavedChanges = true;
          } else {
            this.slotsMarkedForDeletion = this.slotsMarkedForDeletion.filter(s => s !== slot);
            this.hasUnsavedChanges = this.slotsMarkedForDeletion.length > 0;
          }
        }
      }
    },

    // Est-ce qu'un slot est déjà actif (non réservé) ?
    isActiveSlot(slot) {
      if (!this.selectedDate) return false;
      const dateKey = this.selectedDate.toLocaleDateString('fr-CA');
      return (
          this.dates[dateKey] &&
          this.dates[dateKey].some(h => h.hour === slot && h.user === null)
      );
    },
    // Est-ce réservé ?
    isReservedSlot(slot) {
      if (!this.selectedDate) return false;
      const dateKey = this.selectedDate.toLocaleDateString('fr-CA');
      return (
          this.dates[dateKey] &&
          this.dates[dateKey].some(h => h.hour === slot && h.user !== null)
      );
    },
    // Dans la liste d'ajout ?
    isNewSlot(slot) {
      return this.newSlots.includes(slot);
    },
    // Dans la liste de suppression ?
    isSlotMarkedForDeletion(slot) {
      return this.slotsMarkedForDeletion.includes(slot);
    },

    // Vérifie chevauchement (optionnel, ex. s’il existe un user + prestation)
    isDisabledByAdjacentReservation(slot) {
      // On récupère la dateKey
      const dateKey = this.selectedDate?.toLocaleDateString('fr-CA', { timeZone: 'Europe/Paris' });
      if (!dateKey) return false;

      // On recherche l'objet complet si "slot" est une string
      const slotData = this.dates[dateKey]?.find(s => s.hour === slot);

      // Convertit l'heure du slot courant
      const currentSlotTime = this.convertTimeToMinutes(slotData?.hour || slot);

      // Parcourt tous les créneaux de la date
      const daySlots = this.dates[dateKey] || [];
      for (let reservedSlot of daySlots) {
        if (!reservedSlot || !reservedSlot.user) {
          continue; // non réservé => on ignore
        }

        // Ignorer si c'est le même user, par exemple
        if (reservedSlot.user === this.currentUserId && slotData?.hour === reservedSlot.hour) {
          continue;
        }

        // Durée : variant si existe, sinon prestation
        let reservedDuration = 0;
        if (reservedSlot.variant) {
          reservedDuration = this.getPrestationDurationInMinutes(reservedSlot.variant.duration);
        } else if (reservedSlot.prestation) {
          reservedDuration = this.getPrestationDurationInMinutes(reservedSlot.prestation.duration);
        }

        const reservedSlotTime = this.convertTimeToMinutes(reservedSlot.hour);
        const reservedEnd = reservedSlotTime + reservedDuration;

        // Vérif chevauchement
        if (currentSlotTime >= reservedSlotTime && currentSlotTime < reservedEnd) {
          return true; // Désactivé
        }
      }
      return false;
    },
    // Convert 'HH:MM' => minutes
    convertTimeToMinutes(time) {
      const [hh, mm] = time.split(':').map(Number);
      return hh * 60 + mm;
    },
    // Convert 'HH:MM:SS' => minutes
    getPrestationDurationInMinutes(duration) {
      const [hh, mm] = duration.split(':').map(Number);
      return hh * 60 + mm;
    },

    // Sauvegarde Add
    async saveSelectedHours() {
      if (!this.selectedDate) return;
      const dateKey = this.selectedDate.toLocaleDateString('fr-CA');

      // On prend l'existant + les "nouveaux"
      const existing = this.dates[dateKey] || [];
      const existingHours = existing.map(e => typeof e === 'string' ? e : e.hour);

      const all = [...new Set([...existingHours, ...this.newSlots])];

      // Payload minimal
      const payload = {
        date: dateKey,
        hours: all
      };
      try {
        const response = await fetch('/api/date-hours', {
          method: 'POST',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();

        if (response.ok) {
          this.message = result.message || 'Enregistrement réussi.';
          this.messageType = 'success';
          // On re-fetch pour voir la maj
          await this.fetchSavedDates();
          // Reset
          this.newSlots = [];
          this.hasUnsavedChanges = false;
        } else {
          this.message = result.message || 'Une erreur est survenue.';
          this.messageType = 'error';
        }
      } catch (err) {
        this.message = 'Impossible de contacter le serveur.';
        this.messageType = 'error';
      }
    },

    // Supprimer
    async deleteSelectedHours() {
      if (!this.selectedDate) return;
      const dateKey = this.selectedDate.toLocaleDateString('fr-CA');
      const payload = {
        date: dateKey,
        hours: this.slotsMarkedForDeletion
      };

      try {
        const response = await fetch('/api/date-hours', {
          method: 'DELETE',
          headers: { 'Content-Type': 'application/json' },
          body: JSON.stringify(payload)
        });
        const result = await response.json();

        this.message = result.message || 'Une erreur est survenue.';
        this.messageType = response.ok ? 'success' : 'error';

        if (response.ok) {
          // On re-fetch
          await this.fetchSavedDates();
          // Reset
          this.slotsMarkedForDeletion = [];
          this.hasUnsavedChanges = false;
        }
      } catch (err) {
        this.message = 'Erreur lors de la suppression.';
        this.messageType = 'error';
      }
    },

    // Appel pour recharger les "dates" depuis l'API
    async fetchSavedDates() {
      try {
        const response = await fetch('/api/dates');
        const data = await response.json();
        data.dates.forEach(item => {
          this.dates[item.date] = item.hours;
        });
      } catch (err) {
        this.apiError = 'Erreur lors du rafraîchissement.';
      }
    },
    selectAllDay() {
      if (!this.selectedDate) return;

      // On récupère tous les créneaux disponibles de la journée
      const slotsToSelect = this.timeSlots;

      // On filtre pour ne retenir que les créneaux "cliquables" (non réservés, non désactivés, et
      // selon le mode : en "add" ceux qui ne sont pas encore actifs, en "delete" ceux qui sont actifs)
      const clickableSlots = slotsToSelect.filter(slot => {
        const notReserved = !this.isReservedSlot(slot);
        const notDisabled = !this.isDisabledByAdjacentReservation(slot);
        const modeCondition = (this.mode === 'delete')
            ? this.isActiveSlot(slot)
            : !this.isActiveSlot(slot);
        return notReserved && notDisabled && modeCondition;
      });

      if (this.mode === 'add') {
        // Vérifier si tous les créneaux de la journée sont déjà sélectionnés dans newSlots
        const allSelected = clickableSlots.every(slot => this.newSlots.includes(slot));
        if (allSelected) {
          // Tous les créneaux sont déjà sélectionnés : on les désélectionne
          this.newSlots = this.newSlots.filter(slot => !clickableSlots.includes(slot));
        } else {
          // Sinon, on ajoute les créneaux non sélectionnés
          this.newSlots = [...new Set([...this.newSlots, ...clickableSlots])];
        }
        this.hasUnsavedChanges = this.newSlots.length > 0;
      } else {
        // Mode "delete"
        const allSelected = clickableSlots.every(slot => this.slotsMarkedForDeletion.includes(slot));
        if (allSelected) {
          this.slotsMarkedForDeletion = this.slotsMarkedForDeletion.filter(slot => !clickableSlots.includes(slot));
        } else {
          this.slotsMarkedForDeletion = [...new Set([...this.slotsMarkedForDeletion, ...clickableSlots])];
        }
        this.hasUnsavedChanges = this.slotsMarkedForDeletion.length > 0;
      }
    },
    selectMorning() {
      // 1. Vérifier qu'une date est bien sélectionnée
      if (!this.selectedDate) return;

      // 2. Définir les créneaux "matin" en fonction de l'heure (avant 12:00)
      const slotsToSelect = this.timeSlots;
      const morningEndInMinutes = 12 * 60;

      const morningSlots = slotsToSelect.filter(slot => {
        // Convertir "HH:MM" en minutes
        const [hh, mm] = slot.split(':').map(Number);
        const slotInMinutes = hh * 60 + mm;

        // Vérifier que le créneau se situe avant 12:00
        const isMorning = slotInMinutes < morningEndInMinutes;

        // Conditions habituelles : le slot ne doit pas être réservé ni désactivé par chevauchement,
        // et doit respecter la logique du mode (ajout : pas déjà actif, suppression : déjà actif)
        const notReserved = !this.isReservedSlot(slot);
        const notDisabled = !this.isDisabledByAdjacentReservation(slot);
        const modeCondition = (this.mode === 'delete')
            ? this.isActiveSlot(slot)       // en mode "delete", on cible les créneaux actifs
            : !this.isActiveSlot(slot);     // en mode "add", on cible ceux non actifs

        return isMorning && notReserved && notDisabled && modeCondition;
      });

      // 3. Selon le mode, vérifier si tous les créneaux du matin sont déjà sélectionnés
      if (this.mode === 'add') {
        const allMorningSelected = morningSlots.every(slot => this.newSlots.includes(slot));
        if (allMorningSelected) {
          // S'ils le sont, les désélectionner en les retirant de newSlots
          this.newSlots = this.newSlots.filter(slot => !morningSlots.includes(slot));
        } else {
          // Sinon, les ajouter (en évitant les doublons)
          this.newSlots = [...new Set([...this.newSlots, ...morningSlots])];
        }
        this.hasUnsavedChanges = this.newSlots.length > 0;
      } else if (this.mode === 'delete') {
        const allMorningSelected = morningSlots.every(slot => this.slotsMarkedForDeletion.includes(slot));
        if (allMorningSelected) {
          this.slotsMarkedForDeletion = this.slotsMarkedForDeletion.filter(slot => !morningSlots.includes(slot));
        } else {
          this.slotsMarkedForDeletion = [...new Set([...this.slotsMarkedForDeletion, ...morningSlots])];
        }
        this.hasUnsavedChanges = this.slotsMarkedForDeletion.length > 0;
      }
    },
    selectAfternoon() {
      if (!this.selectedDate) return;

      // On récupère tous les créneaux disponibles
      const slotsToSelect = this.timeSlots;
      // Définir le début de l'après-midi (14h00 = 14 * 60 minutes)
      const afternoonStartInMinutes = 14 * 60;

      // On filtre pour ne retenir que les créneaux de l'après-midi
      const afternoonSlots = slotsToSelect.filter(slot => {
        // Convertir "HH:MM" en minutes
        const [hh, mm] = slot.split(':').map(Number);
        const slotInMinutes = hh * 60 + mm;
        const isAfternoon = slotInMinutes >= afternoonStartInMinutes;

        const notReserved = !this.isReservedSlot(slot);
        const notDisabled = !this.isDisabledByAdjacentReservation(slot);
        const modeCondition = (this.mode === 'delete')
            ? this.isActiveSlot(slot)
            : !this.isActiveSlot(slot);

        return isAfternoon && notReserved && notDisabled && modeCondition;
      });

      if (this.mode === 'add') {
        // Vérifier si tous les créneaux de l'après-midi sont déjà sélectionnés dans newSlots
        const allSelected = afternoonSlots.every(slot => this.newSlots.includes(slot));
        if (allSelected) {
          // Tous les créneaux sont déjà sélectionnés : on les désélectionne
          this.newSlots = this.newSlots.filter(slot => !afternoonSlots.includes(slot));
        } else {
          // Sinon, on ajoute les créneaux non sélectionnés
          this.newSlots = [...new Set([...this.newSlots, ...afternoonSlots])];
        }
        this.hasUnsavedChanges = this.newSlots.length > 0;
      } else {
        // Mode "delete"
        const allSelected = afternoonSlots.every(slot => this.slotsMarkedForDeletion.includes(slot));
        if (allSelected) {
          this.slotsMarkedForDeletion = this.slotsMarkedForDeletion.filter(slot => !afternoonSlots.includes(slot));
        } else {
          this.slotsMarkedForDeletion = [...new Set([...this.slotsMarkedForDeletion, ...afternoonSlots])];
        }
        this.hasUnsavedChanges = this.slotsMarkedForDeletion.length > 0;
      }
    },

  },

  async mounted() {
    // Simule / déclenche le chargement initial
    await this.mountedLoadingSimulation();
  },
};
</script>

<style scoped>
/* Loader identique au calendrier Client */
.loader {
  width: 50px;
  padding: 8px;
  aspect-ratio: 1;
  border-radius: 50%;
  background: #e5e7eb;
  --_m: conic-gradient(#0000 10%, #000),
  linear-gradient(#000, #fff) content-box;
  -webkit-mask: var(--_m);
  mask: var(--_m);
  -webkit-mask-composite: source-out;
  mask-composite: subtract;
  animation: l3 1s infinite linear;
}
@keyframes l3 { to { transform: rotate(1turn); } }

/* Messages success / error */
.success {
  padding: 15px;
  background-color: #d4edda;
  color: #155724;
  border: 1px solid #c3e6cb;
  border-radius: 5px;
}
.error {
  padding: 15px;
  background-color: #f8d7da;
  color: #721c24;
  border: 1px solid #f5c6cb;
  border-radius: 5px;
}



/* Sélection ajout / suppression */
.selectAdd {
  border: 1.5px solid black !important;
}
.selectDel {
  border: 1.5px solid black !important;
}

/* Désactivation */
.disabledHour {
  background-color: #f5f5f5;
  color: #ccc;
  cursor: not-allowed;

}
.unclickable:hover {
  cursor: not-allowed;
}
.unclickable {
  cursor: not-allowed;
  border: 2px solid transparent !important;
}



/* Couleurs pour les jours du calendrier */
.has-slots .day-content {
  background-color: #c8e6c9; /* vert clair */
}
.has-reserved-slots .day-content {
  background-color: #bfdbfe; /* bleu clair */
}
/* Jour qui a ET des slots libres ET des slots réservés */
.has-active-reserved-slots .day-content {
  background: linear-gradient(to bottom right, #c8e6c9 50%, #bfdbfe 50%);
}

.current-day .day-content {
  background-color: #fecaca;
  color: black;
}

/* Survol (uniquement si pas disabled, etc.) */
.aspect-square:hover {
  transition: all 0.2s ease-in-out;
}
</style>
