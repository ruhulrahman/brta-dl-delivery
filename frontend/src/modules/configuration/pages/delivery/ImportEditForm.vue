<template>
  <b-overlay :show="loading">
    <div class="formBoder">
    <ValidationObserver ref="form" v-slot="{ handleSubmit, reset }">
      <b-form @submit.prevent="handleSubmit(submitData)" @reset.prevent="reset" autocomplete="off">
        <b-row>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Serial Number" vid="serial_number" rules="required" v-slot="{ errors }">
              <b-form-group label-for="serial_number">
              <template v-slot:label>
                Serial Number <span class="text-danger">*</span>
              </template>
                <b-form-input
                  type="number"
                  id="serial_number"
                  v-model="form.serial_number"
                  placeholder="Enter Serial Number"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
              </b-form-group>
            </ValidationProvider>
          </b-col>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Receiving Box Number" vid="receiving_box_number" rules="required" v-slot="{ errors }">
              <b-form-group label-for="receiving_box_number">
              <template v-slot:label>
                Receiving Box Number <span class="text-danger">*</span>
              </template>
                <b-form-input
                  id="receiving_box_number"
                  v-model="form.receiving_box_number"
                  placeholder="Enter Receiving Box Number"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
                  {{ errors[0] }}
                </div>
              </b-form-group>
            </ValidationProvider>
          </b-col>
          <b-col lg="4" md="4" sm="12" xs="12">
            <ValidationProvider name="Receive Date" vid="receive_date" rules="required" v-slot="{ errors }">
              <b-form-group
                label-for="receive_date">
                <template v-slot:label>
                  Receive Date <span class="text-danger">*</span>
                </template>
                <flat-pickr
                  id="receive_date"
                  v-model="form.receive_date"
                  class="form-control"
                  placeholder="Select Receive Date"
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
            <ValidationProvider name="Entry Box Number" vid="entry_box_number" rules="required" v-slot="{ errors }">
              <b-form-group
                id="entry_box_number"
                label-for="entry_box_number"
              >
              <template v-slot:label>
                Entry Box Number <span class="text-danger">*</span>
              </template>
                <b-form-input
                  id="entry_box_number"
                  v-model="form.entry_box_number"
                  placeholder="Enter Entry Box Number"
                  :state="errors[0] ? false : (valid ? true : null)"
                ></b-form-input>
                <div class="invalid-feedback">
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
                Reference Number <span class="text-danger">*</span>
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

export default {
  props: ['editItem'],
  components: {
    flatPickr
  },
  data () {
    return {
      SaveButton: this.editItem ? 'Update' : 'Save',
      form: {
        serial_number: 1,
        receive_date: '',
        receiving_box_number: '',
        reference_number: '',
        entry_box_number: '',
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
    }
  },
  methods: {
    async submitData () {
      this.loading = true
      const result = await RestApi.postData(baseURL, 'api/v1/admin/ajax/check_duplicate_reference_number', this.form)
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
