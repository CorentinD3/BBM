<template>
  <div id="app">
    <h1>Calendrier avec Horaires Disponibles</h1>
    <Calendar v-model="selectedDate" @change="onDateChange" />
    <TimeSlots :selectedDate="selectedDate" />
  </div>
</template>

<script>
import { ref } from 'vue';
import Calendar from './CalendarAdmin.vue';
import Slider from './Slider.vue';

export default {
  components: {
    Calendar,
    Slider
  },
  setup() {
    const selectedDate = ref(null);

    const onDateChange = (date) => {
      selectedDate.value = date;
    };

    return {
      selectedDate,
      onDateChange
    };
  }
};
</script>

<style scoped>
#app {
  font-family: Arial, sans-serif;
  text-align: center;
  margin-top: 20px;
}
</style>