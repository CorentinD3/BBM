<template>
  <div class="relative mx-auto container px-4 py-6">
    <div class="flex items-center justify-center">
      <img
          :src="hybrideImages[prevImageHybride]"
          alt="cilACil"
          class="rounded-lg border-[1.5px] border-black z-0 transform translate-x-[75%] scale-90 w-[70%] h-auto shadow-[0px_3px_10px_rgba(0,0,0,0.5)]"
      />
      <img
          :src="hybrideImages[currentImageHybride]"
          alt="cilACil"
          onclick="openModal(this.src)"
          id="image-1"
          class="rounded-lg cursor-pointer border-2 border-black z-10 transform scale-110 translate-y-[-2%] w-[70%] h-auto shadow-[0px_3px_10px_rgba(0,0,0,0.5)]"
      />
      <img
          :src="hybrideImages[nextImageHybride]"
          alt="cilACil"
          class="rounded-lg border-[1.5px] border-black z-0 transform -translate-x-[75%] scale-90 w-[70%] h-auto shadow-[0px_3px_10px_rgba(0,0,0,0.5)]"
      />
    </div>
    <div class="">
      <button
          class="absolute top-1/2 -translate-y-1/2 left-[0px] w-12 h-12 sm:w-14 sm:h-14 transform translate-x-[0%] text-orange-950 z-20 rounded-full flex items-center justify-center bg-white bg-opacity-75 border-[1.5px] border-black"
          @click="previousImage('cilACil')"
      >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="black"
            class="w-full h-full m-2"
        >
          <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5L8.25 12l7.5-7.5"
          />
        </svg>
        <span class="absolute inset-0 -m-4"></span>
      </button>

      <button
          class="absolute top-1/2 -translate-y-1/2 right-[0px] w-12 h-12 sm:w-14 sm:h-14 transform -translate-x-[0%] text-orange-950 z-20 rounded-full flex items-center justify-center bg-white bg-opacity-75 rotate-180 border-[1.5px] border-black"
          @click="nextImage('cilACil')"
      >
        <svg
            xmlns="http://www.w3.org/2000/svg"
            fill="none"
            viewBox="0 0 24 24"
            stroke-width="1.5"
            stroke="black"
            class="w-full h-full m-2"
        >
          <path
              stroke-linecap="round"
              stroke-linejoin="round"
              d="M15.75 19.5L8.25 12l7.5-7.5"
          />
        </svg>
        <span class="absolute inset-0 -m-4"></span>
      </button>
    </div>

  </div>
</template>


<script>
export default {
 data() {
   return {
     currentImageHybride: 0,


     hybrideImages: [
       require('../img/mixte/1.webp'),
       require('../img/mixte/2.webp'),
       require('../img/mixte/3.webp'),
     ],
   }
 },

  computed: {
    nextImageHybride() {
      // Calculer l'index de l'image précédente
      return (this.currentImageHybride + this.hybrideImages.length - 1) % this.hybrideImages.length;
    },
    prevImageHybride() {
      // Calculer l'index de l'image suivante
      return (this.currentImageHybride + 1) % this.hybrideImages.length;
    },
  },
  methods: {
    nextImage() {
      this.currentImageHybride = (this.currentImageHybride + this.hybrideImages.length - 1) % this.hybrideImages.length;
    },
    previousImage() {
      this.currentImageHybride = (this.currentImageHybride + 1) % this.hybrideImages.length;
    },
  },

}
</script>

<style scoped>
img {
  filter:  contrast(0.8) ;
}
</style>