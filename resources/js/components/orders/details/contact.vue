<template>
  <div class="order-details-box bg-white">
    <div class="box-header">
      <p class="fw-bold text-light-black fs-20 mb-0">Contact</p>
      <a v-b-modal.contact-info class="d-inline-flex edit align-items-center fw-bold cursor-pointer">Edit <span class="icon-edit ms-3"><span class="path1"></span><span class="path2"></span></span></a>
    </div>
    <div class="box-body">
      <div class="list__group">
        <p class="mb-0 left-side">Contact name</p>
        <span>:</span>
        <p class="right-side mb-0" v-html="contact_info"></span></p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Phone Numbers</p>
        <span>:</span>
        <p class="right-side list-items mb-0 phone-number">
          <input @click="selectText(item)" readonly class="d-inline-block mb-2" v-for="item, ik in contact_number_s" :key="ik" :value="item"/>
        </p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Email Address</p>
        <span>:</span>
        <p class="right-side list-items mb-0">
          <span class="d-inline-block mb-2" v-for="item, ik in email_address_s" :key="ik"> {{ item }} </span>
        </p>
      </div>
    </div>
    <ValidationObserver ref="orderForm">
    <!-- modal -->
    <b-modal id="contact-info" class="brrower-modal" size="lg" title="Edit Contact">
      <div class="modal-body brrower-modal-body">
        <div class="row">
          <div class="col-12">
            <ValidationProvider class="d-block group" name="Contact name" rules="required" v-slot="{ errors }">
              <div class="group" :class="{ 'invalid-form' : errors[0] }">
                <label for="" class="d-block mb-2 dashboard-label">Contact name <span
                    class="text-danger require"></span></label>
                <input v-model="edit_contact_name" class="dashboard-input w-100" id="" >
                <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
              </div>
            </ValidationProvider>

            <ValidationObserver class="d-block group" ref="addContactForm">
                <ValidationProvider name="Contact Number" :rules="{'required' : add.contact == null && contact_number == false, min: 10, max: 12}" v-slot="{ errors }">
                  <div class="group" :class="{ 'invalid-form' : errors[0] }">
                    <label for="" class="d-block mb-2 dashboard-label">Phone <span class="text-danger require"></span></label>
                    <div class="new-array-items mgb-12">
                      <div class="items" v-for="item, ki in contact_number_s" :key="ki">
                        <div class="item-content"> {{ item }} </div>
                        <div class="item-remove">
                          <button class="button button-transparent p-1" @click="removeItem(ki, 'contact')">
                            <span class="icon-trash fs-20"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                          </button>
                        </div>
                      </div>
                    </div>
                    <input v-model="add.contact" @input="contactNumberChecking($event, 1)" @blur="addContact" type="text" class="dashboard-input w-100">
                    <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                    <div class="mgt-12">
                      <button class="add-more" @click="addContact">
                        <span class="icon-plus"></span> Add
                      </button>
                    </div>
                  </div>
                </ValidationProvider>
            </ValidationObserver>

            <ValidationObserver class="d-block group" ref="addEmailForm">
                <ValidationProvider name="Contact Email Address" :rules="{'required' : email_address == false && add.email == null, 'email' : true }" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Email <span
                      class="text-danger require"></span></label>
                  <div class="new-array-items mgb-12">
                    <div class="items" v-for="item, ki in email_address_s" :key="ki">
                      <div class="item-content"> {{ item }} </div>
                      <div class="item-remove">
                        <button class="button button-transparent p-1" @click="removeItem(ki, 'email')">
                          <span class="icon-trash fs-20"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></span>
                        </button>
                      </div>
                    </div>
                  </div>
                  <input v-model="add.email" type="text" @blur="addEmail" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                  <div class="mgt-12">
                    <button class="add-more" @click="addEmail">
                      <span class="icon-plus"></span> Add
                    </button>
                  </div>
                </div>
              </ValidationProvider>
            </ValidationObserver>
          </div>
        </div>
      </div>
      <div slot="modal-footer" class="mgt-44">
        <button class="button button-transparent" @click="hideModal()">Close</button>
        <button class="button button-primary" @click="updateContactInfo">Save</button>
      </div>
    </b-modal>
    </ValidationObserver>
  </div>
</template>
<script>
  export default {
    props:{
      orderId: String,
      order: []
    },
    data(){
      return{
        contactSame: false,
        contact_info: null,
        edit_contact_name: null,
        contact_number: false,
        email_address: false,
        contact_number_s: [],
        email_address_s: [],

        add: {
          contact: null,
          email: null,
          contact2: null,
          email2: null
        }
      }
    },
    created() {
      this.getBorrowerInfo()
    },
    methods:{
        hideModal(){
            this.$bvModal.hide('contact-info')
            this.edit_contact_name = this.contact_info
        },
      getBorrowerInfo(){

        let contactInfo = JSON.parse(this.order.contact_info.contact_email);
        let contactInfoEmail = contactInfo['email'];
        let contactInfoPhone = contactInfo['phone'];

        this.contactSame = this.order.contact_info.is_borrower;
        this.contact_info = this.order.contact_info.contact;
        this.edit_contact_name = this.order.contact_info.contact;
        this.contact_number = contactInfoPhone.length ? true : false;
        this.email_address = contactInfoEmail.length ? true : false;
        this.contact_number_s = contactInfoPhone;
        this.email_address_s = contactInfoEmail;
      },
      updateContactInfo(){
          this.$refs.orderForm.validate().then((status) => {
              if (status) {
                  if (this.email_address == true && this.contact_number == true) {
                      this.submitData();
                  }
              }
          })
      },
      submitData() {
          this.$refs.orderForm.reset();
          this.$boston.post('order/update/contactInfo', {
            contact_info: this.edit_contact_name,
            contact_number: this.contact_number,
            email_address: this.email_address,
            contact_number_s: this.contact_number_s,
            email_address_s: this.email_address_s,
            order: this.order
          }).then(res => {
              this.contact_info = this.edit_contact_name
              this.$root.$emit('toast_msg', res)
              this.$bvModal.hide('contact-info');
          });
      },
      addEmail() {
          this.$refs.addEmailForm.validate().then((status) => {
              if (status) {
                  let newEmail = this.add.email;
                  let findOld = this.email_address_s.find((ele) =>  ele == newEmail);
                  if (!findOld && newEmail != null && newEmail != "") {
                    this.email_address_s.push(newEmail);
                    this.add.email = null;
                    this.email_address = true;
                    this.$refs.addEmailForm.reset();
                  }
              }
          });
      },
      contactNumberChecking(e, type){
          let phoneNo = e.target.value;
          let formatedNumber = this.$boston.formatPhoneNo(phoneNo);
          this.add.contact = formatedNumber;
      },
      addContact() {
          this.$refs.addContactForm.validate().then((status) => {
              if (status) {
                  let newContact = this.add.contact;
                  if ( newContact.length < 10 || newContact.length > 12 ) {
                      return false;
                  }
                  let findOld = this.contact_number_s.find((ele) =>  ele == newContact);
                  if (!findOld && newContact != null && newContact != "") {
                      this.contact_number_s.push(newContact);
                      this.add.contact = null;
                      this.contact_number = true;
                      this.$refs.addContactForm.reset();
                  }
              }
          });
      },
      removeItem(index, type) {
        if (type == 'email') {
            this.email_address_s.splice(index, 1);
            if (this.email_address_s.length == 0) {
              this.email_address = false;
            }
        } else if (type == 'contact') {
            this.contact_number_s.splice(index, 1);
            if (this.contact_number_s.length == 0) {
              this.contact_number = false;
            }
        }
      },
    }
  }
</script>


<style scoped>
.new-array-items .items {
    display: flex;
    justify-content: space-between;
}
.new-array-items .items:nth-last-child(1) {
    margin-bottom: 0px;
    border-bottom: unset;
}
.divider {
    border-top: 4px solid #35a6dc;
    background: transparent;
    padding-top: 20px;
    margin-top: 20px;
}

</style>
