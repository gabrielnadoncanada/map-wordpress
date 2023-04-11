<template>
  <form @submit.prevent="submitCheckout">
    <!-- Customer details -->
    <div>
      <label for="first_name">First Name</label>
      <input type="text" id="first_name" v-model="customer.first_name" required/>
    </div>
    <!-- ... more form fields ... -->

    <!-- Billing Address -->
    <div>
      <h3>Billing Address</h3>
      <label for="billing_address_1">Address Line 1</label>
      <input type="text" id="billing_address_1" v-model="customer.billing.address_1" required/>
    </div>
    <!-- ... more form fields ... -->

    <!-- Shipping Address -->
    <div>
      <h3>Shipping Address</h3>
      <label for="shipping_address_1">Address Line 1</label>
      <input type="text" id="shipping_address_1" v-model="customer.shipping.address_1" required/>
    </div>
    <!-- ... more form fields ... -->

    <button type="submit">Submit</button>
  </form>

  <form action="PAYPAL_IPN_LISTENER_URL" method="post" target="_blank">
  <input type="hidden" name="cmd" value="_xclick">
  <input type="hidden" name="business" value="sb-a7fh417457492@business.example.com">
  <input type="hidden" name="item_name" value="Order Payment">
  <input type="hidden" name="amount" v-bind:value="totalAmount">
  <input type="hidden" name="currency_code" value="USD">
  <input type="hidden" name="return" value="https://yourwebsite.com/payment-success">
  <input type="hidden" name="cancel_return" value="https://yourwebsite.com/payment-cancel">
  <input type="submit" value="Pay with PayPal">
    <input type="hidden" name="custom" v-bind:value="JSON.stringify(cartDetails)">

</form>
</template>
<script>


export default {
  data() {
    return {
      customer: {
        first_name: '',
        // ... more customer data properties ...
        billing: {
          address_1: '',
          // ... more billing address properties ...
        },
        shipping: {
          address_1: '',
          // ... more shipping address properties ...
        },
      },
    };
  },
  methods: {
    async submitCheckout() {
      try {
        const order = {
          payment_method: 'bacs',
          payment_method_title: 'Direct Bank Transfer',
          set_paid: false,
          billing: this.customer.billing,
          shipping: this.customer.shipping,
          line_items: [], // You should populate this with the products in the cart
          shipping_lines: [], // You should populate this with the chosen shipping method(s) and cost(s)
          customer_note: '',
        };

        const response = await fetch('https://yourwebsite.com/wp-json/myplugin/v1/submit_order', {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
          },
          body: JSON.stringify(order),
        });

        if (response.ok) {
          const responseData = await response.json();
          console.log('Order created:', responseData);
        } else {
          throw new Error('Failed to create order');
        }
      } catch (error) {
        console.error('Error:', error.message);
      }
    },
  },
};
</script>