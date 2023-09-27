<template>
  <b-overlay :show="loading">
    <div class="formBoder">
    <ValidationObserver ref="form" v-slot="{ handleSubmit, reset }">
      <b-form @submit.prevent="handleSubmit(submitData)" @reset.prevent="reset" autocomplete="off">
        <b-row>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Stock Date" vid="stock_date" rules="required" v-slot="{ errors }">
              <b-form-group
                label-for="stock_date">
                <template v-slot:label>
                  Stock Date
                </template>
                <flat-pickr
                  id="stock_date"
                  v-model="form.stock_date"
                  class="form-control"
                  placeholder="Select Stock Date"
                  :state="errors[0] ? false : (valid ? true : null)"
                  :config="flatPickrConfig"
                />
                <div class="d-block invalid-feedback">
                  {{ errors[0] }}
                </div>
              </b-form-group>
            </ValidationProvider>
          </b-col>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Reference Number" vid="reference_number" rules="required" v-slot="{ errors }">
              <b-form-group
                id="reference_number"
                label-for="reference_number"
              >
              <template v-slot:label>
                Reference Number <span>*</span>
              </template>
                <b-form-input
                  id="reference_number"
                  v-model="form.reference_number"
                  placeholder="Enter Reference Number"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
              </b-form-group>
            </ValidationProvider>
          </b-col>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Box Number" vid="box_number" rules="required" v-slot="{ errors }">
              <b-form-group
                id="box_number"
                label-for="box_number"
              >
              <template v-slot:label>
                Box Number <span>*</span>
              </template>
                <b-form-input
                  id="box_number"
                  v-model="form.box_number"
                  placeholder="Enter Box Number"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
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

import flatPickr from 'vue-flatpickr-component'
import 'flatpickr/dist/flatpickr.css'
import moment from 'moment'

export default {
  props: ['editItem'],
  components: {
    flatPickr
  },
  data () {
    return {
      SaveButton: this.editItem ? 'Update' : 'Save',
      form: {
        stock_date: 'today',
        reference_number: '',
        box_number: '',
        dl_number: '',
        name: '',
        father_name: '',
        dob: '',
        blood: '',
        comment: ''
      },
      errors: [],
      valid: null,
      loading: false,
      flatPickrConfig: {
        dateFormat: 'd-m-Y'
      }
    }
  },
  created () {
    if (this.editItem) {
      this.form = JSON.parse(this.editItem)
      this.form.stock_date = moment(this.editItem.stock_date).format('DD-MM-YYYY')
    }
  },
  methods: {
    async submitData () {
      this.loading = true
      let result = ''
      if (this.form.id) {
        result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/update_dl_stock_data', this.form)
      } else {
        result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/store_dl_stock_data', this.form)
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
