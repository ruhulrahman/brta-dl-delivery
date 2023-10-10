<template>
  <b-overlay :show="loading">
    <div class="formBoder">
      <table v-if="detailsItem" class="table table-striped table-hover table-bordered">
        <tbody>
          <tr>
            <th class="text-right">Reference</th>
            <td class="text-left">{{ detailsItem.reference_number }}</td>
          </tr>
          <tr>
            <th class="text-right">Serial Number</th>
            <td class="text-left">{{ detailsItem.serial_number }}</td>
          </tr>
          <tr>
            <th class="text-right">Entry Box Number</th>
            <td class="text-left">{{ detailsItem.entry_box_number }}</td>
          </tr>
          <tr v-if="detailsItem.dl_number">
            <th class="text-right">DL Number</th>
            <td class="text-left">{{ detailsItem.dl_number }}</td>
          </tr>
          <tr v-if="detailsItem.name">
            <th class="text-right">Name</th>
            <td class="text-left">{{ detailsItem.name }}</td>
          </tr>
          <tr v-if="detailsItem.father_name">
            <th class="text-right">Father's Name</th>
            <td class="text-left">{{ detailsItem.father_name }}</td>
          </tr>
          <tr>
            <th class="text-right">Comment</th>
            <td class="text-left">{{ detailsItem.comment }}</td>
          </tr>
          <tr v-if="detailsItem.dob">
            <th class="text-right">Date Of Birth</th>
            <td class="text-left">{{ detailsItem.dob ? dDate(detailsItem.dob) : '' }}</td>
          </tr>
          <tr v-if="detailsItem.blood">
            <th class="text-right">Blood</th>
            <td class="text-left">{{ detailsItem.blood }}</td>
          </tr>
          <tr>
            <th class="text-right">Receiving Box Number</th>
            <td class="text-left">{{ detailsItem.receiving_box_number }}</td>
          </tr>
          <tr>
            <th class="text-right">Receive Date</th>
            <td class="text-left">{{ detailsItem.receive_date ? dDateTime(detailsItem.receive_date) : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Created By</th>
            <td class="text-left">{{ detailsItem.creator ? detailsItem.creator.name : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Created At</th>
            <td class="text-left">{{ detailsItem.created_at ? dDateTime(detailsItem.created_at) : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Updated By</th>
            <td class="text-left">{{ detailsItem.editor ? detailsItem.editor.name : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Updated At</th>
            <td class="text-left">{{ detailsItem.updated_at ? dDateTime(detailsItem.updated_at) : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Comment</th>
            <td class="text-left">{{ detailsItem.comment }}</td>
          </tr>
          <tr>
            <th class="text-right">Delivered By</th>
            <td class="text-left">{{ detailsItem.delivered ? detailsItem.delivered.name : '' }}</td>
          </tr>
          <tr>
            <th class="text-right">Delivery Date</th>
            <td class="text-left">{{ detailsItem.delivery_date ? dDateTime(detailsItem.delivery_date) : '' }}</td>
          </tr>
        </tbody>
      </table>
      <div class="row mt-3">
        <div class="col-sm-3"></div>
        <div class="col text-right">
            <!-- <b-button type="submit" variant="primary" class="mr-2">{{ SaveButton }}</b-button>
            &nbsp; -->
            <b-button variant="danger" class="mr-1" @click="$bvModal.hide('modal-1')">Close</b-button>
        </div>
      </div>
    </div>
  </b-overlay>
</template>
<script>
import RestApi, { baseURL } from '@/config'

export default {
  props: ['editItem'],
  components: {
  },
  data () {
    return {
      SaveButton: this.editItem ? 'Update' : 'Save',
      detailsItem: '',
      loading: false,
      flatPickrConfig: {
        dateFormat: 'd-m-Y'
      }
    }
  },
  created () {
    if (this.editItem) {
      this.detailsItem = JSON.parse(this.editItem)
    }
    console.log('this.editItem', this.editItem)
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
