<template>
  <Sidebar :order="order"></Sidebar>
  <div v-for="(passenger, index) in order.passengers" :key="index">
    <h3>Passenger {{ index + 1 }}</h3>
    <label for="first-name">First Name:</label>
    <input type="text" :id="'first-name-' + index" v-model="passenger.firstName"/>

    <label for="last-name">Last Name:</label>
    <input type="text" :id="'last-name-' + index" v-model="passenger.lastName"/>

    <label for="age-range">Age Range:</label>
    <select :id="'age-range-' + index" v-model="passenger.ageRange" @change="calculateTotal">
      <option value="2" selected>0 à 2 ans</option>
      <option value="12">2 à 12 ans</option>
      <option value="99">12 ans et +</option>
    </select>

    <div class="weight-selector">
      <label for="'weight-' + index">Weight:</label>
      <input type="range" :id="'weight-' + index" v-model="passenger.weight" :min="weightMin" :max="weightMax"/>
      <div class="unit-selector">
        <button :class="{ active: passenger.weightUnit === 'LB' }" @click="setWeightUnit('LB', index)">LB</button>
        <button :class="{ active: passenger.weightUnit === 'KG' }" @click="setWeightUnit('KG', index)">KG</button>
      </div>
    </div>
    <label for="'email-' + index">Email:</label>
    <input type="email" :id="'email-' + index" v-model="passenger.email"/>

    <label for="'phone-' + index">Phone:</label>
    <input type="tel" :id="'phone-' + index" v-model="passenger.phone"/>
    <label>
      <input type="checkbox" v-model="passenger.acceptPromotions"/>
      Accept promotions
    </label>

    <button @click="deletePassenger(index)">Delete Passenger</button>

    <p v-if="errors[index]">{{ errors[index] }}</p>
  </div>
  <button @click="addPassenger">Add Passenger</button>
  <button @click="back">Back</button>

  <button @click="next">Next</button>
</template>

<style>
.unit-selector button.active {
  background-color: blue;
  color: white;
}
</style>

<script>
import Sidebar from "../components/SidebarComp.vue";

export default {
  components: {
    Sidebar,
  },
  data() {
    return {
      order: JSON.parse(localStorage.getItem('order')) || [],
      weightMin: 0,
      weightMax: 150,
      errors: [],
    };
  },
  created() {
    this.calculateTotal();
  },
  methods: {
    addPassenger() {
      this.order.passengers.push({
        price: 0,
        discount: 0,
        firstName: "",
        lastName: "",
        ageRange: "99",
        weight: 50,
        weightUnit: "LB",
        email: "",
        phone: "",
        acceptPromotions: false,
      });
      this.errors.push("");
      this.calculateTotal();
    },
    deletePassenger(index) {
      if (this.order.passengers.length > 1) {
        this.order.passengers.splice(index, 1);
        this.errors.splice(index, 1);
        this.calculateTotal();
      }
    },
    setWeightUnit(unit, index) {
      if (unit === "LB") {
        this.order.passengers[index].weightUnit = "LB";
        this.order.passengers[index].weight = Math.round(this.order.passengers[index].weight * 2.20462);
      } else if (unit === "KG") {
        this.order.passengers[index].weightUnit = "KG";
        this.order.passengers[index].weight = Math.round(this.order.passengers[index].weight / 2.20462 * 10) / 10;
      }
    },
    back() {
      this.$emit("back");
    },
    next() {
      this.errors = [];
      let hasErrors = false;

      this.order.passengers.forEach((passenger, index) => {
        if (!passenger.firstName ||
            !passenger.lastName ||
            !passenger.ageRange ||
            !passenger.weight
        ) {
          this.errors[index] = "Please fill in all fields";
          hasErrors = true;
        } else {
          this.errors[index] = "";
        }
      });

      if (!hasErrors) {
        if (this.order.passengers.length > 0) {
          localStorage.setItem("order", JSON.stringify(this.order));
          this.$router.push({name: 'Checkout'})
        }
      }
    },
    calculateTotal() {
      let totalPrice = 0;
      let totalDiscountPrice = 0;
      let adultPassengers = 0;
      let childPassengers = [];

      this.order.passengers.forEach((passenger, index) => {
        const ageRange = parseInt(passenger.ageRange);
        const isAdult = ageRange > 12;

        if (isAdult) {
          adultPassengers++;
        } else {
          childPassengers.push({ index, ageRange });
        }

        this.order.passengers[index].price = this.order.basePrice;
        totalPrice += this.order.basePrice;
      });

      if (adultPassengers >= 2) {
        childPassengers.forEach(({ index, ageRange }) => {
          let discount = 0;

          if (ageRange === 2) {
            discount = this.order.basePrice;
          } else if (ageRange === 12) {
            discount = this.order.basePrice * 0.2;
          }

          this.order.passengers[index].discount = discount;
          totalDiscountPrice += discount;
        });
      } else {
        childPassengers.forEach(({ index }) => {
          this.order.passengers[index].discount = 0;
        });
      }

      this.order.orderDiscountTotal = totalDiscountPrice;
      this.order.orderSubTotal = totalPrice;
      this.order.orderTotal = totalPrice - totalDiscountPrice;
    }
  },

  computed: {
    hasErrors() {
      return this.errors.some((error) => error !== "");
    },
  },
};
</script>

