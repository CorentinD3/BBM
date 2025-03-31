<template>
  <div class="text-white flicking-container w-full h-auto overflow-hidden">
    <div class="container mx-auto">
      <Flicking
          ref="flicking"
          :circular="true"
          :gap="20"
          :duration="500"
          moveType="snap"
          align="0%"
          :bounce="30"
          :infinite="true"
      >
        <!-- Carrés noirs -->
        <div
            v-for="index in 10"
            :key="index"
            class="text-center pt-24 panel cursor-pointer lg:w-80 lg:h-80 w-64 h-64 bg-black mx-3 mt-4 mb-6 rounded-lg border-2 border-stone-700 shadow-[0px_3px_10px_rgba(0,0,0,0.5)]"
        ></div>
      </Flicking>
   </div>

  </div>
</template>

<script>
import Flicking from "@egjs/vue3-flicking";
import "@egjs/vue3-flicking/dist/flicking.css";

export default {
  name: "BlackSquareSlider",
  components: {
    Flicking,
  },
  mounted() {
    this.$nextTick(() => {
      if (this.$refs.flicking) {
        this.$refs.flicking.moveTo(3).catch(() => {});
      }
    });
  },
};
</script>

<style scoped>
 </style>