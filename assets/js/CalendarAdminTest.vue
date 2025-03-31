<template>
  <div v-if="loading" class="flex justify-center items-center lg:h-[50vh] h-[80vh]">
    <span class="loader"></span>
  </div>

  <div v-else class="mx-auto container px-6 text-[#334155]">
  <div class="mx-auto text-[#334155]  ">
    <!-- Boutons de bascule entre vue journalière et vue hebdomadaire -->
    <div class="flex justify-center my-4">
      <button
          @click="currentView = 'day'"
          :class="{'bg-[#5368d5] text-white': currentView === 'day', 'bg-[#f8fafc]': currentView !== 'day'}"
          class="py-1 px-2 rounded mr-2">
        Vue Jour
      </button>
      <button
          @click="currentView = 'week'"
          :class="{'bg-[#5368d5] text-white': currentView === 'week', 'bg-[#f8fafc]': currentView !== 'week'}"
          class="py-1 px-2 rounded">
        Vue Semaine
      </button>
    </div>

    <!-- Calendrier journalier -->
    <div v-if="currentView === 'day'">
      <!-- Navigation journalière -->
      <div class="flex justify-between items-center my-4 w-full">
        <button
            @click="prevDay"
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

        <h2 class="mx-auto text-center text-xl font-semibold">
          {{ dayName.charAt(0).toUpperCase() + dayName.slice(1) }} du {{ formatDate(currentDay) }}
        </h2>

        <button
            @click="nextDay"
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

      <!-- Tableau de l'agenda pour le jour -->
      <div class="overflow-x-auto">
        <div v-if="loadingDate" class="flex justify-center items-center h-40">
          <span class="loader"></span>
        </div>

        <table v-else class="min-w-full border-collapse">
          <colgroup>
            <!-- Les deux premières colonnes en largeur auto -->
            <col style="width: auto;">
            <col style="width: auto;">
            <!-- Les deux dernières colonnes se partagent le reste de la largeur -->
            <col style="width: 50%;">
            <col style="width: 50%;">
          </colgroup>
          <thead>
          <tr class="bg-[#f8fafc]">
            <th class="border px-2 py-1 text-center whitespace-nowrap">Créneaux</th>
            <th class="border px-2 py-1 text-center whitespace-nowrap">Statut</th>
            <th class="border px-2 py-1 text-center">Prestation</th>
            <th class="border px-2 py-1 text-center">Client</th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(slot, slotIndex) in timeSlots" :key="slotIndex">
            <td class="border px-2 py-1 text-center font-medium">
              {{ slot }}
            </td>
            <td
                class="border px-2 py-1 text-center cursor-pointer aspect-square flex"
                :class="{
                  'bg-gray-300 cursor-not-allowed':isDisabledByAdjacentReservationForDate(slot, currentDay) && !isReserved(slot, currentDay),
                  'bg-[#c8e6c9]': isAvailable(slot, currentDay),
                  'bg-[#dbeafe]': isReserved(slot, currentDay)
                }"
            >
              <!-- Vous pouvez ajouter ici une icône ou un texte indiquant le statut -->
            </td>
            <td class="border px-2 py-1 text-center cursor-pointer">
                <span v-if="getPrestationLabel(slot, currentDay) && !isDayDisabled(currentDay)">
                  {{ getPrestationLabel(slot, currentDay) }}
                </span>
              <span v-else>&nbsp;</span>
            </td>
            <td class="border px-2 py-1 text-center cursor-pointer">
                <span v-if="getPrestationLabel(slot, currentDay) && !isDayDisabled(currentDay)">
                  {{ getUser(slot, currentDay) }}
                </span>
              <span v-else>&nbsp;</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Calendrier hebdomadaire -->
    <div v-if="currentView === 'week'">
      <!-- Navigation semaine -->
      <div class="flex justify-between items-center my-4 w-full">
        <button
            @click="prevWeek"
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

        <h2 class="mx-auto text-center text-xl font-semibold px-2">
          Semaine du {{ formatDate(startOfWeek) }} au {{ formatDate(endOfWeek) }}
        </h2>

        <button
            @click="nextWeek"
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

      <!-- Tableau de l'agenda hebdomadaire -->
      <div class="overflow-x-auto">
        <div v-if="loadingDate" class="flex justify-center items-center h-40">
          <span class="loader"></span>
        </div>

        <table v-else class="min-w-full border-collapse">
          <thead>
          <tr class="bg-[#f8fafc]">
            <th class="border px-2 py-1 text-center w-24">Créneaux</th>
            <th v-for="(dayDate, i) in weekDays" :key="i" class="border px-2 py-1 text-center">
              {{ dayNames[i] }}<br />
              {{ formatDate(dayDate) }}
            </th>
          </tr>
          </thead>
          <tbody>
          <tr v-for="(slot, slotIndex) in timeSlots" :key="slotIndex">
            <td class="border px-2 py-1 text-center font-medium">
              {{ slot }}
            </td>
            <td
                v-for="(dayDate, dayIndex) in weekDays"
                :key="dayIndex"
                class="border w-[14%] px-2 py-1 text-center cursor-pointer"
                :class="{
                  'bg-gray-300 cursor-not-allowed': isDisabledByAdjacentReservationForDate(slot, dayDate) && !isReserved(slot, dayDate),
                  'bg-[#c8e6c9]': isAvailable(slot, dayDate),
                  'bg-[#dbeafe]': isReserved(slot, dayDate)
                }"
            >
                <span v-if="isWeekDisabled(dayDate) || getPrestationLabel(slot, dayDate)">
                  {{ getPrestationLabel(slot, dayDate) }}
                </span>
              <span v-else>&nbsp;</span>
            </td>
          </tr>
          </tbody>
        </table>
      </div>
    </div>

    <!-- Affichage d'une éventuelle erreur API -->
    <div v-if="apiError" class="mt-4 text-center text-red-700">
      {{ apiError }}
    </div>
  </div>
  </div>
</template>

<script>
export default {
  name: "WeeklyCalendarAdmin",
  data() {
    return {
      loading: true,
      loadingDate: true,
      apiError: "",
      // Variables de navigation
      startOfWeek: null,
      weekDays: [],
      dayNames: ["Lun", "Mar", "Mer", "Jeu", "Ven", "Sam", "Dim"],
      currentDay: null,
      // Variable pour la vue actuelle : 'day' ou 'week'
      currentView: 'day',
      // Liste des créneaux horaires issus de l’API
      timeSlots: [],
      dates: {},
    };
  },
  computed: {
    dayName() {
      return this.currentDay
          ? this.currentDay.toLocaleDateString("fr-FR", { weekday: "long" })
          : "";
    },
    endOfWeek() {
      const end = new Date(this.startOfWeek);
      end.setDate(end.getDate() + 6);
      return end;
    },
  },
  async mounted() {
    this.initCurrentDay();
    this.initCurrentWeek();
    await this.fetchTimeSlots();
    await this.fetchSavedDates();
  },
  methods: {
    initCurrentWeek() {
      const today = new Date();
      const dayOfWeek = today.getDay();
      const distanceToMonday = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
      const monday = new Date(today);
      monday.setHours(0, 0, 0, 0);
      monday.setDate(monday.getDate() - distanceToMonday);
      this.startOfWeek = monday;
      this.computeWeekDays();
    },
    initCurrentDay() {
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      this.currentDay = today;
      this.selectedDate = today;
    },
    computeWeekDays() {
      this.weekDays = [];
      for (let i = 0; i < 7; i++) {
        const d = new Date(this.startOfWeek);
        d.setDate(d.getDate() + i);
        this.weekDays.push(new Date(d));
      }
    },
    async prevWeek() {
      // Par exemple, si vous utilisez une variable de chargement spécifique aux changements de semaine
      this.loadingDate = true;
      const d = new Date(this.startOfWeek);
      d.setDate(d.getDate() - 7);
      this.startOfWeek = d;
      this.computeWeekDays();
      await this.fetchSavedDates();
      this.loadingDate = false;
    },
    async nextWeek() {
      this.loadingDate = true;
      const d = new Date(this.startOfWeek);
      d.setDate(d.getDate() + 7);
      this.startOfWeek = d;
      this.computeWeekDays();
      await this.fetchSavedDates();
      this.loadingDate = false;
    },
    async prevDay() {
      this.loadingDate = true;
      const d = new Date(this.currentDay);
      d.setDate(d.getDate() - 1);
      this.currentDay = d;
      this.selectedDate = d;
      await this.fetchSavedDates(); // On attend que les données soient chargées
      this.loadingDate = false;
    },
    async nextDay() {
      this.loadingDate = true;
      const d = new Date(this.currentDay);
      d.setDate(d.getDate() + 1);
      this.currentDay = d;
      this.selectedDate = d;
      await this.fetchSavedDates(); // On attend que les données soient chargées
      this.loadingDate = false;
    },
    formatDate(date) {
      if (!date) return "";
      return date.toLocaleDateString("fr-FR", { day: "2-digit", month: "2-digit", year: "numeric" });
    },
    async fetchTimeSlots() {
      try {
        const response = await fetch("/api/hours-templates");
        if (!response.ok) throw new Error("Erreur du serveur");
        const data = await response.json();
        this.timeSlots = data.map((item) => item.hour.slice(0, 5));
      } catch (error) {
        this.apiError = "Impossible de charger la liste des créneaux.";
      }
    },
    async fetchSavedDates() {
      try {
        const response = await fetch("/api/dates");
        if (!response.ok) throw new Error("Erreur du serveur");
        const data = await response.json();
        this.dates = {};
        data.dates.forEach((item) => {
          const dateKey = new Date(item.date).toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
          this.dates[dateKey] = item.hours;
        });
      } catch (error) {
        this.apiError = "Impossible de charger les réservations.";
      } finally {
        this.loading = false;
        this.loadingDate = false;

      }
    },
    isAvailable(slot, dayDate) {
      if (!dayDate) return false;
      const dateKey = dayDate.toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
      return this.dates[dateKey]?.some((h) => h.hour === slot && h.user === null) || false;
    },
    isReserved(slot, dayDate) {
      if (!dayDate) return false;
      const dateKey = dayDate.toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
      return this.dates[dateKey]?.some((h) => h.hour === slot && h.user !== null) || false;
    },
    isDisabledByAdjacentReservationForDate(slot, dayDate) {
      this.selectedDate = dayDate;
      return this.isDisabledByAdjacentReservation(slot);
    },
    isDisabledByAdjacentReservation(slot) {
      const dateKey = this.selectedDate?.toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
      if (!dateKey) return false;
      const slotData = this.dates[dateKey]?.find(s => s.hour === slot);
      const currentSlotTime = this.convertTimeToMinutes(slotData?.hour || slot);
      const daySlots = this.dates[dateKey] || [];
      for (let reservedSlot of daySlots) {
        if (!reservedSlot || !reservedSlot.user) continue;
        if (reservedSlot.user === this.currentUserId && slotData?.hour === reservedSlot.hour) continue;
        let reservedDuration = 0;
        if (reservedSlot.variant) {
          reservedDuration = this.getPrestationDurationInMinutes(reservedSlot.variant.duration);
        } else if (reservedSlot.prestation) {
          reservedDuration = this.getPrestationDurationInMinutes(reservedSlot.prestation.duration);
        }
        const reservedSlotTime = this.convertTimeToMinutes(reservedSlot.hour);
        const reservedEnd = reservedSlotTime + reservedDuration;
        if (currentSlotTime >= reservedSlotTime && currentSlotTime < reservedEnd) {
          return true;
        }
      }
      return false;
    },
    getPrestationDurationInMinutes(duration) {
      if (!duration) return 0;
      const [hh, mm] = duration.split(":").map(Number);
      return (hh || 0) * 60 + (mm || 0);
    },
    convertTimeToMinutes(time) {
      const [hh, mm] = time.split(":").map(Number);
      return hh * 60 + mm;
    },
    getPrestationLabel(slot, dayDate) {
      if (!dayDate) return null;
      const dateKey = dayDate.toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
      const found = this.dates[dateKey]?.find((h) => h.hour === slot && h.user !== null);
      if (!found) return null;


      if (found.variant && this.currentView === "day") {
        return found.prestation.label + " " + found.variant.label;
      } else if (found.variant && this.currentView !== "day") {
        return found.prestation.label + " " + this.trimVariantLabel(found.variant.label);
      } else if (found.prestation) {
        return found.prestation.label;
      } else {
        return 'Réservé';
      }
    },
    trimVariantLabel(label) {
      if (!label) return "";
      // On recherche le nombre dans la chaîne (par exemple "1" dans "Remplissage 1 semaine")
      const match = label.match(/Remplissage\s*(\d+)/i);
      if (match && match[1]) {
        return `R${match[1]}S`;
      }
      return label;
    },
    getUser(slot, dayDate) {
      if (!dayDate) return null;
      const dateKey = dayDate.toLocaleDateString("fr-CA", { timeZone: "Europe/Paris" });
      const found = this.dates[dateKey]?.find((h) => h.hour === slot && h.user !== null);
      if (!found) return null;
      if (found.user && found.user.firstName && found.user.lastName) {
        return `${found.user.lastName} ${found.user.firstName}`;
      } else if (found.user) {
        return found.user.id.toString();
      } else {
        return 'Utilisateur Inconnu';
      }
    },
    isDayDisabled(date) {
      if (!date) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      // Désactiver le jour si c'est antérieur à aujourd'hui
      return date < today;
    },
    isWeekDisabled(weekStartDate) {
      if (!weekStartDate) return false;
      const today = new Date();
      today.setHours(0, 0, 0, 0);
      // Calcul du lundi de la semaine courante
      const currentMonday = new Date(today);
      const dayOfWeek = today.getDay();
      // Dans notre système, on considère que la semaine commence le lundi :
      const diff = dayOfWeek === 0 ? 6 : dayOfWeek - 1;
      currentMonday.setDate(today.getDate() - diff);
      // Désactiver la semaine si le lundi de la semaine cible est antérieur au lundi courant
      return weekStartDate < currentMonday;
    },
  },
};
</script>
<style>
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
</style>