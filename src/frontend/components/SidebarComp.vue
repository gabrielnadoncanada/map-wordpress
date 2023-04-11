<template>
  <div class="sidebar">
    <div class="orderSubTotal">
      Subtotal: ${{ order.orderSubTotal }}
    </div>
    <div class="orderDiscountTotal" v-if="order.orderDiscountTotal > 0">
      Discount: ${{ order.orderDiscountTotal }}
    </div>
    <div class="orderTotal">
      Total: ${{ order.orderTotal }}
    </div>
  </div>
</template>

<script>
export default {
  props: {
    order: {
      type: Object,
      required: true,
    },
  }
};
</script>

<style scoped>
.sidebar {
  background-color: #f2f2f2;
  height: 100%;
  padding: 1rem;
  text-align: center;
}

.total {
  font-size: 2rem;
  margin-bottom: 1rem;
}

.passengers {
  margin-top: 2rem;
}

.passengers h3 {
  font-size: 1.5rem;
  margin-bottom: 1rem;
}

.passengers p {
  margin-bottom: 0.5rem;
}
</style>
