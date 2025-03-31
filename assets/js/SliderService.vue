<template>
  <section>
    <!-- Trois carrousels, un pour chaque service -->
    <div class="carousel-wrapper">
      <div class="carousel">
        <img :src="cilACilImages[currentImageCilACil]" alt="Cil à Cil" class="carousel-image" />
        <div class="carousel-controls">
          <button @click="previousImage('cilACil')">Précédent</button>
          <button @click="nextImage('cilACil')">Suivant</button>
        </div>
      </div>
    </div>

    <div class="carousel-wrapper">
      <div class="carousel">
        <img :src="volRusseImages[currentImageVolRusse]" alt="Volume Russe" class="carousel-image" />
        <div class="carousel-controls">
          <button @click="previousImage('volRusse')">Précédent</button>
          <button @click="nextImage('volRusse')">Suivant</button>
        </div>
      </div>
    </div>


  </section>
</template>

<script>
export default {
  data() {
    return {
      currentImageCilACil: 0,
      currentImageVolRusse: 0,
      currentImageHybride: 0,
      hybrideImages: [
        require('../img/hybride/1.jpg'),
        require('../img/hybride/2.jpg'),
        require('../img/hybride/3.jpg'),
      ],
      volRusseImages: [
        require('../img/russe/1.png'),
        require('../img/russe/2.png'),
        require('../img/russe/3.png'),
      ],
      cilACilImages: [
        require('../img/mixte/1.jpg'),
        require('../img/mixte/2.jpg'),
        require('../img/mixte/3.jpg'),
      ],
    };
  },
  methods: {
    // Méthodes pour afficher l'image précédente
    previousImage(type) {
      if (type === 'cilACil') {
        this.currentImageCilACil = (this.currentImageCilACil + this.cilACilImages.length - 1) % this.cilACilImages.length;
      } else if (type === 'volRusse') {
        this.currentImageVolRusse = (this.currentImageVolRusse + this.volRusseImages.length - 1) % this.volRusseImages.length;
      } else if (type === 'hybride') {
        this.currentImageHybride = (this.currentImageHybride + this.hybrideImages.length - 1) % this.hybrideImages.length;
      }
    },
    // Méthodes pour afficher l'image suivante
    nextImage(type) {
      if (type === 'cilACil') {
        this.currentImageCilACil = (this.currentImageCilACil + 1) % this.cilACilImages.length;
      } else if (type === 'volRusse') {
        this.currentImageVolRusse = (this.currentImageVolRusse + 1) % this.volRusseImages.length;
      } else if (type === 'hybride') {
        this.currentImageHybride = (this.currentImageHybride + 1) % this.hybrideImages.length;
      }
    },
  },
};
</script>

<style scoped>
.carousel-wrapper {
  margin-bottom: 20px;
}

.carousel {
  display: flex;
  flex-direction: column;
  align-items: center;
}

.carousel-image {
  width: 100%;
  max-width: 500px; /* Ajustez selon vos besoins */
  border-radius: 8px;
}

.carousel-controls button {
  margin: 5px;
  padding: 8px 12px;
}
</style>
