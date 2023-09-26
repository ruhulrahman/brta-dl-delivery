<template>
  <b-overlay :show="loading">
    <div class="formBoder">
    <ValidationObserver ref="form" v-slot="{ handleSubmit, reset }">
      <b-form @submit.prevent="handleSubmit(submitData)" @reset.prevent="reset" autocomplete="off">
        <b-row>
        <b-col lg="12" md="12" sm="12" xs="12">
          <ValidationProvider name="Plan Title" vid="plan_title" rules="required" v-slot="{ errors }">
            <b-form-group
              class="row"
              label-cols-sm="12"
              id="plan_title"
              label-for="Plan Title"
            >
            <template v-slot:label>
              Plan Title <span>*</span>
            </template>
              <b-form-input
                id="plan_title"
                v-model="form.plan_title"
                placeholder="Enter Plan Title"
                :state="errors[0] ? false : (valid ? true : null)"
              ></b-form-input>
              <div class="invalid-feedback">
                {{ errors[0] }}
              </div>
            </b-form-group>
          </ValidationProvider>
        </b-col>
        </b-row>
        <b-row>
        <b-col lg="6" md="6" sm="3" xs="12">
          <ValidationProvider name="Plan Duration" vid="plan_duration" rules="required" v-slot="{ errors }">
            <b-form-group
              class="row"
              label-cols-sm="12"
              label-for="plan_duration"
            >
            <template v-slot:label>
              Plan Duration <span>*</span>
            </template>
            <b-input-group append="Month">
              <b-form-input
                id="plan_duration"
                v-model="form.plan_duration"
                placeholder="Enter Plan Duration"
                :state="errors[0] ? false : (valid ? true : null)"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
              ></b-form-input>
            </b-input-group>
              <!-- <span>Month</span> -->
              <div class="d-block invalid-feedback">
                {{ errors[0] }}
              </div>
            </b-form-group>
          </ValidationProvider>
        </b-col>
          <b-col lg="6" md="6" sm="6" xs="12">
          <ValidationProvider name="Plan Price" vid="plan_price" rules="required" v-slot="{ errors }">
             <b-form-group
              class="row"
              label-cols-sm="12"
              label-for="plan_price"
            >
            <template v-slot:label>
              Plan Price <span>*</span>
            </template>
            <b-input-group prepend="BDT">
              <b-form-input
                id="plan_price"
                v-model="form.plan_price"
                placeholder="Enter Plan Price"
                :state="errors[0] ? false : (valid ? true : null)"
                oninput="this.value = this.value.replace(/[^0-9.]/g, '').replace(/(\..*?)\..*/g, '$1');"
              ></b-form-input>
             </b-input-group>
              <div class="d-block invalid-feedback">
                {{ errors[0] }}
              </div>
            </b-form-group>
          </ValidationProvider>
          </b-col>
       </b-row>
       <!-- <b-row>
          <b-col lg="8" md="8" sm="8" xs="12" offset="2">
          <ValidationProvider name="Plan Benefit" vid="plan_benefits" rules="required" v-slot="{ errors }">
            <b-form-group
              class="row"
              label-cols-sm="3"
              label-for="plan_benefits"
            >
            <template v-slot:label>
              Plan Benefit
            </template>
              <b-form-input
                id="plan_benefits"
                v-model="form.plan_benefits"
                placeholder="Enter Plan Benefit"
                :state="errors[0] ? false : (valid ? true : null)"
              ></b-form-input>
              <div class="invalid-feedback">
                {{ errors[0] }}
              </div>
            </b-form-group>
          </ValidationProvider>
          </b-col>
       </b-row> -->
        <b-row>
          <b-col lg="12" md="12" sm="12" xs="12">
            <ValidationProvider name="Plan Benefit" vid="plan_benefits" rules="required">
              <b-form-group
                class="row"
                label-cols-sm="12"
                label-for="plan_benefits"
                slot-scope="{ valid, errors }"
                >
               <template v-slot:label>
                Plan Benefits
               </template>
               <vue-editor
                id="text"
                v-model="form.plan_benefits"
                :state="errors[0] ? false : (valid ? true : null)"
              ></vue-editor>
          </b-form-group>
          </ValidationProvider>
          </b-col>
       </b-row>
      <div class="row mt-3">
        <div class="col-sm-3"></div>
        <div class="col text-right">
            <b-button type="submit" variant="primary" class="mr-2">{{ SaveButton }}</b-button>
            &nbsp;
            <b-button variant="danger" class="mr-1" @click="$bvModal.hide('modal-1')">Cancel</b-button>
        </div>
      </div>
     </b-form>
    </ValidationObserver>
    </div>
  </b-overlay>
</template>
<script>
import RestApi, { baseURL } from '@/config'

export default {
  props: ['editItem'],
  data () {
    return {
      SaveButton: this.editItem ? 'Update' : 'Save',
      form: {
        plan_benefits: '',
        plan_title: '',
        plan_duration: '',
        plan_price: ''
      },
      errors: [],
      valid: null,
      loading: false,
      parentList: []
    }
  },
  created () {
    if (this.editItem) {
      this.form = this.editItem
    }
  },
  methods: {
    async submitData () {
      this.loading = true
      let result = ''
      if (this.form.id) {
        result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/update_subscription_plan_data', this.form)
      } else {
        result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/store_subscription_plan_data', this.form)
      }
      if (result.success) {
        this.$emit('loadList', true)
        this.$toast.success({
          title: 'Success',
          message: result.message
        })
        this.$bvModal.hide('modal-1')
        this.loading = false
      } else {
        this.$refs.form.setErrors(result.errors)
      }
    }
  }
}
</script>
<style>
 .formBoder {
    border: 1px solid;
    margin: 5px;
    padding: 35px;
    font-size: 13px
 }
 .input-group-text {
   height: 30.5px!important;
 }
</style>
