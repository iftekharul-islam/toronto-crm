<template>
  <div class="order-details-box bg-white">
    <div class="box-header">
      <p class="fw-bold text-light-black fs-20 mb-0">Property Information</p>
      <a href="#" @click="openModal" class="d-inline-flex edit align-items-center fw-bold">Edit <span
          class="icon-edit ms-3"><span class="path1"></span><span class="path2"></span></span></a>
    </div>
    <div class="box-body">
      <div class="list__group">
        <p class="mb-0 left-side">Property Details </p>
        <span>:</span>
        <p class="right-side mb-0 primary-text fw-bold fs-20">{{ edited.full_addr }}</p>
      </div>
      <!-- <div class="list__group">
        <p class="mb-0 left-side">Property address</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.search_address }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">State</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.state_name }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">City</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.city_name }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Street</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.street_name }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Latitude</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.latitude }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Longitude</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.longitude }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">Zip</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.zip }}</p>
      </div> -->
      <div class="list__group">
        <p class="mb-0 left-side">Unit No</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.unit_no }}</p>
      </div>
      <div class="list__group">
        <p class="mb-0 left-side">County</p>
        <span>:</span>
        <p class="right-side mb-0">{{ edited.county }}</p>
      </div>
    </div>
    <b-modal id="property-info" size="lg" title="Edit Basic Information">
      <div class="modal-body">
        <b-alert v-if="message" show variant="success"><a href="#" class="alert-link">{{ message }}</a></b-alert>
        <ValidationObserver ref="clientPropertyName">
          <div class="row">
            <div class="col-md-6">
              <ValidationProvider rules="required" name="Search Address" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Search address <span class="require"></span></label>
                  <input type="text" ref="searchMapLocation" v-model="info.search_address" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>

              <ValidationProvider rules="required" name="Street name" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Street name <span class="require"></span></label>
                  <input type="text" v-model="info.street_name" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="City name" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">City name <span class="require"></span></label>
                  <input type="text" v-model="info.city_name" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="State name" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">State name <span class="require"></span></label>
                  <input type="text" v-model="info.state_name" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <div class="divider"></div>
              <div class="group">
                <small>Edit with map location:</small>
                <a :href="`/orders/${order.id}/edit`" class="btn btn-sm btn-primary">Edit Here</a>
              </div>
            </div>
            <div class="col-md-6">
              <ValidationProvider :rules="{'required' : condoType }" name="Unit #" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Unit # <span v-if="condoType" class="require"></span></label>
                  <input type="text" v-model="info.unit_no" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="County" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">County <span class="require"></span></label>
                  <input type="text" v-model="info.county" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="Zip" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Zip <span class="require"></span></label>
                  <input type="text" v-model="info.zip" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="Latitude" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Latitude <span class="require"></span></label>
                  <input type="text" v-model="info.latitude" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
              <ValidationProvider rules="required" name="Longitude" v-slot="{ errors }">
                <div class="group" :class="{ 'invalid-form' : errors[0] }">
                  <label for="" class="d-block mb-2 dashboard-label">Longitude <span class="require"></span></label>
                  <input type="text" v-model="info.longitude" class="dashboard-input w-100">
                  <span v-if="errors[0]" class="error-message">{{ errors[0] }}</span>
                </div>
              </ValidationProvider>
            </div>
          </div>
        </ValidationObserver>
      </div>
      <div slot="modal-footer">
        <b-button variant="secondary" @click="closeModal">Close</b-button>
        <b-button variant="primary" @click="updatePropertyInfo">Save</b-button>
      </div>
    </b-modal>
  </div>
</template>
<script>
export default {
  props:{
    orderId: String,
    appraisal_types: [],
    order: []
  },
  data() {
    return {
      info:{
        formatedAddress: '',
        search_address: '',
        street_name: '',
        city_name: '',
        state_name: '',
        zip:'',
        unit_no:'',
        country:'',
        longitude: '',
        latitude: ''
      },
      condoType: false,
      edited: {},
      message: '',
      mapData: {
        map: null,
        center: {lat: null, lng: null},
      }
    }
  },
  computed: {
    oldData() {
        return this.edited;
    }
  },
  created() {
    this.getPropertyInfo(this.order);
  },
  mounted() {

  },
  methods: {
    closeModal() {
        this.$bvModal.hide('property-info');
    },
    openModal() {
        this.$bvModal.show('property-info');
    },
    getPropertyInfo(){
        this.info.search_address = this.order.property_info.search_address
        this.info.street_name = this.order.property_info.street_name
        this.info.full_addr = this.order.property_info.full_addr
        this.info.city_name = this.order.property_info.city_name
        this.info.state_name = this.order.property_info.state_name
        this.info.formatedAddress = this.order.property_info.full_addr
        this.info.zip = this.order.property_info.zip
        this.info.county = this.order.property_info.county
        this.info.unit_no = this.order.property_info.unit_no
        this.info.latitude = this.order.property_info.latitude
        this.info.longitude = this.order.property_info.longitude
        this.mapData.center.lat = parseFloat(this.info.latitude);
        this.mapData.center.lng = parseFloat(this.info.longitude);
        this.edited = Object.assign({}, this.info);

        let provider_service = JSON.parse(this.order.provider_service.appraiser_type_fee);
        for(let i in provider_service) {
            let self = provider_service[i];
            let findIndex = this.appraisal_types.find(ele => ele.id == self.typeId);
            if (findIndex && findIndex.condo_type == 1) {
              this.condoType = true;
              break;
            }
        }
    },
    updatePropertyInfo(){
      let that = this
      if (this.condoType == true && (this.info.unit_no == null || this.info.unit_no == "") ) {
          return false;
      }
      this.$refs.clientPropertyName.validate().then((status) => {
          if (status) {
              axios.post('update-property-info/'+ this.orderId, this.info)
                  .then(res => {
                    that.message = res.data.message
                    this.edited = Object.assign({}, this.info);
                    this.$toast.open({
                        message: that.message,
                        type: res.data.error == true ? 'error' : 'success',
                    });

                    that.$bvModal.hide('property-info');
                  }).catch(err => {
              })
          }
      });
    },
  }
}
</script>

<style scoped>
.group {
  margin-bottom: 15px;
}
</style>
