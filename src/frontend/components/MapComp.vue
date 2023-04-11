<template>
  <div class="item--map">

    <l-map :zoom="zoom" :center="center">
      <l-tile-layer :url="url" :attribution="attribution" />
      <l-marker v-for="item in items" :key="item.id" :lat-lng="item.coordinates" />
    </l-map>
  </div>
</template>

<script>
import L from 'leaflet';
import { LMap, LTileLayer, LMarker } from "@vue-leaflet/vue-leaflet";
import 'leaflet/dist/leaflet.css';

export default {
  components: {
    LMap,
    LTileLayer,
    LMarker,
  },
  props: {
    items: Array,
  },
  data() {
    return {
      zoom: 5,
      center: [46.8139, -71.208],
      url: "https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png",
      attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors',
    };
  },
};
</script>
