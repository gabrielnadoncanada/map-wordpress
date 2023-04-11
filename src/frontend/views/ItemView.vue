<template>
  <div>
    <label for="survols">Survols:</label>
    <select v-model="order.item">

      <option v-for="item in items" :value="item.id">{{ item.name }}</option>
    </select>

    <div v-if="selectedItem && selectedItem.base.length > 0">
      <label for="bases">Bases:</label>
      <select v-model="order.base">
        <option v-for="base in selectedItem.base" :value="base.id">{{ base.name }}</option>
      </select>
    </div>

    <p v-if="error">{{ error }}</p>
    <button @click="next">Next</button>
  </div>
</template>

<script>
export default {

  data() {
    return {
      items: [
        {
          name: "Le baptême",
          id: 1,
          price: 150,
          description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vitae ultricies lacinia, nisl nisl aliquet nisl",
          image: "https://www.lesailesdusaguenay.com/wp-content/uploads/2019/05/le-bapteme.jpg",
          base: [
            {
              name: "Saguenay",
              id: 1
            },
            {
              name: "Mauricie",
              id: 2
            },
            {
              name: "Québec",
              id: 3
            },
          ],
        },
        {
          name: "Le Découverte",
          id: 2,
          price: 150,
          description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vitae ultricies lacinia, nisl nisl aliquet nisl",
          image: "https://www.lesailesdusaguenay.com/wp-content/uploads/2019/05/le-bapteme.jpg",
          base: [
            {
              name: "Saguenay",
              id: 1

            },
            {
              name: "Mauricie",
              id: 2
            },
            {
              name: "Québec",
              id: 3
            },
          ],
        },

        {
          name: "L'aventurière",
          id: 3,
          price: 150,
          description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vitae ultricies lacinia, nisl nisl aliquet nisl",
          image: "https://www.lesailesdusaguenay.com/wp-content/uploads/2019/05/le-bapteme.jpg",
          base: [
            {
              name: "Saguenay",
              id: 1
            },
            {
              name: "Mauricie",
              id: 2
            },
            {
              name: "Québec",
              id: 3
            },
          ],
        },
        {
          name: "L'aviature",
          id: 4,
          price: 150,
          description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vitae ultricies lacinia, nisl nisl aliquet nisl",
          image: "https://www.lesailesdusaguenay.com/wp-content/uploads/2019/05/le-bapteme.jpg",
          base: [
            {
              name: "Saguenay",
              id: 1
            },
            {
              name: "Mauricie",
              id: 2
            },
            {
              name: "Québec",
              id: 3
            },
          ],
        },
        {
          name: "A la carte !",
          id: 5,
          price: 150,
          description: "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed euismod, nisl vitae ultricies lacinia, nisl nisl aliquet nisl",
          image: "https://www.lesailesdusaguenay.com/wp-content/uploads/2019/05/le-bapteme.jpg",
          base: [],
        },
      ],
      order: {
        item: '0',
        base: '1',
        basePrice: 0,
        orderSubTotal: 0,
        orderTotal: 0,
        orderDiscountTotal: 0,
        passengers: [
          {
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
          },
        ],
      },
      error: ''
    };
  },

  methods: {
    next() {
      this.errors = '';
      let hasErrors = false;

      if (!this.order.item || !this.order.base) {
        this.error = "Please fill in all fields";
        hasErrors = true;
      } else {
        this.error = "";
      }

      if (!hasErrors) {
        this.order.basePrice = this.selectedItem.price;
        localStorage.setItem("order", JSON.stringify(this.order));
        this.$router.push({name: 'Passengers'})
      }
    },
  },
  computed: {
    selectedItem() {
      return this.items.find(item => item.id === this.order.item);
    },
    selectedItemHasBase() {
      return this.selectedItem && this.selectedItem.base && this.selectedItem.base.length > 0
    }
  }
};
</script>
